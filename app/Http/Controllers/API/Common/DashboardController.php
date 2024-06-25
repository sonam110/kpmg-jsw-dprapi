<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Auth;
use DB;
use App\Models\Project;
use App\Models\DprConfig;
use App\Models\DprImport;
use App\Models\WorkPackage;
use App\Models\Vendor;
use App\Models\User;
use App\Models\ItemDescriptionMaster;
class DashboardController extends Controller
{
    /**
     * Dashboard Report Data.
     */
    public function dashboard()
    {
        try {
            $user = getUser();
            $data = [];
            if(!empty(auth()->user()->vendor_id)){
                $data['userCount'] = User::where('role_id','!=','1')->where('vendor_id',auth()->user()->vendor_id)->count();
                $data['totalProject'] = Project::join('dpr_configs','projects.id','dpr_configs.project_id')
                 ->where('dpr_configs.vendor_id',auth()->user()->vendor_id)
                 ->count();
                $data['totalVendor'] = Vendor::join('dpr_configs','vendors.id','dpr_configs.vendor_id')
                 ->where('dpr_configs.vendor_id',auth()->user()->vendor_id)
                 ->count();
                $data['TotalWorkPackage'] = WorkPackage::join('dpr_configs','work_packages.id','dpr_configs.work_pack_id')
                 ->where('dpr_configs.vendor_id',auth()->user()->vendor_id)
                 ->count();
                $data['dprUploads'] = DprImport::join('dpr_configs','dpr_imports.dpr_config_id','dpr_configs.id')
                ->whereDate('dpr_imports.created_at',date('Y-m-d'))
                ->where('dpr_configs.vendor_id',auth()->user()->vendor_id)
                ->groupBy('dpr_imports.random_no')
                ->get()
                ->count();

            }else{
                $data['userCount'] = User::whereNotIn('role_id',['1'])->count();
                $data['totalProject'] = Project::count();
                $data['totalVendor'] = Vendor::count();
                $data['TotalWorkPackage'] = WorkPackage::count();
                $data['dprUploads'] = DprImport::whereDate('created_at',date('Y-m-d'))->groupBy('random_no')->get()->count();
                
               
            }
                
            
            return response(prepareResult(false, $data, trans('translate.dashboard')), config('httpcodes.success'));
        } catch(Exception $exception) {
            return response(prepareResult(false, $exception->getMessage(),trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));   
        }  
    }

    
    //Dpr Import report.
    public function dprUploadsGraph(Request $request)
    {
        try
        {
            $year = $request->year ? $request->year : date('Y');
            $data = [];
            if(!empty($request->month))
            {
                $arr =  [
                        "Jan"=>1,
                        "Feb"=>2,
                        "Mar"=>3,
                        "Apr"=>4,
                        "May"=>5,
                        "Jun"=>6,
                        "Jul"=>7,
                        "Aug"=>8,
                        "Sep"=>9,
                        "Oct"=>10,
                        "Nov"=>11,
                        "Dec"=>12
                    ];
                foreach ($arr as $key => $value) {
                    if($request->month == $key)
                    {
                        $first_date = date($year.'-'.$value.'-1');
                        $month = $value;
                    }
                }
                $last_date =  date("t", strtotime($first_date));
                for ($d=1; $d<=$last_date; $d++) {
                    $date = date($year.'-'.sprintf("%02d", $month).'-'.sprintf("%02d", $d));
                    $dprUploads = \DB::table('dpr_imports')->join('dpr_configs', function($join) use ($request) {
                        $join->on('dpr_configs.id', '=', 'dpr_imports.dpr_config_id');
                    })
                    ->whereDate('dpr_imports.data_date',$date)->groupBy('dpr_imports.random_no');
                    // ->whereYear('dpr_imports.data_date',$year);
                    if(!empty(auth()->user()->dpr_config_ids))
                    {
                        $dpr_config_ids = explode(',',auth()->user()->dpr_config_ids);
                        $dprUploads = $dprUploads->whereIn('dpr_imports.dpr_config_id',$dpr_config_ids);
                    }
                    if(!empty($request->project_id))
                    {
                        $dprUploads = $dprUploads->where('dpr_configs.project_id', $request->project_id);
                    }
                    if(!empty($request->work_pack_id))
                    {
                        $dprUploads = $dprUploads->where('dpr_configs.work_pack_id', $request->work_pack_id);
                    }
                    if(!empty($request->vendor_id))
                    {
                        $dprUploads = $dprUploads->where('dpr_configs.vendor_id', $request->vendor_id);
                    }
                    $dprUploads = count($dprUploads->get());
                    
                    $data['date'][] = date('Y-m-d',strtotime($date));
                    $data['data'][] = $dprUploads;
                }
            }
            else{
                for ($m=1; $m<=12; $m++) {
                    $dprUploads = \DB::table('dpr_imports')->join('dpr_configs', function($join) use ($request) {
                        $join->on('dpr_configs.id', '=', 'dpr_imports.dpr_config_id');
                    })
                    ->whereMonth('dpr_imports.data_date',sprintf("%02d", $m))
                    ->whereYear('dpr_imports.data_date',$year)->groupBy('dpr_imports.random_no');

                    if(!empty(auth()->user()->dpr_config_ids))
                    {
                        $dpr_config_ids = explode(',',auth()->user()->dpr_config_ids);
                        $dprUploads = $dprUploads->whereIn('dpr_imports.dpr_config_id',$dpr_config_ids);
                    }
                    if(!empty($request->project_id))
                    {
                        $dprUploads = $dprUploads->where('dpr_configs.project_id', $request->project_id);
                    }
                    if(!empty($request->work_pack_id))
                    {
                        $dprUploads = $dprUploads->where('dpr_configs.work_pack_id', $request->work_pack_id);
                    }
                    if(!empty($request->vendor_id))
                    {
                        $dprUploads = $dprUploads->where('dpr_configs.vendor_id', $request->vendor_id);
                    }
                    
                    $dprCount = count($dprUploads->get());
                    $date = date('Y-'.$m.'-d');
                    $data['months'][] = date('M',strtotime($date));
                    $data['data'][] = $dprCount;
                }
               
            }
            return response(prepareResult(false, $data, trans('translate.dpr_uploads_graph')), config('httpcodes.success'));
        } catch(Exception $exception) {
            return response(prepareResult(false, $exception->getMessage(),trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));   
        } 
    }

