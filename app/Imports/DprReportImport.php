<?php

namespace App\Imports;
use App\Models\DprImport;
use App\Models\DprMap;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithMappedCells;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class DprReportImport implements WithMappedCells,ToModel,WithCalculatedFormulas 
{
    
    public $sheetName;

    public function __construct($sheetName)
    {
        $this->sheetName = $sheetName;
    }

    public function mapping(): array

    {
        $userDprMap = DprMap::where('user_id','1')->orderby('id','ASC')->where('sheet_name',$this->sheetName)->get();
           
        $data_date = (@$userDprMap[0]['row_position'] == @$userDprMap[0]['row_new_position']) ? @$userDprMap[0]['cell_value'].@$userDprMap[0]['row_position'] : @$userDprMap[0]['cell_value'].@$userDprMap[0]['row_new_position'];

        $total_scope = (@$userDprMap[1]['row_position'] == @$userDprMap[1]['row_new_position']) ? @$userDprMap[1]['cell_value'].@$userDprMap[1]['row_position'] : @$userDprMap[1]['cell_value'].@$userDprMap[1]['row_new_position'];

        $actual_till_date = (@$userDprMap[2]['row_position'] == @$userDprMap[2]['row_new_position']) ? @$userDprMap[2]['cell_value'].@$userDprMap[2]['row_position'] : @$userDprMap[2]['cell_value'].@$userDprMap[2]['row_new_position'];

        $plan_ftm = (@$userDprMap[3]['row_position'] == @$userDprMap[3]['row_new_position']) ? @$userDprMap[3]['cell_value'].@$userDprMap[3]['row_position'] : @$userDprMap[3]['cell_value'].@$userDprMap[3]['row_new_position'];

        $actual_ftm = (@$userDprMap[4]['row_position'] == @$userDprMap[4]['row_new_position']) ? @$userDprMap[4]['cell_value'].@$userDprMap[4]['row_position'] : @$userDprMap[4]['cell_value'].@$userDprMap[4]['row_new_position'];

        $today = (@$userDprMap[5]['row_position'] == @$userDprMap[5]['row_new_position']) ? @$userDprMap[5]['cell_value'].@$userDprMap[5]['row_position'] : @$userDprMap[5]['cell_value'].@$userDprMap[5]['row_new_position'];

        $dwg_avail = (@$userDprMap[6]['row_position'] == @$userDprMap[6]['row_new_position']) ? @$userDprMap[6]['cell_value'].@$userDprMap[6]['row_position'] : @$userDprMap[6]['cell_value'].@$userDprMap[6]['row_new_position'];

        $manpower = (@$userDprMap[7]['row_position'] == @$userDprMap[7]['row_new_position']) ? @$userDprMap[7]['cell_value'].@$userDprMap[7]['row_position'] : @$userDprMap[7]['cell_value'].@$userDprMap[7]['row_new_position'];

        $change_reason_for_plan_ftm = (@$userDprMap[8]['row_position'] == @$userDprMap[8]['row_new_position']) ? @$userDprMap[8]['cell_value'].@$userDprMap[8]['row_position'] : @$userDprMap[8]['cell_value'].@$userDprMap[8]['row_new_position'];
        
        return [
            'data_date'  => $data_date,
            'total_scope' => $total_scope,
            'actual_till_date' => $actual_till_date,
            'plan_ftm' => $plan_ftm,
            'actual_ftm' => $actual_ftm,
            'today' => $today,
            'dwg_avail' => $dwg_avail,
            //'manpower' => $manpower,
            //'change_reason_for_plan_ftm' => $change_reason_for_plan_ftm,
        ];
    }
    
    public function model(array $row)
    {
        print_r($row);
       die;
        return new DprImport([
            'user_id' => '1',
            'dpr_config_id' => '1',
            'data_date' => date('Y-m-d',strtotime($row['data_date'])),
            'actual_till_date' =>  $row['actual_till_date'],
            'plan_ftm' => $row['plan_ftm'],
            'actual_ftm' => $row['actual_ftm'],
            'today' => $row['today'],
            'dwg_avail' => $row['dwg_avail'],
            'manpower' => @$row['manpower'],
            'change_reason_for_plan_ftm' => @$row['change_reason_for_plan_ftm'],
        ]);
    }
}
