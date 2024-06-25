<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\User;
use App\Models\Vendor;
use Validator;
use Auth;
use Exception;
use DB;
class VendorController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:vendor-browse',['only' => ['vendors']]);
        $this->middleware('permission:vendor-add', ['only' => ['store']]);
        $this->middleware('permission:vendor-edit', ['only' => ['update','action']]);
        $this->middleware('permission:vendor-read', ['only' => ['show']]);
        $this->middleware('permission:vendor-delete', ['only' => ['destroy']]);
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    //creating new vendor
     public function store(Request $request)
    {
        $validation = \Validator::make($request->all(), [
            'name'      => 'required|regex:/^[a-zA-Z0-9-_ &@#\/]+$/',
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), $validation->messages()->first()), config('httpcodes.bad_request'));
        }

        DB::beginTransaction();
        try {
            $vendor = new Vendor;
            $vendor->name = $request->name;
            $vendor->description = $request->description;
            // $vendor->user_id = auth()->id();
            $vendor->status = $request->status ? $request->status : 1;
            $vendor->save();

            //notify admin about new vendor
            $notification = new Notification;
            $notification->user_id              = User::first()->id;
            $notification->sender_id            = auth()->id();
            $notification->status_code          = 'success';
            $notification->type                = 'Vendor';
            $notification->event                = 'Created';
            $notification->title                = 'New Vendor Created';
            $notification->message              = 'New Vendor '.$vendor->name.' Added.';
            $notification->read_status          = false;
            $notification->data_id              = $vendor->id;
            $notification->save();

            DB::commit();
            return response(prepareResult(false, $vendor, trans('translate.vendor_created')),config('httpcodes.created'));
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
    //view vendor
    public function show(Vendor $vendor)
    {
        try
        {
            return response(prepareResult(false, $vendor, trans('translate.vendor_detail')), config('httpcodes.success'));
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
    //update vendor
    public function update(Request $request, $id)
    {
        $validation = \Validator::make($request->all(), [
            'name'      => 'required|regex:/^[a-zA-Z0-9-_ &@#\/]+$/',
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), $validation->messages()->first()), config('httpcodes.bad_request'));
        }

        DB::beginTransaction();
        try {
            $vendor = Vendor::where('id',$id)->first();
            if(!$vendor)
            {
                return response(prepareResult(true, [],trans('translate.record_not_found')), config('httpcodes.not_found'));
            }
            $vendor->name = $request->name;
            $vendor->description = $request->description;
            $vendor->status = $request->status ? $request->status : 1;
            $vendor->save();

            DB::commit();
            return response(prepareResult(false, $vendor, trans('translate.vendor_updated')),config('httpcodes.success'));
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
    //delete vendor
    public function destroy($id)
    {
        try {
            $vendor= Vendor::where('id',$id)->first();
            if (!is_object($vendor)) {
                 return response(prepareResult(true, [],trans('translate.record_not_found')), config('httpcodes.not_found'));
            }
            
            $deleteOrg = $vendor->delete();
            return response(prepareResult(false, [], trans('translate.vendor_deleted')), config('httpcodes.success'));
        }
        catch(Exception $exception) {
            return response(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }
    }

    //action performed on vendor

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
                $vendors = Vendor::whereIn('id',$ids)->delete();
                $message = trans('translate.vendor_deleted');
            }
            elseif($request->action == 'inactive')
            {
                Vendor::whereIn('id',$ids)->update(['status'=>"2"]);
                $message = trans('translate.vendor_inactivated');
            }
            elseif($request->action == 'active')
            {
                Vendor::whereIn('id',$ids)->update(['status'=>"1"]);
                $message = trans('translate.vendor_activated');
            }
            $vendors = Vendor::whereIn('id',$ids)->get();
            DB::commit();
            return response(prepareResult(false, $vendors, $message), config('httpcodes.success'));
        }
        catch (\Throwable $e) {
            \Log::error($e);
            return response(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }
    }
}
