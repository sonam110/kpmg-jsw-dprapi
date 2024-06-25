<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Auth;
use Exception;
use App\Models\DprMap;
use App\Models\User;
use App\Models\Notification;
use App\Models\DprImport;
use App\Models\DprManage;
use App\Models\DprLog;
use App\Models\DprConfig;
use App\Models\AppSetting;
use Excel;
use PDF;
use Storage;
use App\Imports\DprReportImport;
use App\Imports\DprSelectSheet;
use Illuminate\Support\Facades\File;
use App\Exports\ReportExport;
use DB;
use App\Exports\CustomExport;

class PdfViewController extends Controller
{

    public function loadPdf(Request $request) {
        try {
            
            $date = '2023-09-03';
            $item_desc = 'Loading/Un loading of Borrowed soil at site';
            
            $query = DprImport::where('data_date',$date)->groupBy('item_desc')->with('dprManage');
            if(!empty($item_desc))
            {
               $query = $query->where('item_desc',$item_desc);
            }
            $query = $query->get();
            $dprList =[];
            $request->type ="pdf";
           //dd($query) ;
            $itemData = [];
            foreach ($query as $value) {
                // Decode the JSON data from the column into a PHP array
                $alldpr = DprImport::join('dpr_configs','dpr_imports.dpr_config_id','dpr_configs.id')
                ->where('dpr_imports.data_date',$date)
                ->groupBy('dpr_configs.project_id','dpr_configs.work_pack_id')
                ->where('dpr_imports.item_desc','Loading/Un loading of Borrowed soil at site')
                ->get();
            
                foreach ($alldpr as $key => $dpr) {
                    $allData =  DprConfig::join('dpr_imports','dpr_configs.id','dpr_imports.dpr_config_id')
                    ->where('dpr_imports.data_date',$date)
                    ->where('dpr_imports.item_desc',$value->item_desc)
                    ->where('dpr_configs.project_id',$dpr->project_id)
                    ->where('dpr_configs.work_pack_id',$dpr->work_pack_id)
                    ->get();

                    $mergedArray = [];
                    $extaraData = [];
                    foreach ($allData as $key => $data) {
                        $jsonData = json_decode($data->sheet_json_data, true);
                        $extaraData['vendor_name'] = @$data->vendor->name;
                        $extaraData['project_name'] = @$data->Project->name;
                        $extaraData['project_status'] = @$data->Project->status;
                        $extaraData['file_name'] = @$data->dprManage->original_import_file;

                        if(env('APP_ENV', 'local')==='production')
                        {
                            $extaraData['original_csv'] =secure_url('user/import/'.@$data->dprManage->original_import_file.'');
                        }
                        else
                        {
                            $extaraData['original_csv'] =url('user/import/'.@$data->dprManage->original_import_file.'');
                        }

                        $mergeExtraCol = array_merge($jsonData, $extaraData);
                        $mergedArray[] = $mergeExtraCol;
                           
                          
                    }

                    if(env('APP_ENV', 'local')==='production')
                    {
                        $original_csv =secure_url('user/import/'.@$dpr->dprManage->original_import_file.'');
                    }
                    else
                    {
                        $original_csv =url('user/import/'.@$dpr->dprManage->original_import_file.'');
                    }

                    $itemData[] =[
                        'project_name' => @$dpr->dprConfig->Project->name,
                        'project_status' => @$dpr->dprConfig->Project->status,
                        'sheet_name' => @$dpr->dprConfig->sheet_name,
                        'profile_name' => @$dpr->dprConfig->profile_name,
                        'work_package' => $dpr->dprConfig->WorkPackage->name,
                        'unit_of_measure' => $dpr->dprConfig->WorkPackage->unit_of_measure,
                        'vendor_name' => @$dpr->dprConfig->vendor->name,
                        'work_item' => $dpr->item_desc,
                        'data' => $mergedArray,
                        'file_name' => @$dpr->dprManage->original_import_file,
                        'original_csv' => $original_csv,
                        
                    ];
                }

                $dprList[] =[
                    'date' => $date,
                    'work_item' => $value->item_desc,
                    'unit_of_measure' => $value->dprConfig->WorkPackage->unit_of_measure,
                    'item_data' => $itemData,

                ];
                $itemData = [];

            }

           //dd($dprList);
          

            if(count($dprList)>0){
                if($request->type=="html"){
                    $FileName = time().'.html';
                    $FilePath = 'pdf/' . $FileName;
                    $html = view('pdfview',compact('dprList', 'date'));
                    $html = $html->render();
                   \Storage::disk('excel_uploads')->put($FilePath, $html, 'public');
                   if(env('APP_ENV', 'local')==='production')
                   {
                        $callApi = secure_url('api/file-access/'.$FilePath);
                   }
                   else
                   {
                        $callApi = url('api/file-access/'.$FilePath);
                   }
               } else{


                    $FileName = time().'.pdf';
                    $date = '2023-09-03';

                    return view('pdfview', ['dprList' => $dprList,'date'=>$date]);
                    $pdf = PDF::loadView('pdfview',compact('dprList', 'date'));

                    $FilePath = 'pdf/' . $FileName;
                    \Storage::disk('excel_uploads')->put($FilePath, $pdf->output(), 'public');
                    if(env('APP_ENV', 'local')==='production')
                    {
                        $callApi = secure_url('api/file-access/'.$FilePath);
                    }
                    else
                    {
                        $callApi = url('api/file-access/'.$FilePath);
                    }
                }

                return response(prepareResult(false,$callApi, trans('translate.Download')),config('httpcodes.success'));

            } else{
                return response(prepareResult(true,[],trans('translate.record_not_found')), config('httpcodes.bad_request'));
            }



        } catch (Exception $e) {
            \Log::error($e);
            return response(prepareResult(true, $e->getMessage(),trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }
    }
    public function loadExcel(Request $request) {
        try {
           /* $validator = \Validator::make($request->all(),[ 
                'date'     => 'required',
            ]);
            if ($validator->fails()) {
                return response(prepareResult(true, $validator->messages(),$validator->messages()->first()), config('httpcodes.bad_request'));
            }*/

             $date = '2023-09-03';
            $query = DprImport::where('data_date',$date)->groupBy('item_desc')->with('dprManage');
            if(!empty($request->item_desc))
            {
                $query->where('item_desc',$request->item_desc);
            }
            $query = $query->get();
            $dprList =[];
            foreach ($query as $value) {
                // Decode the JSON data from the column into a PHP array
                $alldpr = DprImport::join('dpr_configs','dpr_imports.dpr_config_id','dpr_configs.id')
                ->where('dpr_imports.data_date',$date)
                ->groupBy('dpr_configs.project_id','dpr_configs.work_pack_id')
                ->where('dpr_imports.item_desc',$value->item_desc)
                ->get();
            
                foreach ($alldpr as $key => $dpr) {
                    $allData =  DprConfig::join('dpr_imports','dpr_configs.id','dpr_imports.dpr_config_id')
                    ->where('dpr_imports.data_date',$date)
                    ->where('dpr_imports.item_desc',$value->item_desc)
                    ->where('dpr_configs.project_id',$dpr->project_id)
                    ->where('dpr_configs.work_pack_id',$dpr->work_pack_id)
                    ->get();

                    $mergedArray = [];
                    $extaraData = [];
                    foreach ($allData as $key => $data) {
                        $jsonData = json_decode($data->sheet_json_data, true);
                        $extaraData['vendor_name'] = @$data->vendor->name;
                        $extaraData['project_name'] = @$data->Project->name;
                        $extaraData['project_status'] = @$data->Project->status;
                        $extaraData['file_name'] = @$data->dprManage->original_import_file;

                        if(env('APP_ENV', 'local')==='production')
                        {
                            $extaraData['original_csv'] =secure_url('user/import/'.@$data->dprManage->original_import_file.'');
                        }
                        else
                        {
                            $extaraData['original_csv'] =url('user/import/'.@$data->dprManage->original_import_file.'');
                        }

                        

                        $mergeExtraCol = array_merge($jsonData, $extaraData);
                        $mergedArray[] = $mergeExtraCol;
                           
                          
                    }

                    if(env('APP_ENV', 'local')==='production')
                    {
                        $original_csv =secure_url('user/import/'.@$dpr->dprManage->original_import_file.'');
                    }
                    else
                    {
                        $original_csv =url('user/import/'.@$dpr->dprManage->original_import_file.'');
                    }

                    $itemData[] =[
                        'project_name' => @$dpr->dprConfig->Project->name,
                        'project_status' => @$dpr->dprConfig->Project->status,
                        'sheet_name' => @$dpr->dprConfig->sheet_name,
                        'profile_name' => @$dpr->dprConfig->profile_name,
                        'work_package' => $dpr->dprConfig->WorkPackage->name,
                        'vendor_name' => @$dpr->dprConfig->vendor->name,
                        'work_item' => $dpr->item_desc,
                        'data' => $mergedArray,
                        'file_name' => @$dpr->dprManage->original_import_file,
                        'original_csv' => $original_csv,
                        
                    ];
                }

                $dprList[] =[
                    'date' => $date,
                    'work_item' => $value->item_desc,
                    'unit_of_measure' => $value->dprConfig->WorkPackage->unit_of_measure,
                    'item_data' => $itemData,

                ];
                $itemData = [];

            }
            //dd($dprList);
            $appSetting = AppSetting::first();
            if(count($dprList)>0){
                $FileName = time().'.xlsx';
                $FilePath = 'excel/'.$FileName;
                $data =  [
                    'date' => '2023-09-03',
                    'dprList' => $dprList,
                    'appSetting' => $appSetting,
                ];

                $html = view('excelView',$data)->render();
                

                Excel::store(new CustomExport($data),$FilePath, 'excel_uploads');
                if(env('APP_ENV', 'local')==='production')
                {
                    $callApi = secure_url('api/file-access/'.$FilePath);
                }
                else
                {
                    $callApi = url('api/file-access/'.$FilePath);
                }
                return $callApi;
               
                return response(prepareResult(false,$callApi, trans('translate.download_excel')),config('httpcodes.success'));

            } else{
                return response(prepareResult(true,[],trans('translate.record_not_found')), config('httpcodes.bad_request'));
            }

        } catch (Exception $e) {
            \Log::error($e);
            return response(prepareResult(true, $e->getMessage(),trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }

    }
}
