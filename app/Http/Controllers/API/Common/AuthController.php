<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Otp;
use App\Models\LoginLog;
use App\Models\AppSetting;
use App\Models\CustomLog;
use App\Models\DprLog;
use App\Models\FailedLoginAttempt;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Auth;
use DB;
use Exception;
use Mail;
use Spatie\Permission\Models\Permission;
use App\Mail\ForgotPasswordMail;
use App\Mail\PasswordUpdateMail;
use App\Mail\VerifyOtpMail;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\RateLimiter;
use App\Mail\TooManyAttemptMail;


class AuthController extends Controller
{

	//login attempt
	public function login(Request $request)
	{
		
		$email = base64_decode($request->email);
		$password = base64_decode($request->password);

		$checkOtpReq = Otp::where('email', $email)->whereNotNull('lock_till')->first();
		if($checkOtpReq && strtotime($checkOtpReq->lock_till) > time()) 
		{
			return response(prepareResult(true, ["account_locked"=> true, "time" => timeDiff($checkOtpReq->lock_till)], trans('translate.too_many_otp_requests')), config('httpcodes.unauthorized'));
		}
		if($checkOtpReq && !empty($checkOtpReq->lock_till) && strtotime($checkOtpReq->lock_till) < time()) 
		{
			$checkOtpReq->lock_till = null;
			$checkOtpReq->resent_count = 1;
			$checkOtpReq->save();
		}

		if (RateLimiter::tooManyAttempts(request()->ip(), env('LOGIN_ATTEMPT_LIMIT', 5))) {

			$seconds = RateLimiter::availableIn($this->throttleKey());

			//mail integrate here
			$user = User::first();
			$content = [
			    "name" => $user->name,
			    "body" => 'User with email address '.$email.' is trying brute force.',
			];

			if (env('IS_MAIL_ENABLE', false) == true) {
			   
			    $recevier = Mail::to($user->email)->send(new TooManyAttemptMail($content));
			}
			return response(prepareResult(true, ["account_locked"=> true, "time"=> $seconds], trans('translate.too_many_attempts')), config('httpcodes.unauthorized'));
        }

		$validation = \Validator::make($request->all(),[ 
			'email'     => 'required',
			'password'  => 'required',
		]);

		if ($validation->fails()) {
			return response(prepareResult(true, $validation->messages(), $validation->messages()->first()), config('httpcodes.bad_request'));
		}

		try 
		{
			$allUsers = User::get();
			$user = false;
			foreach ($allUsers as $key => $matchEmail) {
				if($matchEmail->email==$email)
				{
					$user = $matchEmail;
					break;
				}
			}
			if (!$user)  {
				return response(prepareResult(true, [], trans('translate.user_not_exist')), config('httpcodes.not_found'));
			}
            ////create-log
			$customLog = new CustomLog;
			$customLog->created_by = $user->id;
			$customLog->type = 'login';
			$customLog->event = 'login';
			$customLog->status = 'failed';

			
			$loginCheck = DB::table('oauth_access_tokens')->where('user_id', $user->id)->first();
			/*if(!empty($loginCheck))
			{
				if($request->logout_from_all_devices == 'yes')
				{
					DB::table('oauth_access_tokens')->where('user_id', $user->id)->delete();
				}
				else
				{
					$customLog->failure_reason = trans('translate.user_already_logged_in');
					$customLog->save();
					RateLimiter::hit(request()->ip(), env('LOCK_TIME_IN_SEC_INCORRECT_PWD_TIME', 3600));
					return response(prepareResult(true, ['is_logged_in'=> true], trans('translate.user_already_logged_in')), config('httpcodes.not_found'));
				}
			}*/

			if(in_array($user->status, [0,2])) {
				$customLog->failure_reason = trans('translate.account_is_inactive');
				$customLog->save();
				RateLimiter::hit(request()->ip(), env('LOCK_TIME_IN_SEC_INCORRECT_PWD_TIME', 3600));
				return response(prepareResult(true, [], trans('translate.account_is_inactive')), config('httpcodes.unauthorized'));
			}

			if(Hash::check($password, $user->password)) {
                
				if(env('IS_MAIL_ENABLE', false) == true)
                {
                    $otpSend = rand(100000,999999);
                }
                else
                {
                    $otpSend = 123456;
                }
                
				$otp = Otp::where('email',$email)->first();
				if(!$otp)
				{
					$otp = new Otp; 
				}
				$otp->email = $email;
				$otp->otp =  base64_encode($otpSend);
				$otp->otp_expired =  date('Y-m-d H:i:s', strtotime("5 minutes", time()));
				$otp->resent_count = $otp->resent_count + 1;
				$otp->save();
				if($otp->resent_count>=env('OTP_ATTEMPT_LIMIT', 3))
				{
					$otp->lock_till = date("Y-m-d H:i:s", strtotime("10 minutes", time()));
					$otp->save();
				}
				
				$content = [
					"name" => $user->name,
					"body" => 'your verification otp is : '.$otpSend,
				];

				if (env('IS_MAIL_ENABLE', false) == true) {

					$recevier = Mail::to($email)->send(new VerifyOtpMail($content));
				}

				$customLog->status = 'success';
				$customLog->save();
				return response(prepareResult(false, [], trans('translate.otp_sent')),config('httpcodes.success'));
			} else {
				$customLog->failure_reason = trans('translate.invalid_username_and_password');
				$customLog->save();
				RateLimiter::hit(request()->ip(), env('LOCK_TIME_IN_SEC_INCORRECT_PWD_TIME', 3600));
				return response(prepareResult(true, [], trans('translate.invalid_username_and_password')),config('httpcodes.unauthorized'));
			}
		} catch (\Throwable $e) {
			\Log::error($e);
			return response(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
		}
	}

	/**
     * for checking RateLimiter by IP address
     */
    public function throttleKey()
    {
        return \Str::lower(request()->ip());
    }

	/**
     * Otp verification on the specified resource in storage.
     */

	public function verifyOtp(Request $request)
	{
		$validation = \Validator::make($request->all(),[
			'otp'     => 'required',
		]);

		if ($validation->fails()) {
			return response(prepareResult(true, $validation->messages(), $validation->messages()->first()), config('httpcodes.bad_request'));
		}

		try 
		{

			//$email = $request->email;
			$email = base64_decode($request->email);
			$otp = base64_decode($request->otp);
			$allUsers = User::get();

			$user = false;
			foreach ($allUsers as $key => $matchEmail) {
				if($matchEmail->email==$email)
				{
					$user = $matchEmail;
					break;
				}
			}
            if (!$user)  {
                return response()->json(prepareResult(true, [], trans('translate.user_not_exist')), config('httpcodes.not_found'));
            }

            $checkOtpReq = Otp::where('email', $email)->whereNotNull('lock_till')->first();
            if($checkOtpReq && strtotime($checkOtpReq->lock_till) > time()) 
            {
                return response(prepareResult(true, ["account_locked"=> true, "time" => timeDiff($checkOtpReq->lock_till)], trans('translate.too_many_otp_attempts')), config('httpcodes.unauthorized'));
            }

            if($checkOtpReq && !empty($checkOtpReq->lock_till) && strtotime($checkOtpReq->lock_till) < time()) 
            {
                $checkOtpReq->otp_expired = null;
                $checkOtpReq->lock_till = null;
                $checkOtpReq->resent_count = 0;
                $checkOtpReq->save();
            }

			//create log
			$customLog = new CustomLog;
			$customLog->type = 'login';
			$customLog->event = 'otp-verify';
			$customLog->status = 'failed';
			$customLog->created_by = $user->id;
			
			$otpCheck = Otp::where('email',$email)->first();

            if(!$otpCheck)
            {
                return response()->json(prepareResult(true, [], trans('translate.otp_not_exist')), config('httpcodes.not_found'));
            }

            if (base64_decode($otpCheck->otp) != $otp)  {
                //update validation case
                $otpCheck->resent_count = $otpCheck->resent_count + 1;
                $otpCheck->updated_at = $otpCheck->updated_at;
                $otpCheck->save();

                if($otpCheck->resent_count>=env('OTP_ATTEMPT_LIMIT', 3))
                {
                    $otpCheck->lock_till = date("Y-m-d H:i:s", strtotime("10 minutes", time()));
                    $otpCheck->updated_at = $otpCheck->updated_at;
                    $otpCheck->save();
                }

                $customLog->failure_reason = trans('translate.invalid_otp');
                $customLog->save();
                return response()->json(prepareResult(true, [], trans('translate.invalid_otp')), config('httpcodes.not_found'));
            }
            elseif((strtotime($otpCheck->updated_at) + 600) < time())
            {
                $customLog->failure_reason = trans('translate.otp_expired');
                $customLog->save();

                //deleted expired OTP
                $otpCheck->delete();
                return response()->json(prepareResult(true, [], trans('translate.otp_expired')), config('httpcodes.not_found'));
            }

            $accessToken = $user->createToken('authToken')->accessToken;
    		$user['access_token'] = $accessToken;
    		$appSetting = \DB::table('app_settings')->first();
    		$user['app_name'] = $appSetting->app_name;
    		$user['app_logo'] = $appSetting->app_logo;
    		$user['disclaimer_text'] = $appSetting->disclaimer_text;
    		$role   = Role::where('id', $user->role_id)->first();
    		$user['roles']    = $role;
    		$user['permissions']  = $role->permissions()->select('id','name as action','group_name as subject','se_name')->get();

            ////create-log

			$log = new LoginLog;
			$log->user_id = $user->id;
			$log->save();

			$customLog->status = 'success';
			$customLog->save();

			//OTP record deleted
            $otpCheck->delete();

			return response(prepareResult(false, $user, trans('translate.request_successfully_submitted')),config('httpcodes.success'));
		} catch (\Throwable $e) {
			\Log::error($e);
			return response(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
		}
	}
	
	/**
     * Log Out on the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param Request  $request
     * @return \Illuminate\Http\Response
     */
	public function logout(Request $request)
	{
		if (Auth::check()) 
		{
			try
			{
                //create-log
				$customLog = new CustomLog;
				$customLog->created_by = auth()->id();
				$customLog->type = 'logout';
				$customLog->event = 'logout';
				$customLog->status = 'success';
				$customLog->save();

				$token = Auth::user()->token();
				$token->revoke();
				auth('api')->user()->tokens->each(function ($token, $key) {
					$token->delete();
				});

				return response(prepareResult(false, [], trans('translate.logout_message')), config('httpcodes.success'));
			}
			catch (\Throwable $e) {
				\Log::error($e);
				return response(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
			}
		}
		return response(prepareResult(true, [], trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
	}

	//Send Fassword Reset Link
	public function forgotPassword(Request $request)
	{
		$validation = \Validator::make($request->all(),[ 
			'email'     => 'required|email'
		]);

		if ($validation->fails()) {
			return response(prepareResult(true, $validation->messages(), $validation->messages()->first()), config('httpcodes.bad_request'));
		}

		try 
		{
			$email = $request->email;
			$allUsers = User::get();
			$user = false;
			foreach ($allUsers as $key => $matchEmail) {
				if($matchEmail->email==$email)
				{
					$user = $matchEmail;
					break;
				}
			}
			if (!$user) {
				return response(prepareResult(true, [], trans('translate.user_not_exist')), config('httpcodes.not_found'));
			}
            //create-log
			$customLog = new CustomLog;
			$customLog->created_by = $user->id;
			$customLog->type = 'forgot-password';
			$customLog->event = 'forgot-password';
			
			if(in_array($user->status, [0,2])) {
				$customLog->status = 'failed';
				$customLog->failure_reason = trans('translate.account_is_inactive');
				$customLog->save();
				return response(prepareResult(true, [], trans('translate.account_is_inactive')), config('httpcodes.unauthorized'));
			}

            //Delete if entry exists
			DB::table('password_resets')->where('email', $request->email)->delete();

			$token = Str::random(64);
			DB::table('password_resets')->insert([
				'email' => $request->email, 
				'token' => $token, 
				'created_at' => Carbon::now()
			]);

			$customLog->status = 'sucess';
			$customLog->save();

			$baseRedirURL = env('FRONT_URL');
			$app_settings = \DB::table('app_settings')->select('id','app_name')->first();
			$content = [
				"name" => $user->name,
                // "passowrd_link" => $baseRedirURL.'/reset-password/'.$token,
				"body" => 'Please click <a href='.$baseRedirURL.'/reset-password/'.$token.' style="color: #000;font-size: 18px;text-decoration: underline, font-family: Roboto Condensed, sans-serif;"  target="_blank">here </a> to reset your password for '.$app_settings->app_name.'',
			];

			if (env('IS_MAIL_ENABLE', false) == true) {

				$recevier = Mail::to($request->email)->send(new ForgotPasswordMail($content));
			}
			return response(prepareResult(false, $request->email, trans('translate.password_reset_link_send_to_your_mail')),config('httpcodes.success'));

		} catch (\Throwable $e) {
			\Log::error($e);
			return response(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
		}
	}

	/**
     * Reset Password on the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param Request  $request
     * @return \Illuminate\Http\Response
     */
	// update password 
	public function updatePassword(Request $request)
	{
		$validation = \Validator::make($request->all(),[ 
			'password'  => 'required|min:8',
			'token'     => 'required'
		]);

		if ($validation->fails()) {
			return response(prepareResult(true, $validation->messages(), $validation->messages()->first()), config('httpcodes.bad_request'));
		}

		try 
		{
			$tokenExist = DB::table('password_resets')
			->where('token', $request->token)
			->first();
			if (!$tokenExist) {
				return response(prepareResult(true, [], trans('translate.token_expired_or_not_found')), config('httpcodes.unauthorized'));
			}

			$email = $tokenExist->email;
			$allUsers = User::get();
			$user = false;
			foreach ($allUsers as $key => $matchEmail) {
				if($matchEmail->email==$email)
				{
					$user = $matchEmail;
					break;
				}
			}

			if (!$user) {
				return response(prepareResult(true, [], trans('translate.user_not_exist')), config('httpcodes.not_found'));
			} 

			if($user->role_id == 1)
			{
				$validation = \Validator::make($request->all(),[ 
					'password'      => 'min:15'
				]);
			}
			else
			{
				$validation = \Validator::make($request->all(),[ 
					'password'      => 'min:8'
				]);
			}
            //create-log
			$customLog = new CustomLog;
			$customLog->created_by = $user->id;
			$customLog->type = 'update-password';
			$customLog->event = 'update-password';
			$customLog->status = 'failed';
			if ($validation->fails()) {
				$customLog->failure_reason = $validation->messages()->first();
				$customLog->save();
				return response(prepareResult(true, $validation->messages(), $validation->messages()->first()), config('httpcodes.bad_request'));
			}

			if(empty(validatePassword($request->password)))
			{
				$customLog->failure_reason = trans('translate.password_format_invalid');
				$customLog->save();
				return response(prepareResult(true, [], trans('translate.password_format_invalid')), config('httpcodes.bad_request'));
			}
			

			if(Hash::check($request->password, $user->password)) {
				$customLog->failure_reason = trans('translate.choose_other_password');
				$customLog->save();
				return response(prepareResult(true, ['password_denied'=>true], trans('translate.choose_other_password')), config('httpcodes.bad_request'));
			}           

			if(in_array($user->status, [0,2])) {
				$customLog->failure_reason = trans('translate.account_is_inactive');
				$customLog->save();
				return response(prepareResult(true, [], trans('translate.account_is_inactive')), config('httpcodes.unauthorized'));
			}

			$user->update([
				'password' => Hash::make($request->password),
				'password_last_updated' => date('Y-m-d')
			]);

			$customLog->status = 'success';
			$customLog->save();

			DB::table('password_resets')->where(['email'=> $tokenExist->email])->delete();
			$app_settings = \DB::table('app_settings')->select('id','app_name')->first();
			$baseRedirURL = env('FRONT_URL');
			$content = [
				"name" => $user->name,
				"body" => 'The password for your account on '.$app_settings->app_name.' has been reset <br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  Please click here to <a href='.$baseRedirURL.' style="color: #000;font-size: 18px;text-decoration: underline, font-family: Roboto Condensed, sans-serif;"  target="_blank">login </a>.',
			];

			if (env('IS_MAIL_ENABLE', false) == true) {
				
				$recevier = Mail::to($user->email)->send(new PasswordUpdateMail($content));
			}

			return response(prepareResult(false, $tokenExist->email, trans('translate.password_changed')),config('httpcodes.success'));

		} catch (\Throwable $e) {
			\Log::error($e);
			return response(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
		}
	}

	/**
     * Change Password  on the specified User in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param Request  $request
     * @return \Illuminate\Http\Response
     */

	// chane password 
	public function changePassword(Request $request)
	{
		try 
		{
			$user = Auth::user();  

			if($user->role_id == 1)
			{
				$validation = \Validator::make($request->all(),[ 
					'old_password'  => 'required',
					'password'      => 'required|min:15'
				]);
			}
			else
			{
				$validation = \Validator::make($request->all(),[ 
					'old_password'  => 'required',
					'password'      => 'required|min:8'
				]);
			}
            //create-log
			$customLog = new CustomLog;
			$customLog->created_by = $user->id;
			$customLog->type = 'change-password';
			$customLog->event = 'change-password';
			$customLog->status = 'failed';

			if ($validation->fails()) {
				$customLog->failure_reason = $validation->messages()->first();
				$customLog->save();
				return response(prepareResult(true, $validation->messages(), $validation->messages()->first()), config('httpcodes.bad_request'));
			}

			if(empty(validatePassword($request->password)))
			{
				$customLog->failure_reason = trans('translate.password_format_invalid');
				$customLog->save();
				return response(prepareResult(true, [], trans('translate.password_format_invalid')), config('httpcodes.bad_request'));
			}

			if(Hash::check($request->password, auth()->user()->password)) {
				$customLog->failure_reason = trans('translate.choose_other_password');
				$customLog->save();
				return response(prepareResult(true, ['password_denied'=>true], trans('translate.choose_other_password')), config('httpcodes.bad_request'));
			}

			if(in_array($user->status, [0,2])) {
				$customLog->failure_reason = trans('translate.account_is_inactive');
				$customLog->save();
				return response(prepareResult(true, [], trans('translate.account_is_inactive')), config('httpcodes.unauthorized'));
			}

			if(Hash::check($request->old_password, $user->password)) {
				$email = auth()->user()->email;
				$user->update(['password' => Hash::make($request->password),'password_last_updated' => date('Y-m-d')]);

				$content = [
					"name" => auth()->user()->name,
					"body" => 'Your Password has been updated Successfully!',
				];

				if (env('IS_MAIL_ENABLE', false) == true) {
					
					$recevier = Mail::to(auth()->user()->email)->send(new PasswordUpdateMail($content));
				}
				$customLog->status = 'success';
				$customLog->save();
			}
			else
			{
				$customLog->failure_reason = trans('translate.old_password_not_matched');
				$customLog->save();
				return response(prepareResult(true, [], trans('translate.old_password_not_matched')),config('httpcodes.unauthorized'));
			}
			
			return response(prepareResult(false, $request->email, trans('translate.password_changed')),config('httpcodes.success'));

		} catch (\Throwable $e) {
			\Log::error($e);
			return response(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
		}
	}

	/**
     *Reset Password Token-verification on the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param Request  $request
     * @return \Illuminate\Http\Response
     */
	public function resetPassword($token)
	{
		if($token){
			$usertoken =[
				'token'=> $token,
			];
			return response(prepareResult(false, $usertoken, trans('translate.Token')),config('httpcodes.success'));
		} else {
			return response(prepareResult(true, [], trans('translate.token_expired_or_not_found')),config('httpcodes.bad_request'));
		}
	}
	
	// Not Authorized----need to login 
	public function unauthorized(Request $request)
	{
		return response(prepareResult(false,[],trans('translate.unauthorized')), config('httpcodes.unauthorized'));
	}

	/**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

	//get profile data
	public function myProfile(Request $request)
	{
		try {
			$user = auth()->user();
			return response(prepareResult(false, $user, trans('translate.fetched_detail')),config('httpcodes.success'));

		} catch (\Throwable $e) {
			\Log::error($e);
			return response(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
		}
	}

	/**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
	//update profile data
	public function updateProfile(Request $request)
	{
		$validation = \Validator::make($request->all(),[ 
			'name'      => 'required|regex:/^[a-zA-Z0-9-_ ]+$/',
			'email'     => 'email|required|unique:users,email,'.auth()->id(),
			'mobile_number'    => 'nullable|min:10'
		]);
		if ($validation->fails()) {
			return response(prepareResult(true, $validation->messages(), $validation->messages()->first()), config('httpcodes.bad_request'));
		}

		try {
			$user = auth()->user();
			if (!$user) {
				return response(prepareResult(true, [], trans('translate.user_not_exist')), config('httpcodes.not_found'));
			}

			$user->name             = $request->name;
			$user->email            = $request->email;
			$user->mobile_number    = $request->mobile_number;
			$user->address          = $request->address;
			$user->avatar           = $request->avatar;
			$user->save();
			
			return response(prepareResult(false, $user, trans('translate.profile_updated')),config('httpcodes.success'));

		} catch(Exception $exception) {
			\Log::error($exception);
			return response(prepareResult(false,$this->intime,$exception->getMessage(),trans('translate.something_went_wrong')),config('httpcodes.internal_server_error'));
		}
	}

}
