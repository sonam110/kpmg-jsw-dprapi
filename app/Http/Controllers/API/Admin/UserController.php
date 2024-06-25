<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Module;
use App\Models\Notification;
use App\Models\AssigneModule;
use App\Models\DprConfig;
use Validator;
use Auth;
use Exception;
use DB;
use Mail;
use Str;
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:user-browse',['only' => ['users']]);
        $this->middleware('permission:user-add', ['only' => ['store']]);
        $this->middleware('permission:user-edit', ['only' => ['update','userAction']]);
        $this->middleware('permission:user-read', ['only' => ['show']]);
        $this->middleware('permission:user-delete', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    //users list
    public function users(Request $request)
    {
        try {
            $column = 'id';
            $dir = 'Desc';
            if(!empty($request->sort))
            {
                if(!empty($request->sort['column']))
                {
                    $column = $request->sort['column'];
                }
                if(!empty($request->sort['dir']))
                {
                    $dir = $request->sort['dir'];
                }
            }
            $query = User::whereNotIn('role_id',['1'])->with('vendor','role:id,name,se_name')->orderBy($column,$dir);
            
            if(!empty($request->email))
            {
                $checkEmails = User::select('id','email')
                    ->get();
                $ids = [];
                foreach ($checkEmails as $key => $checkEmail) 
                {
                    if($checkEmail->email==$request->email)
                    {
                        $ids[] = $checkEmail->id;
                    }
                }
                $query->whereIn('id', $ids);
            }

            if(!empty($request->name))
            {
                $query->where('name', 'LIKE', '%'.$request->name.'%');
            }
            if(!empty($request->mobile_number))
            {
                $query->where('mobile_number', 'LIKE', '%'.$request->mobile_number.'%');
            }
            if(!empty($request->status))
            {
                $query->where('status', $request->status);
            }
            if(!empty($request->vendor_id))
            {
                $query->where('vendor_id', $request->vendor_id);
            }

            if(!empty($request->per_page_record))
            {
                $perPage = $request->per_page_record;
                $page = $request->input('page', 1);
                $total = $query->count();
                $result = $query->offset(($page - 1) * $perPage)->limit($perPage)->get();

                $pagination =  [
                    'data' => $result,
                    'total' => $total,
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'last_page' => ceil($total / $perPage)
                ];
                $query = $pagination;
            }
            else
            {
                $query = $query->get();
            }

            return response(prepareResult(false, $query, trans('translate.user_list')), config('httpcodes.success'));
        } catch (\Throwable $e) {
            \Log::error($e);
            return response(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }
    }

    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    //create new user
    public function store(Request $request)
    {
        $validation = \Validator::make($request->all(), [
            'name'      => 'required|regex:/^[a-zA-Z0-9-_ ]+$/',
            'email'     => 'required|email|unique:users,email',
            // 'password'  => 'required|string|min:6',
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), $validation->messages()->first()), config('httpcodes.bad_request'));
        }

        if(checkUserExist($request->email))
        {
            return response(prepareResult(true, trans('translate.user_already_exist_with_this_email'), trans('translate.user_already_exist_with_this_email')), config('httpcodes.internal_server_error'));
        }

        DB::beginTransaction();
        try {
            $password = "Dpr@2023";
            $email = $request->email;

            $user = new User;
            $user->role_id = $request->role_id;
            $user->vendor_id = $request->vendor_id;
            $user->name = $request->name;
            $user->email  = $request->email;
            $user->password =  Hash::make($password);
            $user->mobile_number = $request->mobile_number;
            $user->address = $request->address;
            $user->avatar = $request->avatar;
            $user->dpr_config_ids = $request->dpr_config_ids;
            $user->created_by = auth()->id();
            $user->save();
            $user['id'] = $user->id;

            //Role and permission sync
            $role = Role::where('id', $request->role_id)->first();
            $permissions = $role->permissions->pluck('name');
            
            $user->assignRole($role->name);
            foreach ($permissions as $key => $permission) {
                $user->givePermissionTo($permission);
            }

            //notify user of registration
            $notification = new Notification;
            $notification->user_id              = User::first()->id;
            $notification->sender_id            = auth()->id();
            $notification->status_code          = 'success';
            $notification->event                = 'Created';
            $notification->type                = 'User';
            $notification->title                = 'New User Created';
            $notification->message              = 'New User '.$user->name.' registered.';
            $notification->read_status          = false;
            $notification->data_id              = $user->id;
            $notification->save();

            //Delete if entry exists
            DB::table('password_resets')->where('email', $request->email)->delete();

            $token = \Str::random(64);
            DB::table('password_resets')->insert([
              'email' => $request->email, 
              'token' => $token, 
              'created_at' => \Carbon\Carbon::now()
            ]);

            $baseRedirURL = env('FRONT_URL');
            // Login credentials are following - email:'.$user->email.' , password:'.$randomNo.'.
            $content = [
                "name" => $user->name,
                "body" => 'You have been registered.<br>To reset your password Please click on the link -> <a href='.$baseRedirURL.'/reset-password/'.$token.' style="color: #000;font-size: 18px;text-decoration: underline, font-family: Roboto Condensed, sans-serif;"  target="_blank">Reset your password </a>',
            ];

            if (env('IS_MAIL_ENABLE', false) == true) {
               
                $recevier = Mail::to($request->email)->send(new WelcomeMail($content));
            }

            DB::commit();
            return response(prepareResult(false, $user, trans('translate.user_created')),config('httpcodes.created'));
        } catch (\Throwable $e) {
            \Log::error($e);
            DB::rollback();
            return response(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $userinfo = User::select('*')
                ->where('role_id','!=','1')
                ->with('vendor')
                ->find($id);
            $dpr_config_ids = (!empty($userinfo->dpr_config_ids)) ? explode(',',$userinfo->dpr_config_ids) :[];
            $role   = Role::where('id', $userinfo->role_id)->first();
            $query = DprConfig::select(array('dpr_configs.id','dpr_configs.vendor_id','dpr_configs.work_pack_id','vendors.id as vendor_id','vendors.name as vendor_name','work_packages.id as work_pack_id','work_packages.name as work_package_name'))
            ->join('vendors','dpr_configs.vendor_id','vendors.id')
            ->join('work_packages','dpr_configs.work_pack_id','work_packages.id')
            ->whereIn('dpr_configs.id',$dpr_config_ids)->get();;
            $userinfo['roles']    = $role;
            $userinfo['vendor_workpack']    = $query;
            if($userinfo)
            {
                return response(prepareResult(false, $userinfo, trans('translate.user_detail')), config('httpcodes.success'));
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
    //update existing user data
    public function update(Request $request, $id)
    {
        $validation = \Validator::make($request->all(), [
            'name'      => 'required|regex:/^[a-zA-Z0-9-_ ]+$/',
            'email'     => 'email|required|unique:users,email,'.$id,
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), $validation->messages()->first()), config('httpcodes.bad_request'));
        }

        DB::beginTransaction();
        try {
            $user = User::where('id',$id)->first();
        
            if(!$user)
            {
                return response(prepareResult(true, [], trans('translate.user_not_exist')), config('httpcodes.not_found'));
            }
            if($user->role_id=='1')
            {
                return response(prepareResult(true, [], trans('translate.record_not_found')), config('httpcodes.not_found'));
            }
            
            $user->role_id = $request->role_id;
            $user->vendor_id = $request->vendor_id;
            $user->name = $request->name;
            $user->email  = $request->email;
            $user->mobile_number = $request->mobile_number;
            $user->address = $request->address;
            $user->avatar = $request->avatar;
            $user->dpr_config_ids = $request->dpr_config_ids;
            $user->save();

            //delete old role and permissions
            DB::table('model_has_roles')->where('model_id', $user->id)->delete();
            DB::table('model_has_permissions')->where('model_id', $user->id)->delete();

            //Role and permission sync
            $role = Role::where('id', $request->role_id)->first();
            $permissions = $role->permissions->pluck('name');
            
            $user->assignRole($role->name);
            foreach ($permissions as $key => $permission) {
                $user->givePermissionTo($permission);
            }
           
            DB::commit();
            return response(prepareResult(false, $user, trans('translate.user_updated')),config('httpcodes.success'));
        } catch (\Throwable $e) {
            \Log::error($e);
            DB::rollback();
            return response(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Request $request  $id
     * @return \Illuminate\Http\Response
     */
    //action performed on user
    public function userAction(Request $request)
    {
        try {
            $validation = \Validator::make($request->all(), [
                'action'   => 'required',
                "ids"    => "required|array|min:1",
                "ids.*"  => "required|distinct|min:1|exists:users,id",

            ]);
           
            if ($validation->fails()) {
                return response(prepareResult(true, $validation->messages(), $validation->messages()->first()), config('httpcodes.bad_request'));
            }
            if($request->action =='active'){

                $userActive = User::whereIn('id',$request->ids)->update(['status'=>'1']);
                return response(prepareResult(false, [],trans('translate.user_activated')), config('httpcodes.success'));
            }
            if($request->action =='inactive'){

                $userDeActive = User::whereIn('id',$request->ids)->update(['status'=>'0']);
                return response(prepareResult(false, [], trans('translate.user_inactivated')), config('httpcodes.success'));
            }
            
        } catch (\Throwable $e) {
            \Log::error($e);
            return response(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }
    }
}
