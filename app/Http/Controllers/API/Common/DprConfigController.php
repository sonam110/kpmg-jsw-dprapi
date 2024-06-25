<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DprConfig;
use App\Models\Notification;
use Validator;
use Auth;
use Exception;
use DB;;
class DprConfigController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:dpr-config-browse');
        $this->middleware('permission:dpr-config');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    //get dpr configs list
    public function dprConfigs(Request $request)
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
            $query = DprConfig::orderby($column,$dir)->with('vendor','Project','WorkPackage','DprMap')
            ->whereHas('vendor',function($q){
              $q->where('status', 1);
            })
            ->whereHas('project',function($q){
              $q->where('status', 1);
            })
            ->whereHas('WorkPackage',function($q){
              $q->where('status', 1);
            });

            if(!empty(auth()->user()->vendor_id)){
                $query->where('vendor_id',auth()->user()->vendor_id);
            }

            if(!empty($request->profile_name))
            {
                $query->where('profile_name', 'LIKE', '%'.$request->profile_name.'%');
            }
            if(!empty($request->project_id))
            {
                $query->where('project_id',$request->project_id);
            }
            if(!empty($request->vendor_id))
            {
                $query->where('vendor_id',$request->vendor_id);
            }
            if(!empty($request->work_pack_id))
            {
                $query->where('work_pack_id',$request->work_pack_id);
            }
            
            if(!empty($request->status))
            {
                $query->where('status',$request->status);
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
            return response(prepareResult(false, $query, trans('translate.dpr_config_list')), config('httpcodes.success'));
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
    //create new dpr config
    public function store(Request $request)
    {
        $validation = \Validator::make($request->all(), [
            'project_id'      => 'required|exists:projects,id',
            'vendor_id'      => 'required|exists:vendors,id',
            'work_pack_id'      => 'required|exists:work_packages,id',
            'profile_name'      => 'required|regex:/^[a-zA-Z0-9-_ ]+$/',
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), $validation->messages()->first()), config('httpcodes.bad_request'));
        }
        $checkAlready = DprConfig::where('project_id',$request->project_id)->where('vendor_id',$request->vendor_id)->where('work_pack_id',$request->work_pack_id)->first();
        if(!empty($checkAlready)){
            return response(prepareResult(true,[],trans('translate.dpr_vendor_dublicacy')), config('httpcodes.bad_request')); 
        }

        DB::beginTransaction();
        try {
            $dprConfig = new DprConfig;
            $dprConfig->project_id = $request->project_id;
            $dprConfig->vendor_id = $request->vendor_id;
            $dprConfig->work_pack_id = $request->work_pack_id;
            $dprConfig->profile_name = $request->profile_name;
            $dprConfig->slug = \Str::slug($request->profile_name);
            $dprConfig->user_id = auth()->id();
            $dprConfig->save();

            //notify admin about new dpr config
            $notification = new Notification;
            $notification->user_id              = User::first()->id;
            $notification->sender_id            = auth()->id();
            $notification->status_code          = 'success';
            $notification->type                = 'Dpr-Config';
            $notification->event                = 'Created';
            $notification->title                = 'New Dpr-Config Created';
            $notification->message              = 'New Dpr-Config with profile name '.$dprConfig->profile_name.' created.';
            $notification->read_status          = false;
            $notification->data_id              = $dprConfig->id;
            $notification->save();

            DB::commit();
            return response(prepareResult(false, $dprConfig, trans('translate.dpr_config_created')),config('httpcodes.created'));
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
    //detail view dpr config
    public function show($id)
    {
        try {
            $dprConfig = DprConfig::select('*')->with('vendor','Project','WorkPackage','DprMap','DprLogs','dprImport:id,dpr_config_id,data_date')
                ->find($id);
            // $dprConfig['data_date'] = @$dprConfig->dprImport->data_date;
            if($dprConfig)
            {
                return response(prepareResult(false, $dprConfig, trans('translate.dpr_config_detail')), config('httpcodes.success'));
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
    //update dpr config
    public function update(Request $request, $id)
    {
         $validation = \Validator::make($request->all(), [
            'project_id'      => 'required|exists:projects,id',
            'vendor_id'      => 'required|exists:vendors,id',
            'work_pack_id'      => 'required|exists:work_packages,id',
            'profile_name'      => 'required|regex:/^[a-zA-Z0-9-_ ]+$/',
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), $validation->messages()->first()), config('httpcodes.bad_request'));
        }

        DB::beginTransaction();
        try {
            $dprConfig = DprConfig::where('id',$id)->first();
            if(!$dprConfig)
            {
                return response(prepareResult(true, [],trans('translate.record_not_found')), config('httpcodes.not_found'));
            }
            $checkAlready = DprConfig::where('id','!=',$id)->where('project_id',$request->project_id)->where('vendor_id',$request->vendor_id)->where('work_pack_id',$request->work_pack_id)->first();
            if(!empty($checkAlready)){
                return response(prepareResult(true,[],trans('translate.dpr_vendor_dublicacy')), config('httpcodes.bad_request')); 
            }
            $dprConfig->project_id = $request->project_id;
            $dprConfig->vendor_id = $request->vendor_id;
            $dprConfig->work_pack_id = $request->work_pack_id;
            $dprConfig->profile_name = $request->profile_name;
            $dprConfig->slug = \Str::slug($request->profile_name);
            $dprConfig->save();

            DB::commit();
            return response(prepareResult(false, $dprConfig, trans('translate.dpr_config_updated')),config('httpcodes.success'));
        } catch (\Throwable $e) {
            \Log::error($e);
            DB::rollback();
            return response(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    //destroy dpr config data
    public function destroy($id)
    {
        try {
            $dprConfig= DprConfig::where('id',$id)->first();
            if (!is_object($dprConfig)) {
                 return response(prepareResult(true, [],trans('translate.record_not_found')), config('httpcodes.not_found'));
            }
            
            $deletedprConfig= $dprConfig->delete();
            return response(prepareResult(false, [], trans('translate.dpr_config_deleted')), config('httpcodes.success'));
        }
        catch(Exception $exception) {
            return response(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }
    }

     /**
     * Action on the specified resource from storage.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function action(Request $request)
    {
        $validation = \Validator::make($request->all(), [
            'ids'      => 'required',
            'action'      => 'required',
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), $validation->messages()->first()), config('httpcodes.bad_request'));
        }
        DB::beginTransaction();
        try 
        {
            $ids = $request->ids;
            $message = trans('translate.invalid_action');
            if($request->action == 'delete')
            {
                $configs = DprConfig::whereIn('id',$ids)->delete();
                $message = trans('translate.dpr_config_deleted');
            }
            elseif($request->action == 'inactive')
            {
                DprConfig::whereIn('id',$ids)->update(['status'=>"2"]);
                $message = trans('translate.dpr_config_inactivated');
            }
            elseif($request->action == 'active')
            {
                DprConfig::whereIn('id',$ids)->update(['status'=>"1"]);
                $message = trans('translate.dpr_config_activated');
            }
            $configs = DprConfig::whereIn('id',$ids)->get();
            DB::commit();
            return response(prepareResult(false, $configs, $message), config('httpcodes.success'));
        }
        catch (\Throwable $e) {
            \Log::error($e);
            return response(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }
    }
}
