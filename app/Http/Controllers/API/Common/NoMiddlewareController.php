<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\User;
use App\Models\Vendor;
use App\Models\WorkPackage;
use App\Models\Project;
use App\Models\ItemDescriptionMaster;
use App\Models\DprConfig;
use Validator;
use Auth;
use Exception;
use DB;

class NoMiddlewareController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    //Dpr Config Listing
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

            if(!empty(auth()->user()->dpr_config_ids))
            {
                $dpr_config_ids = explode(',',auth()->user()->dpr_config_ids);
                $query->whereIn('dpr_configs.id',$dpr_config_ids);
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    //listing projects   
    public function projects(Request $request)
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
            $query = Project::orderby($column,$dir);
            
            if(!empty($request->name))
            {
                $query->where('name', 'LIKE', '%'.$request->name.'%');
            }
            if(!empty($request->projectId))
            {
                $query->where('projectId',$request->projectId);
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

            return response(prepareResult(false, $query, trans('translate.project_list')), config('httpcodes.success'));
        } catch (\Throwable $e) {
            \Log::error($e);
            return response(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    //listing work package
    public function workPackages(Request $request)
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
            $query = WorkPackage::orderby($column,$dir);
            
            if(!empty($request->name))
            {
                $query->where('name', 'LIKE', '%'.$request->name.'%');
            }
            if(!empty($request->workPackId))
            {
                $query->where('workPackId',$request->workPackId);
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

            return response(prepareResult(false, $query, trans('translate.work_package_list')), config('httpcodes.success'));
        } catch (\Throwable $e) {
            \Log::error($e);
            return response(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    //listing vendors
    public function vendors(Request $request)
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
            $query = Vendor::orderby($column,$dir);
            
            if(!empty($request->name))
            {
                $query->where('name', 'LIKE', '%'.$request->name.'%');
            }
            
            if(!empty($request->status))
            {
                $query->where('status',$request->status);
            }
            if(!empty($request->vendor_id))
            {
                $query->where('id', $request->vendor_id);
            }

            if(!empty(auth()->user()->dpr_config_ids))
            {
                $dpr_config_ids = explode(',',auth()->user()->dpr_config_ids);
                $getVendorsId = \DB::table('dpr_configs')
                    ->whereIn('id', $dpr_config_ids)
                    ->pluck('vendor_id');
                $query->whereIn('id',$getVendorsId);
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

            return response(prepareResult(false, $query, trans('translate.vendor_list')), config('httpcodes.success'));
        } catch (\Throwable $e) {
            \Log::error($e);
            return response(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }
    }

    /*  Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    //listing Item Description No permisssion required
    public function itemDescList(Request $request)
    {
        try {
            $column = 'title';
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
            $query = ItemDescriptionMaster::orderby($column,$dir);
            
            if(!empty($request->title))
            {
                $query->where('title', 'LIKE', '%'.$request->title.'%');
            }
            
            
            if(!empty($request->created_by))
            {
                $query->where('created_by', $request->created_by);
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

            return response(prepareResult(false, $query, trans('translate.item_desc_list')), config('httpcodes.success'));
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
    //detail view dpr config
    public function dprConfigShow($id)
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
     public function vendorWorkPack(Request $request)
    {
        try {
            $column = 'dpr_configs.id';
            $dir = 'Desc';
            $query = DprConfig::select(array('dpr_configs.id','dpr_configs.vendor_id','dpr_configs.work_pack_id','vendors.id as vendor_id','vendors.name as vendor_name','work_packages.id as work_pack_id','work_packages.name as work_package_name'))->orderby($column,$dir)
            ->join('vendors','dpr_configs.vendor_id','vendors.id')
            ->join('work_packages','dpr_configs.work_pack_id','work_packages.id')
            ->groupBy(['dpr_configs.vendor_id','dpr_configs.work_pack_id']);

            if(!empty(auth()->user()->dpr_config_ids))
            {
                $dpr_config_ids = explode(',',auth()->user()->dpr_config_ids);
                $query->whereIn('dpr_configs.id',$dpr_config_ids);
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
            return response(prepareResult(false, $query, trans('translate.work_package_list')), config('httpcodes.success'));
        } catch (\Throwable $e) {
            \Log::error($e);
            return response(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }
    }
}