    /**
     * Man Power report.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    //Man Power report.
    public function manpowerGraph(Request $request)
    {
        try
        {
            $year = $request->year ? $request->year : date('Y');
            $data = [];
            if(!empty($request->month))
            {
                $arr =  ["Jan"=>1,"Feb"=>2,"Mar"=>3,"Apr"=>4,"May"=>5,"Jun"=>6,"Jul"=>7,"Aug"=>8,"Sep"=>9,"Oct"=>10,"Nov"=>11,"Dec"=>12];
                foreach ($arr as $key => $value) {
                    if($request->month == $key)
                    {
                        $first_date = date($year.'-'.$value.'-1');
                        $month = $value;
                    }
                }
                $last_date =  date("t", strtotime($first_date));
                for ($d=1; $d<=$last_date; $d++) {
                    $date = date($year.'-'.$month.'-'.$d);
                    $dprUploads = \DB::table('dpr_imports')->join('dpr_configs', function($join) use ($request) {
                        $join->on('dpr_configs.id', '=', 'dpr_imports.dpr_config_id');
                    })
                    ->whereDate('dpr_imports.data_date',$date)->groupBy('dpr_imports.random_no');
                    if(!empty(auth()->user()->dpr_config_ids))
                    {
                        $dpr_config_ids = explode(',',auth()->user()->dpr_config_ids);
                        $dprUploads = $dprUploads->whereIn('dpr_imports.dpr_config_id',$dpr_config_ids);
                    }
                    if(!empty($request->project_id))
                    {
                        $dprUploads->where('dpr_configs.project_id', $request->project_id);
                    }
                    if(!empty($request->work_pack_id))
                    {
                        $dprUploads->where('dpr_configs.work_pack_id', $request->work_pack_id);
                    }
                    if(!empty($request->vendor_id))
                    {
                        $dprUploads->where('dpr_configs.vendor_id', $request->vendor_id);
                    }
                    $dprUploads = $dprUploads->get();
                    $manpower = 0;
                    if($dprUploads->count()>0)
                    {
                        foreach ($dprUploads as $skey => $dprUpload) 
                        {
                            if($dprUpload->sheet_json_data!=''){
                                $sheetArray = json_decode($dprUpload->sheet_json_data, true);
                                if(is_array($sheetArray))
                                {
                                    foreach ($sheetArray as $key => $value) 
                                    {
                                        if($key =='manpower'){
                                            $manpower += $value;

                                        }
                                        
                                    }
                                }
                            }
                        }
                    }
                    
                    $data['date'][] = date('Y-m-d',strtotime($date));
                    $data['data'][] = $manpower;
                }
            }
            else{
                for ($m=1; $m<=12; $m++) {
                    $dprUploads = \DB::table('dpr_imports')->join('dpr_configs', function($join) use ($request) {
                        $join->on('dpr_configs.id', '=', 'dpr_imports.dpr_config_id');
                    })
                    ->whereMonth('dpr_imports.data_date',$m)
                    ->whereYear('dpr_imports.data_date',$year)
                    ->groupBy('dpr_imports.random_no');
                    if(!empty(auth()->user()->dpr_config_ids))
                    {
                        $dpr_config_ids = explode(',',auth()->user()->dpr_config_ids);
                        $dprUploads = $dprUploads->whereIn('dpr_imports.dpr_config_id',$dpr_config_ids);
                    }
                    if(!empty($request->project_id))
                    {
                        $dprUploads = $dprUploads->where('dpr_configs.project_id', $request->project_id);
                    }
                    if(!empty($request->work_pack_id))
                    {
                        $dprUploads = $dprUploads->where('dpr_configs.work_pack_id', $request->work_pack_id);
                    }
                    if(!empty($request->vendor_id))
                    {
                        $dprUploads = $dprUploads->where('dpr_configs.vendor_id', $request->vendor_id);
                    }
                    $dprUploads = $dprUploads->get();
                    $manpower = 0;
                    if($dprUploads->count()>0)
                    {
                        foreach ($dprUploads as $skey => $dprUpload) 
                        {
                            if($dprUpload->sheet_json_data!=''){
                                $sheetArray = json_decode($dprUpload->sheet_json_data, true);
                                if(is_array($sheetArray))
                                {
                                    foreach ($sheetArray as $key => $value) 
                                    {
                                        if($key =='manpower'){
                                            $manpower += $value;

                                        }
                                    }
                                }
                            }
                        }
                    }
                    $date = date('Y-'.$m.'-d');
                    $data['months'][] = date('M',strtotime($date));
                    $data['data'][] = $manpower;
                }
            }
            return response(prepareResult(false, $data, trans('translate.manpower_graph')), config('httpcodes.success'));
        } catch(Exception $exception) {
            return response(prepareResult(false, $exception->getMessage(),trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));   
        } 
    }

    /**
     * Dpr Import Man Power report.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    //Dpr Import Man Power report
    public function dprUploadsManPowerGraph(Request $request)
    {
        try
        {
            $dprUploads = DprImport::join('dpr_configs', function($join) use ($request) {
                        $join->on('dpr_configs.id', '=', 'dpr_imports.dpr_config_id');
            })->where('dpr_imports.data_date',$request->data_date)->groupBy('dpr_imports.random_no');
            if(!empty(auth()->user()->dpr_config_ids))
            {
                $dpr_config_ids = explode(',',auth()->user()->dpr_config_ids);
                $dprUploads = $dprUploads->whereIn('dpr_imports.dpr_config_id',$dpr_config_ids);
            }
            $dprUploads = $dprUploads->get();
            $data = [];
            $pr_ids = [];
            foreach ($dprUploads as $key => $dprUpload) {
                $projectData = [];
                $dprConfig = DprConfig::where('id',$dprUpload->dpr_config_id)->first();
                if($dprConfig)
                {
                    if(in_array($dprConfig->project_id, $pr_ids))
                    {

                    }
                    else{
                        $pr_ids[] = $dprConfig->project_id;
                        $projectConfigs = DprConfig::where('project_id',$dprConfig->project_id)->get();
                        $projectData['project'] = @$dprConfig->project->name;
                        foreach ($projectConfigs as $key => $value) {
                            $profile_name = $value->profile_name;
                            $sheet_json_data = DprImport::where('dpr_config_id',$value->id)->select('sheet_json_data')->first();
                            $manpower = 0;
                            if(!empty($sheet_json_data)){
                                foreach (json_decode($sheet_json_data->sheet_json_data, true) as $key => $value) {
                                    if($key =='manpower'){
                                        $manpower += $value;

                                    }
                                }
                                
                            }
                            $projectData['profiles'][] = ['profile_name'=>$profile_name,'manpower'=>$manpower,'work_package'=>@$value->WorkPackage->name];
                        }
                        $data[] = $projectData;
                    }   
                }             
            }            
            return response(prepareResult(false, $data, trans('translate.dpr_uploads_manpower_graph')), config('httpcodes.success'));
        } catch(Exception $exception) {
            return response(prepareResult(false, $exception->getMessage(),trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));   
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

            return response(prepareResult(false, $query, trans('translate.fetched_records')), config('httpcodes.success'));
        } catch (\Throwable $e) {
            \Log::error($e);
            return response(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }
    }

}
