<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Notification;
use App\Models\WorkPackage;
use Validator;
use Auth;
use Exception;
use DB;
class WorkPackageController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:workpack-browse');
        $this->middleware('permission:workpack-add', ['only' => ['store']]);
        $this->middleware('permission:workpack-edit', ['only' => ['update','action']]);
        $this->middleware('permission:workpack-read', ['only' => ['show']]);
        $this->middleware('permission:workpack-delete', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    //listing work package
    public function workPackages(Request $request)
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
            $query = WorkPackage::orderby($column,$dir);
            
            if(!empty($request->name))
            {
                $query->where('name', 'LIKE', '%'.$request->name.'%');
            }
            if(!empty($request->workPackId))
            {
                $query->where('workPackId',$request->workPackId);
            }
            
            if(!empty($request->status))
            {
                $query->where('status',$request->status);
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

            return response(prepareResult(false, $query, trans('translate.work_package_list')), config('httpcodes.success'));
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
    //create new work package
    public function store(Request $request)
    {
        $validation = \Validator::make($request->all(), [
            // 'workPackId'      => 'required|unique:work_packages,workPackId',
            'name'      => 'required|regex:/^[a-zA-Z0-9-_ @#\/]+$/',
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), $validation->messages()->first()), config('httpcodes.bad_request'));
        }

        DB::beginTransaction();
        try {
            $workPackage = new WorkPackage;
            $workPackage->workPackId = $request->workPackId;
            // $workPackage->workPackId = rand(1000,9999);
            $workPackage->name = $request->name;
            $workPackage->description = $request->description;
            $workPackage->unit_of_measure = $request->unit_of_measure;
            $workPackage->man_power_type = $request->man_power_type;
            $workPackage->user_id = auth()->id();
            $workPackage->status = $request->status ? $request->status : 1;
            $workPackage->save();

            $notification = new Notification;
            $notification->user_id              = User::first()->id;
            $notification->sender_id            = auth()->id();
            $notification->status_code          = 'success';
            $notification->type                = 'WorkPackage';
            $notification->event                = 'Created';
            $notification->title                = 'New Work-Package Created';
            $notification->message              = 'New Work-Package '.$workPackage->name.' created.';
            $notification->read_status          = false;
            $notification->data_id              = $workPackage->id;
            $notification->save();

            DB::commit();
            return response(prepareResult(false, $workPackage, trans('translate.work_package_created')),config('httpcodes.created'));
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
    //view work package
    public function show(WorkPackage $work_package)
    {
        try
        {
            return response(prepareResult(false, $work_package, trans('translate.work_package_detail')), config('httpcodes.success'));
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
    //update work package
    public function update(Request $request, $id)
    {
        $validation = \Validator::make($request->all(), [
            'name'      => 'required|regex:/^[a-zA-Z0-9-_ @#]+$/',
            // 'workPackId'     => 'required|unique:work_packages,workPackId,'.$id
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), $validation->messages()->first()), config('httpcodes.bad_request'));
        }

        DB::beginTransaction();
        try {
            $workPackage = WorkPackage::where('id',$id)->first();
            if(!$workPackage)
            {
                return response(prepareResult(true, [],'Work Package not found', config('httpcodes.not_found')));
            }
            $workPackage->workPackId = $request->workPackId;
            // $workPackage->workPackId = rand(1000,9999);
            $workPackage->name = $request->name;
            $workPackage->description = $request->description;
            $workPackage->unit_of_measure = $request->unit_of_measure;
            $workPackage->man_power_type = $request->man_power_type;
            $workPackage->status = $request->status ? $request->status : 1;
            $workPackage->save();

            DB::commit();
            return response(prepareResult(false, $workPackage, trans('translate.work_package_updated')),config('httpcodes.success'));
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
    //delete work package
    public function destroy($id)
    {
         try {
            $WorkPackage= WorkPackage::where('id',$id)->first();
            if (!is_object($WorkPackage)) {
                 return response(prepareResult(true, [],'Work Package not found', config('httpcodes.not_found')));
            }
            
            $deleteWorkPackage = $WorkPackage->delete();
            return response(prepareResult(false, [], trans('translate.work_package_deleted')), config('httpcodes.success'));
        }
        catch(Exception $exception) {
            return response(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }
    }

    //action performed on work package

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
                $work_packages = WorkPackage::whereIn('id',$ids)->delete();
                $message = trans('translate.work_package_deleted');
            }
            elseif($request->action == 'inactive')
            {
                WorkPackage::whereIn('id',$ids)->update(['status'=>"2"]);
                $message = trans('translate.work_package_inactivated');
            }
            elseif($request->action == 'active')
            {
                WorkPackage::whereIn('id',$ids)->update(['status'=>"1"]);
                $message = trans('translate.work_package_activated');
            }
            $work_packages = WorkPackage::whereIn('id',$ids)->get();
            DB::commit();
            return response(prepareResult(false, $work_packages, $message), config('httpcodes.success'));
        }
        catch (\Throwable $e) {
            \Log::error($e);
            return response(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }
    }
}
