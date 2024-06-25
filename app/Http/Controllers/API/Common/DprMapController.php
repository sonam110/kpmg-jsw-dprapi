<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Auth;
use Exception;
use App\Models\DprMap;
use App\Models\DprConfig;
class DprMapController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    //create new dpr map
    public function updateDprMap(Request $request) {
        try {
           
            $user = getUser();
            $validator = Validator::make($request->all(),[ 
                'dpr_config_id' => 'required|exists:dpr_configs,id',  
                'dpr_map.*.name' => 'required',   
                'sheet_name' => 'required',   
            ],
            [   
            '*.name' => 'Name field is required',              
            ]);
            if ($validator->fails()) {
                return response(prepareResult(false, $validator->messages()->first(),'validation failed'), config('httpcodes.bad_request'));
            }
            $sheet_name = str_replace('_',' ',$request->sheet_name);
            $checkAlready = DprMap::where('dpr_config_id',$request->dpr_config_id)->count();
            if($checkAlready >0 ){
                $deleteOld = DprMap::where('dpr_config_id',$request->dpr_config_id)->delete();
            }
            if(is_array($request->dpr_map) && count($request->dpr_map) >0 ){
                /*-----Update sheet name-----------------------*/
                $dprConfig = DprConfig::find($request->dpr_config_id);
                $dprConfig->sheet_name = $request->sheet_name;
                $dprConfig->save();
                $maxCount = 0; // Initialize with a negative value
                $maxMapValue = [];
                foreach ($request->dpr_map as $mapItem) {
                    $count = count(@$mapItem['map_value']);
                    if ($count > $maxCount) {
                        $maxCount = $count;
                        $maxMapValue = @$mapItem['map_value'];
                    }
                }
                $maxCount =  count($maxMapValue);

                foreach($request->dpr_map as $key => $map) {
                    if(!empty(@$map['name']))
                    {

                        if(is_array(@$map['map_value']) && count(@$map['map_value']) >0 ){
                            $map_value = @$map['map_value'];
                            for ($i = 0;$i < $maxCount ;$i++) {
                                if($i == '0') {
                                    $dprMapPrent = new  DprMap;
                                    $dprMapPrent->user_id = $user->id;
                                    $dprMapPrent->is_parent = NULL;
                                    $dprMapPrent->dpr_config_id = $request->dpr_config_id;
                                    $dprMapPrent->sheet_name = $sheet_name;
                                    $dprMapPrent->name = $map['name'];
                                    $dprMapPrent->slug = \Str::slug($map['name']);
                                    $dprMapPrent->cell_value = $map_value[$i]['cell_value'];
                                    $dprMapPrent->row_position = $map_value[$i]['row_position'];
                                    $dprMapPrent->row_new_position = $map_value[$i]['row_new_position'];
                                    $dprMapPrent->item_desc = $map_value[$i]['item_desc'];
                                    $dprMapPrent->position = $i;
                                    $dprMapPrent->orderno = (!empty($map['orderno'])) ? $map['orderno'] :'1';
                                    $dprMapPrent->save();

                                } 
                                if($i !='0') {
                                    $dprMap = new  DprMap;
                                    $dprMap->user_id = $user->id;
                                    $dprMap->is_parent = $dprMapPrent->id;
                                    $dprMap->dpr_config_id = $request->dpr_config_id;
                                    $dprMap->sheet_name = $sheet_name;
                                    $dprMap->name = $map['name'];
                                    $dprMap->slug = \Str::slug($map['name']);
                                    $dprMap->cell_value = (!empty($map_value[$i]['cell_value'])) ? $map_value[$i]['cell_value'] : @$map_value[0] ['cell_value'];
                                    $dprMap->row_position = (!empty($map_value[$i]['row_position'])) ? $map_value[$i]['row_position'] : @$map_value[0] ['row_position'];
                                    $dprMap->row_new_position = (!empty($map_value[$i]['row_new_position'])) ? $map_value[$i]['row_new_position'] : @$map_value[0] ['row_new_position'];
                                    $dprMap->item_desc = (!empty($map_value[$i]['item_desc'])) ? $map_value[$i]['item_desc'] : @$map_value[0] ['item_desc'];
                                    $dprMap->position = $i;
                                    $dprMap->save();
                                }


                            }

                        }
                    }
                }
                
                $allDprMap = DprMap::where('dpr_config_id',$request->dpr_config_id)->orderBy('id','ASC')->get();
                return response(prepareResult(false,$allDprMap, trans('translate.dpr_map_updated')),config('httpcodes.created'));

            } else{
                 return response(prepareResult(false, [],trans('translate.invalid_request')), config('httpcodes.bad_request'));
            }
           
        } catch (Exception $e) {
            \Log::error($e);
            return response(prepareResult(true, $e->getMessage(),trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }
    }

    /**
     * Dpr Map view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    //getting details of dpr map
    public function DprMapView(Request $request) {
        try {
            $validator = Validator::make($request->all(),[ 
               'dpr_config_id' => 'required|exists:dpr_configs,id',  
            ]);
            if ($validator->fails()) {
                return response(prepareResult(true, $validator->messages(),$validator->messages()->first()), config('httpcodes.bad_request'));
            }
            $sheet_name = \DB::table('dpr_configs')->where('id',$request->dpr_config_id)->first();

            $sheet_name = str_replace('_',' ',@$sheet_name->sheet_name);
            $DprMap = \DB::table('dpr_maps')->where('dpr_config_id',$request->dpr_config_id)->whereNotNull('orderno')->orderby('id','ASC')->get();
           
            $output = [];
            $data = [];
            $data['sheet_name'] = $sheet_name;
            foreach($DprMap as $key => $map){
                $map_value = DprMap::select('cell_value','row_position','row_new_position','item_desc')->where('dpr_config_id',$request->dpr_config_id)->where('name',$map->name)->with('itemDesc:id,title')->get();
                $data[$map->name.'-OrderNo#'.$map->orderno] = $map_value;
                    
            }
            return response(prepareResult(false,$data, trans('translate.dpr_map_view')),config('httpcodes.success'));
            
        } catch (Exception $e) {
            \Log::error($e);
            return response(prepareResult(true, $e->getMessage(),trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }
    }
}
