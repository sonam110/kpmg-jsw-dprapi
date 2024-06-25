<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/

Route::namespace('App\Http\Controllers\API\Common')->group(function () {

    Route::controller(AuthController::class)->group(function () {
        Route::get('unauthorized', 'unauthorized')->name('unauthorized');
        Route::post('login', 'login')->name('login');
        Route::post('verify-otp', 'verifyOtp')->name('verify-otp');
        Route::post('forgot-password', 'forgotPassword')->name('forgot-password');
        Route::get('reset-password/{token}','resetPassword')->name('password.reset');
        Route::post('update-password', 'updatePassword')->name('update-password');
    });

    Route::get('file-access/{folderName}/{fileName}', 'FileUploadController@getFile');       
    Route::group(['middleware' => 'auth:api'],function () {
        Route::controller(AuthController::class)->group(function () {
            Route::post('logout', 'logout')->name('logout');
            Route::post('change-password', 'changePassword')->name('changePassword');
            Route::get('my-profile', 'myProfile')->name('my-profile');
            Route::post('update-profile', 'updateProfile')->name('update-profile');
        });

        Route::controller(DashboardController::class)->group(function () {
            Route::post('dashboard','dashboard')->name('dashboard');
            Route::post('dpr-uploads-graph','dprUploadsGraph')->name('dpr-uploads-graph');
            Route::post('manpower-graph','manpowerGraph')->name('manpower-graph');
            Route::post('dpr-uploads-man-power-graph','dprUploadsManPowerGraph')->name('dpr-uploads-man-power-graph');
            Route::post('item-desc-list', 'itemDescList')->name('item-desc-list');
        });

        /*----------Roles------------------------------*/
        Route::controller(RoleController::class)->group(function () {
            Route::post('roles', 'roles')->name('roles');
            Route::apiResource('role', RoleController::class)->only(['store','destroy','show', 'update']);
            Route::post('role-action', 'action')->name('role-action');
        });


        Route::controller(FileUploadController::class)->group(function () {
            Route::post('file-uploads', 'fileUploads')->name('file-uploads');
            Route::post('file-upload', 'store')->name('file-upload');
        });

        Route::controller(NoMiddlewareController::class)->group(function () {
            Route::post('vendors', 'vendors')->name('vendors');
            Route::post('projects', 'projects')->name('projects');
            Route::post('work-packages', 'workPackages')->name('work-packages');
            Route::post('dpr-configs', 'dprConfigs')->name('dpr-configs');
            Route::get('dpr-config/{id}', 'dprConfigShow')->name('dpr-config-show');
            Route::post('vendor-workpack', 'vendorWorkPack')->name('vendor-workpack');
        });

        Route::controller(OrganizationController::class)->group(function () {
            Route::post('organizations', 'organizations')->name('organizations');
            Route::apiResource('organization', OrganizationController::class)->only(['store','destroy','show', 'update']);
        });

        Route::controller(VendorController::class)->group(function () {
            //Route::post('vendors', 'vendors')->name('vendors');
            Route::apiResource('vendor', VendorController::class)->only(['store','destroy','show', 'update']);
            Route::post('vendor-action', 'action')->name('vendor-action');
        });

        Route::controller(ItemDescController::class)->group(function () {
            Route::apiResource('item-desc', ItemDescController::class)->only(['store','destroy','show', 'update']);
        });

        Route::controller(ProjectController::class)->group(function () {
           // Route::post('projects', 'projects')->name('projects');
            Route::apiResource('project', ProjectController::class)->only(['store','destroy','show', 'update']);
            Route::post('project-action', 'action')->name('project-action');
        });

        Route::controller(WorkPackageController::class)->group(function () {
           // Route::post('work-packages', 'workPackages')->name('work-packages');
            Route::apiResource('work-package', WorkPackageController::class)->only(['store','destroy','show', 'update']);
            Route::post('work-package-action', 'action')->name('work-package-action');
        });
        
        Route::controller(DprConfigController::class)->group(function () {
           // Route::post('dpr-configs', 'dprConfigs')->name('dpr-configs');
            Route::apiResource('dpr-config', DprConfigController::class)->only(['store','destroy','update']);
            Route::post('dpr-config-action', 'action')->name('dpr-config-action');
        });

        Route::controller(DprMapController::class)->group(function () {
            Route::post('dpr-map-view', 'DprMapView')->name('dpr-map-view');
            Route::post('update-dpr-map', 'updateDprMap')->name('update-dpr-map');
        });

        /*-------------DprtImport------------------------*/
        Route::controller(DprImportController::class)->group(function () {
            Route::post('dpr-import','dprImport')->name('dpr-import');
            Route::post('dpr-import-list','dprImportList')->name('dpr-import-list');
            Route::post('dpr-import-store','store')->name('dpr-import-store');
            
            Route::post('download-report','downloadReport')->name('download-report');
            Route::post('download-excel','downloadExcel')->name('download-excel');
            Route::post('download-pdf','downloadReport')->name('download-pdf');
            Route::post('work-item-list','workItemList')->name('work-item-list');
            Route::post('summary-report','summaryReport')->name('summary-report');
        });

        

        /*-------------Permission------------------------*/
        Route::controller(PermissionController::class)->group(function () {
            Route::post('permissions','permissions');
            Route::apiResource('permission',PermissionController::class)->only(['store','destroy','show', 'update']);
        });

        //----------------------------Notification----------------------//
        Route::controller(NotificationController::class)->group(function () {
            Route::post('/notifications','index');
            Route::apiResource('/notification', NotificationController::class)->only('store','destroy','show');
            Route::get('/notification/{id}/read', 'read');
            Route::get('/user-notification-read-all', 'userNotificationReadAll');
            Route::get('/user-notification-delete', 'userNotificationDelete');
            Route::post('/notification-check', 'notificationCheck');
            Route::get('/unread-notification-count', 'unreadNotificationsCount');
        });
    });
});

Route::namespace('App\Http\Controllers\API\Admin')->group(function () {

    Route::controller(AppSettingController::class)->group(function () {
        Route::get('app-setting', 'appSetting')->name('app-setting');
        Route::post('update-setting', 'updateSetting')->name('update-setting');
    });
    Route::group(['middleware' => 'auth:api'],function () {
        
        Route::controller(UserController::class)->group(function () {
            Route::post('users', 'users')->name('users');
            Route::apiResource('user', UserController::class)->only(['store','show', 'update']);
            Route::post('user-action', 'userAction')->name('user-action');
        });

        
        Route::controller(ActivityController::class)->group(function () {
            Route::post('activities', 'activities')->name('activities');
            Route::get('activities-info/{activity_id}', 'activitiesInfo')->name('activities-info');
        });

        /*---------------------Logs------------------------*/
        Route::controller(LogController::class)->group(function () {
            Route::post('logs','logs')->name('logs');
        });
    });
}); 



