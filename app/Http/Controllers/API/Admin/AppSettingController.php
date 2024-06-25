<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppSetting;
use App\Models\CustomLog;
use Validator;
use Auth;
use Exception;
use DB;


class AppSettingController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('permission:app-setting-edit', ['only' => ['update','appSetting']]);
    // }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    //fetching Appsetting Data

    public function appSetting(Request $request)
    {
        try {
            $appSetting = AppSetting::select('*')->where('id','1')->first();
            if($appSetting)
            {
                return response(prepareResult(false, $appSetting, trans('translate.app_setting_fetched')), config('httpcodes.success'));
            }
            return response(prepareResult(true, [], trans('translate.record_not_found')), config('httpcodes.not_found'));
        } catch (\Throwable $e) {
            \Log::error($e);
            return response(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    //updating Appsetting or creating new if not exists
    public function updateSetting(Request $request)
    {
        $validation = \Validator::make($request->all(), [
            'app_name'      => 'required|regex:/^[a-zA-Z0-9-_ ]+$/',
            'app_logo'   => 'required',
            'email'   => 'required|email',
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), $validation->messages()->first()), config('httpcodes.bad_request'));
        }

        DB::beginTransaction();
        try {
            // $appSetting = AppSetting::where('id','1')->first();
            $appSetting = AppSetting::first(); 
            //create-log
            $customLog = new CustomLog;
            $customLog->created_by = (Auth::check()) ? auth()->id() :'1';
            $customLog->type = 'app-setting';
            $customLog->event = 'update';
            
            if(!$appSetting)
            {
                $customLog->status = 'failed';
                $customLog->failure_reason = trans('translate.record_not_found');
                $customLog->save();
                DB::commit();
                return response(prepareResult(true, [],trans('translate.record_not_found')), config('httpcodes.not_found'));
            }

            $customLog->status = 'success';
            $customLog->last_record_before_edition = json_encode($appSetting);
            $customLog->save();

            $appSetting->app_name = $request->app_name;
            $appSetting->description = $request->description;
            $appSetting->app_logo  = $request->app_logo;
            $appSetting->email = $request->email;
            $appSetting->mobile_no = $request->mobile_no;
            $appSetting->address = $request->address;
            $appSetting->disclaimer_text = $request->disclaimer_text;
            $appSetting->log_expiry_days = $request->log_expiry_days;
            $appSetting->save();


            DB::commit();
            return response(prepareResult(false, $appSetting, trans('translate.app_setting_updated')),config('httpcodes.success'));
        } catch (\Throwable $e) {
            \Log::error($e);
            DB::rollback();
            return response(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }
    }
}
