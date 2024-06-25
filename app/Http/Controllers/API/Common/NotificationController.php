<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use Auth;
use Log;
use Edujugon\PushNotification\PushNotification;

class NotificationController extends Controller
{
    public function __construct()
    {

    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    //getting list of notifications
    public function index(Request $request)
    {
        try
        {
            if($request->mark_all_as_read == 'true' || $request->mark_all_as_read == 1)
            {
                Notification::where('user_id',Auth::id())->where('read_status', '!=', 1)->update(['read_status' => 1, 'read_at' => date('Y-m-d H:i:s')]);
            }

            $query =  Notification::where('user_id',Auth::id())->orderBy('id','DESC');
            
            if(!empty($request->read_status) && $request->read_status==0)
            {
                $query->where('read_status', 0);
            }
            elseif(!empty($request->read_status) && $request->read_status==1)
            {
                $query->where('read_status', 1);
            }
            
            if($request->type)
            {
                $query->where('type',$request->type);
            }
            if(!empty($request->perPage))
            {
                $perPage = $request->perPage;
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
            return response(prepareResult(false, $query, trans('translate.notification_list')), config('httpcodes.success'));
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
    //view notification
    public function show(Notification $notification)
    {
        return response(prepareResult(false, $userinfo, trans('translate.notification_detail')), config('httpcodes.success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    //delete notification
    public function destroy(Notification $notification)
    {
        try
        {
            $notification->delete();
            return response(prepareResult(false, [], trans('translate.notification_deleted')), config('httpcodes.success'));
        } catch (\Throwable $e) {
            \Log::error($e);
            return response(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }
    }

    /**
     *Read Single Notification on the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int  $id
     * @return \Illuminate\Http\Response
     */
    //mark notification read
    public function read($id)
    {
        try
        {
            $notification = Notification::find($id);
            $notification->update(['read_status' => true, 'read_at' => date('Y-m-d H:i:s')]);
            return response(prepareResult(false, $notification, trans('translate.read')), config('httpcodes.success'));
        } catch (\Throwable $e) {
            \Log::error($e);
            return response(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }
    }

    /**
     *Read All Notification on the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int  $id
     * @return \Illuminate\Http\Response
     */
    //mark all notifications read
    public function userNotificationReadAll()
    {
        try
        {
            Notification::where('user_id', Auth::id())->where('read_status', '!=', 1)->update(['read_status' => true, 'read_at' => date('Y-m-d H:i:s')]);
            return response(prepareResult(false, [], trans('translate.all_read')), config('httpcodes.success'));
        } catch (\Throwable $e) {
            \Log::error($e);
            return response(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }
    }

    /**
     *delete Perticular User All Notifications  on the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int  $id
     * @return \Illuminate\Http\Response
     */
    //delete all notification of a perticulr user
    public function userNotificationDelete()
    {
        try
        {
            Notification::where('user_id', Auth::id())->delete();
            return response(prepareResult(false, [], trans('translate.notification_deleted')), config('httpcodes.success'));
        } catch (\Throwable $e) {
            \Log::error($e);
            return response(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }
    }

    /**
     *get Unread  Notifications Count on the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param Request  $request
     * @return \Illuminate\Http\Response
     */
    //get user notification count
    public function unreadNotificationsCount()
    {
        try
        {
            
            $count = Notification::where('user_id',Auth::id())->where('read_status',0)->count();
            return response(prepareResult(false, $query, trans('translate.fetched_count')), config('httpcodes.success'));
        } catch (\Throwable $e) {
            \Log::error($e);
            return response(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }
    }
}