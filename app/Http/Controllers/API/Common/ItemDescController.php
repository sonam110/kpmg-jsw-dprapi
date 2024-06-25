<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\User;
use App\Models\ItemDescriptionMaster;
use Validator;
use Auth;
use Exception;
use DB;
class ItemDescController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:item-desc-add', ['only' => ['store']]);
        $this->middleware('permission:item-desc-edit', ['only' => ['update']]);
        $this->middleware('permission:item-desc-read', ['only' => ['show']]);
        $this->middleware('permission:item-desc-delete', ['only' => ['destroy']]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    //creating new Item Description
     public function store(Request $request)
    {
        $validation = \Validator::make($request->all(), [
            'title'      => 'required|regex:/^[a-zA-Z0-9-_ \/]+$/',
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), $validation->messages()->first()), config('httpcodes.bad_request'));
        }

        DB::beginTransaction();
        try {
            $checkAlready = ItemDescriptionMaster::where('title',$request->title)->first();
            if(!empty($checkAlready)){
                return response(prepareResult(true,[],trans('translate.item_desc_already_exist')), config('httpcodes.bad_request')); 
            }

            $itemDesc = new ItemDescriptionMaster;
            $itemDesc->title = $request->title;
            $itemDesc->created_by = auth()->id();
            $itemDesc->save();

            //notify admin about new itemDesc
            $notification = new Notification;
            $notification->created_by              = User::first()->id;
            $notification->sender_id            = auth()->id();
            $notification->status_code          = 'success';
            $notification->type                = 'Item Desciption';
            $notification->event                = 'Created';
            $notification->title                = 'New Item Desciption Created';
            $notification->message              = 'New tem Desciption  '.$itemDesc->title.' Added.';
            $notification->read_status          = false;
            $notification->data_id              = $itemDesc->id;
            $notification->save();

            DB::commit();
            return response(prepareResult(false, $itemDesc, trans('translate.item_desc_created')),config('httpcodes.created'));
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
    //view Item Description
    public function show($id) {
        try
        {
            $itemDesc = ItemDescriptionMaster::find($id);
            return response(prepareResult(false, $itemDesc, trans('translate.item_desc_detail')), config('httpcodes.success'));
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
    //update Item Description
    public function update(Request $request, $id)
    {
        $validation = \Validator::make($request->all(), [
            'title'      => 'required|regex:/^[a-zA-Z0-9-_ \/]+$/',
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), $validation->messages()->first()), config('httpcodes.bad_request'));
        }

        DB::beginTransaction();
        try {

            $itemDesc = ItemDescriptionMaster::where('id',$id)->first();
            if(!$itemDesc)
            {
                return response(prepareResult(true, [],trans('translate.record_not_found')), config('httpcodes.not_found'));
            }
            $checkAlready = ItemDescriptionMaster::where('title',$request->title)->where('id','!=',$id)->first();
            if(!empty($checkAlready)){
                return response(prepareResult(true,[],trans('translate.item_desc_already_exist')), config('httpcodes.bad_request')); 
            }
            $itemDesc->title = $request->title;
            $itemDesc->save();

            DB::commit();
            return response(prepareResult(false, $itemDesc, trans('translate.item_desc_updated')),config('httpcodes.success'));
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
    //delete Item Description
    public function destroy($id)
    {
        try {
            $item_desc= ItemDescriptionMaster::where('id',$id)->first();
            if (!is_object($item_desc)) {
                 return response(prepareResult(true, [],trans('translate.record_not_found')), config('httpcodes.not_found'));
            }
            
            $deleteOrg = $item_desc->delete();
            return response(prepareResult(false, [], trans('translate.item_desc_deleted')), config('httpcodes.success'));
        }
        catch(Exception $exception) {
            return response(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }
    }

    
}
