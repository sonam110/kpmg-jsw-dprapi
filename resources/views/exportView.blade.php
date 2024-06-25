<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>KPMG DPR Report</title>
    <style type="text/css">
      .clearfix:after {
        content: "";
        display: table;
        clear: both;
      }

      a {
        color: #0087C3;
        text-decoration: none;
      }

      body {
        margin: 0 auto;
        color: #555555;
        background: #FFFFFF;
        font-family: opensanscondensed;
        font-size: 14px;
      }

      table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        overflow: hidden;
        border: #00338d 3px solid;
      }

      table td,
      table th {
        border-top: 1px solid #ecf0f1;
        padding: 10px;
      }

      table thead tr.orange {
        color: #FFFFFF !important;
        font-size: 16px !important;
        background: #00338d !important;
      }

      .orange {
        color: #FFFFFF !important;
        font-weight: bold;
      }

      .gray {
        color: #000 !important;
        font-size: 14px !important;
        background: #e7e9ee !important;
      }

      table td {
        border-left: 1px solid #ecf0f1;
        border-right: 1px solid #ecf0f1;
      }

      table tr:nth-of-type(even) td,
      table tr:nth-of-type(odd) td {
        background-color: #e7e9ee;
      }

      #thanks {
        font-size: 20px !important;
        text-align: center;
      }

      .boxhead a {
          color: #0087C3;
          text-decoration: none;
      }
    </style>
  </head>
  <body>
     @php
        $appSetting = App\Models\AppSetting::first();
     @endphp
    <table width="100%" style="padding: 10px 20px;">
       <thead>
          <tr class="orange">
            <th class="orange" colspan="6">
             <img src="{{ $appSetting->app_logo }}" width="150">
            </th>
           
            <th class="orange" colspan="4">
              <strong>{{ $appSetting->app_name }}</strong>
            </th>
          </tr>
         </thead>


       @foreach($dprList as $key => $dpr)
      @php
        $alldpr = App\Models\DprImport::where('work_item',$dpr->work_item)
          ->where('data_date', $dpr->data_date)
          ->groupBy('dpr_config_id')
          ->get();
      @endphp
      
        <thead>
          <tr class="">
            <th class="" colspan="6">
              <strong>Work Item: {{ !empty($dpr->work_item) ? $dpr->work_item : "-" }}</strong>
            </th>
           
            <th class="" colspan="4">
              <strong>Date: {{ date('d M Y', strtotime($dpr->data_date)) }}</strong>
            </th>
          </tr>
          <tr>
            <th class="gray" width="15%">
              <div>PROJECT</div>
      
            <th class="gray">
              <div>SCOPE</div>
            </th>
            <th class="gray">
              <div>ACTUAL FTM</div>
            </th>
            <th class="gray">
              <div>ACTUAL TILL DATE</div>
            </th>
            <th class="gray">
              <div>PLAN FTM</div>
            </th>
            <th class="gray">
              <div>DWG AVAIL</div>
            </th>
            <th class="gray">
              <div>MANPOWER</div>
            </th>
            <th class="gray">
              <div>TODAY</div>
            </th>
          </tr>
        </thead>
        <tbody> 
          @foreach($alldpr as $nkey => $dprs) 
          @php 
            if(env('APP_ENV', 'local')==='production')
            {
                $url = secure_url('user/import/'.@$dprs->dprManage->original_import_file.''); 
            }
            else
            {
                $url = url('user/import/'.@$dprs->dprManage->original_import_file.''); 
            }
            
            $allConfigwiseList = App\Models\DprImport::where('work_item',$dprs->work_item)
            ->where('data_date', $dprs->data_date)
            ->where('dpr_config_id',$dprs->dpr_config_id)
            ->get();
            $totalScope = $allConfigwiseList->sum('total_scope');
            $actual_ftm = $allConfigwiseList->sum('actual_ftm');
            $actual_till_date = $allConfigwiseList->sum('actual_till_date');
            $plan_ftm = $allConfigwiseList->sum('plan_ftm');
            $dwg_avail = $allConfigwiseList->sum('dwg_avail');
            $manpower = $allConfigwiseList->sum('manpower');
            $today = $allConfigwiseList->sum('today');
          @endphp 

          <tr>
            
            <td class="desc">
                <strong>
                  {{ @$dprs->dprConfig->Project->name }}
                  @if(@$dprs->dprConfig->Project->status=="2")
                  -{{ @$dprs->work_item }} (inactive)
                  @endif
                </strong>
            </td>
            <td class="desc">{{ $totalScope }} </td>
            <td class="desc">{{ $actual_ftm }} </td>
            <td class="desc">{{ $actual_till_date }} </td>
            <td class="desc">{{ $plan_ftm }} </td>
            <td class="desc">{{ $dwg_avail }} </td>
            <td class="desc">{{ $manpower }} </td>
            <td class="desc">{{ $today }} </td>
          </tr> 
          @foreach($allConfigwiseList as $nkey => $list) 
          
          <tr>
            
            <td class="desc boxhead">
              @if(@$dprs->dprManage->original_import_file!='')
              <a href="{{ $url }}" download>
                  {{ @$list->dprConfig->sheet_name }}-{{ @$list->work_item }}
                
              </a>
              @else
                {{ @$list->dprConfig->profile_name }}-{{ @$list->work_item }}
              @endif
            </td>
            <td class="desc">{{ $list->total_scope }} </td>
            <td class="desc">{{ $list->actual_ftm }} </td>
            <td class="desc">{{ $list->actual_till_date }} </td>
            <td class="desc">{{ $list->plan_ftm }} </td>
            <td class="desc">{{ $list->dwg_avail }} </td>
            <td class="desc">{{ $list->manpower }} </td>
            <td class="desc">{{ $list->today }} </td>
          </tr> 
           @endforeach 

          @endforeach 
        </tbody>
     
      <br />
      @endforeach 
       </table> 
    
      <div>
      </div>
  </body>
</html>