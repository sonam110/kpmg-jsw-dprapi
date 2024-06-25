<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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

      
      .orange {
        color: #FFFFFF !important;
        font-weight: bold;
      }

      .gray {
        color: #000 !important;
        font-size: 14px !important;
        background: #e7e9ee !important;
      }

      
      td{
        text-align: center;

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
    <?php
        $logo = explode('access/uploads/',$appSetting->app_logo);
    ?>

    <table width="100%" style="padding: 10px 20px;">
       <thead>
          <tr class="" height="30">
            <th class="" style="background:#00338d; vertical-align:top" >
                <img src="uploads/Picture1.png" width="150px" height="30px">
            </th>
           
            <th class="" colspan="9" style="background:#00338d; text-align:center">
              <strong>{{ @$appSetting->app_name }}</strong>
            </th>
          </tr>
         </thead>


       @foreach($dprList as $key => $dpr)
      <?php
        
        $totalValue =0;
        $array1 = array_keys(@$dpr['item_data'][0]['data'][0]);

        $array2 = ['vendor_name', 'project_name', 'project_status', 'file_name','original_csv'];
        $array_keys = array_diff($array1, $array2);

        $dataCount = count(@$dpr['item_data']);
        $arraySize = sizeof($array_keys);
        $colValue = $arraySize-2;
       


      ?>
       <thead>
          <tr class="orange">
            <th class="orange text-left" colspan="{{ $colValue }}">
             <strong>Work Item: {{ !empty($dpr['work_item']) ? $dpr['work_item']: "-" }} ({{ $dpr['unit_of_measure'] }})</strong>
            </th>
            <th class="orange text-right" colspan="">
              
            </th>
            <th class="orange text-right" colspan="2">
            <strong> Date &nbsp;&nbsp;&nbsp; {{ date('d M Y', strtotime($date)) }}</strong>
            </th>
          </tr>
          <tr>
            <th class="gray" width="15%">
              <div>Project</div>
      
           </th>

           @foreach($array_keys as $nkey => $kval) 
            
            <th class="gray" width="15%">
              <div>{{ $kval }}</div>
      
           </th>
           @endforeach
          </tr>
         
        </thead>
       <tbody> 
  
        @foreach($dpr['item_data'] as $nkey => $item) 
        <tr>
            <td class="desc">
                <strong>
                  {{ $item['project_name'] }}
                 
                </strong>
            </td>
             <?php

            $totalarray =[];
             $result = [];
              foreach (@$item['data'] as $array) {
                  foreach ($array as $key => $value) {
                      if (!isset($result[$key])) {
                          $result[$key] = $value;
                      } else {
                        if(is_numeric($result[$key])){

                          $result[$key] += $value;
                        }
                      }
                  }
              }


             ?>
             @foreach($array_keys as $nkey => $kval) 
             <?php

                $dval =  '-';
                if(is_numeric(@$result[$kval])){
                  $dval = number_format(@$result[$kval]);
                  
                
                }


            ?>
             
            <td class="desc"><strong>{{ $dval }} </strong></td>
            @endforeach
           
          </tr> 

          @foreach(@$item['data'] as $vkey => $data) 
         
         <tr>
          <td class="desc">
                
              <a href="{{ @$data['original_csv'] }}" download>
                  {{ @$data['vendor_name'] }}
                  @if($data['project_status']=='2')
                  (inactive)
                  @endif
                
              </a>
             
            </td>
           @foreach($array_keys as $nkey => $kval) 
            <?php
            
              $dataValue =  @$data[$kval];
              if(is_numeric(@$data[$kval])){
                $dataValue = number_format(@$data[$kval]);
              }
            ?>
            <td class="desc">{{ $dataValue }}</td>
             @endforeach
          </tr> 
          @endforeach 

          
          @endforeach 
            <tr>
          <td class="desc"><strong>Total</strong></td>
           @foreach($array_keys as $nkey1 => $keyvalue) 
           
            <?php
               $sresult = [];
              foreach (@$dpr['item_data'] as $arrays) {
                foreach (@$arrays['data'] as $arrays) {
                    foreach ($arrays as $keys => $values) {
                        if (!isset($sresult[$keys])) {
                            $sresult[$keys] = $values;
                        } else {
                          if(is_numeric($sresult[$keys])){

                            $sresult[$keys] += $values;
                          }
                        }
                    }
                  }
              }

              $totalValue =  '-';
                if(is_numeric(@$sresult[$keyvalue])){
                  $totalValue = number_format(@$sresult[$keyvalue]);
                  
                
                }




               ?>
          
          <td class="desc"><strong>{{ $totalValue }}</strong></td>
           @endforeach 
          <tr>
          <tr>
          </tr>
         
        </tbody>
        
      <br />
      @endforeach 
       </table> 
    
      <div>
      </div>
  </body>
</html>