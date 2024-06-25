<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use App\Models\SendCampaignMail;
use App\Models\DprImport;
use Auth;
class ReportExport implements FromCollection,WithHeadings
{
	use Exportable;
	
	protected $date;
	public function __construct($date)
	{
	   $this->date = $date;
    	return $this;
	}

	public function headings(): array {
	    return [
	      'PROJECT',
	      'WORK ITEM',
	      'DATA DATE',
	      'SCOPE',
	      'ACTUAL FTM',
	      'ACTUAL TILL DATE',
	      'PLAN FTM',
	      'CHANGE REASON',
	      'DWG AVAIL',
	      'MANPOWER',
	      'TODAY',
	    ];
	 }


    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {

        $reporData = DprImport::whereDate('data_date',$this->date)->get();
        return $array = $reporData->map(function ($b, $key) {
			return [
		      'PROJECT'   					=> @$b->dprConfig->Project->name,
		      'WORK ITEM'   			=> $b->work_item,
		      'DATA DATE'   =>   date('d M Y', strtotime($b->data_date)),
		      'SCOPE'   			=> $b->total_scope,
		      'ACTUAL FTM'   			=> $b->actual_ftm,
		      'ACTUAL TILL DATE'   => $b->actual_till_date,
		      'PLAN FTM'   =>    $b->plan_ftm,
		      'CHANGE REASON'   =>   $b->change_reason_for_plan_ftm,
		      'DWG AVAIL'   => $b->dwg_avail,
		      'MANPOWER'   =>    $b->manpower,
		      'MANPOWER'   =>    $b->today,
			];
		});
    }

}
