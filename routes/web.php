<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RuleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\pointsController;

use App\Http\Controllers\PostmanController;
use App\Http\Controllers\regionsController;
use App\Http\Controllers\sectorsController;
use App\Http\Controllers\settingController;
use App\Http\Controllers\outgoingController;

use App\Http\Controllers\SettingsController;

use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\governmentController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\dashboard\IoTelegramController;
use App\Http\Controllers\qualificationController;
use App\Http\Controllers\statisticController;
use App\Http\Controllers\ReservationStaticsController;
use App\Http\Controllers\ReservationStaticsCreditController;
use App\Http\Controllers\ReserveFetchController;
use App\Http\Controllers\ReserveSectorController;
use App\Http\Controllers\SubDepartmentStatsController;
use App\Http\Controllers\SubDepartmentReservationController;
use App\Http\Controllers\PrisonersDetailsController;
use App\Http\Controllers\SectorEmployeesDetailsController;
use App\Http\Controllers\ReservationReportController;
use App\Http\Controllers\DepartmentEmployeesDetailsController;



use App\Models\paperTransaction;

//
use App\Http\Controllers\ReservationAllowanceController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


// Route::middleware('auth')->group(function () {
//     Route::get('/dashboard', function () {
//         // Matches /admin/dashboard URL
//     });

// });


Route::get('/login', function () {
    return view('login');
});
Route::post('/login', [UserController::class, 'login'])->name('login');
Route::any('/logout', [UserController::class, 'logout'])->name('logout');
Route::post('/verfication_code', [UserController::class, 'verfication_code'])->name('verfication_code');
Route::post('/resend_code', [UserController::class, 'resend_code'])->name('resend_code');

Route::get('/forget-password', function () {
    return view('forgetpassword');
})->name('forget_password');

Route::any('/forget_password2', [UserController::class, 'forget_password2'])->name('forget_password2');
Route::any('/reset_password', [UserController::class, 'reset_password'])->name('reset_password');


//  Auth verfication_code
Route::middleware(['auth'])->group(function () {

    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/user-departments/{flag}', [UserController::class, 'index'])->name('user.departments')->middleware('check.permission:view User');
    Route::get('api/users', [UserController::class, 'getUsers'])->name('api.users')->middleware('check.permission:view User');
    Route::get('/users_create', [UserController::class, 'create'])->name('user.create')->middleware('check.permission:create User');
    Route::post('/store', [UserController::class, 'store'])->name('user.store')->middleware('check.permission:create User');

    Route::any('/getGoverment/{id}', [UserController::class, 'getGoverment'])->name('user.getGoverment')->middleware('check.permission:view Government');
    Route::any('/getRegion/{id}', [UserController::class, 'getRegion'])->name('user.getRegion')->middleware('check.permission:view Region');

    Route::any('/employees/{flag}', [UserController::class, 'index'])->name('user.employees')->middleware('check.permission:view User');
    Route::post('/employees-add', [UserController::class, 'add_employees'])->name('user.employees.add')->middleware('check.permission:create User');
    Route::get('/edit/{user}', [UserController::class, 'edit'])->name('user.edit')->middleware('check.permission:edit User');
    Route::get('/show/{user}', [UserController::class, 'show'])->name('user.show')->middleware('check.permission:view User');
    Route::post('/update/{user}', [UserController::class, 'update'])->name('user.update')->middleware('check.permission:edit User');
    Route::any('/unsigned', [UserController::class, 'unsigned'])->name('user.unsigned');
    Route::any('/get-deprt-sector', [UserController::class, 'GetDepartmentsBySector'])->name('user.department.sector');
    // permission
    Route::any('/permission', [PermissionController::class, 'index'])->name('permission.index')->middleware('check.permission:view Permission');
    Route::get('api/permission', [PermissionController::class, 'getPermision'])->name('api.permission')->middleware('check.permission:view Permission');
    Route::any('/permission_create', [PermissionController::class, 'create'])->name('permission.create')->middleware('check.permission:create Permission');
    Route::any('/permission_edit/{id}', [PermissionController::class, 'edit'])->name('permissions_edit')->middleware('check.permission:edit Permission');
    Route::any('/permission_show/{id}', [PermissionController::class, 'show'])->name('permissions_show')->middleware('check.permission:edit Permission');
    Route::any('/permission_store', [PermissionController::class, 'store'])->name('permission.store')->middleware('check.permission:edit Permission');
    Route::any('/permission_delete/{id}', [PermissionController::class, 'destroy'])->name('permissions_destroy')->middleware('check.permission:delete Permission');






    // rule
    Route::any('/rule', [RuleController::class, 'index'])->name('rule.index')->middleware('check.permission:view Rule');
    Route::any('api/rule', [RuleController::class, 'getRule'])->name('api.rule')->middleware('check.permission:view Rule');
    Route::any('/rule_create', [RuleController::class, 'create'])->name('rule.create')->middleware('check.permission:create Rule');
    Route::any('/rule_store', [RuleController::class, 'store'])->name('rule.store')->middleware('check.permission:edit Rule');
    Route::any('/rule_edit/{id}', [RuleController::class, 'edit'])->name('rule_edit')->middleware('check.permission:edit Rule');
    Route::any('/rule_show/{id}', [RuleController::class, 'show'])->name('rule_show')->middleware('check.permission:edit Rule');
    Route::any('/rule_update/{id}', [RuleController::class, 'update'])->name('rule_update')->middleware('check.permission:edit Rule');


    // instantmission
    // Route::any('/instant_mission', [InstantmissionController::class, 'index'])->name('instant_mission.index')->middleware('check.permission:view instantmission');
    // Route::get('api/instant_mission', [InstantmissionController::class, 'getInstantMission'])->name('api.instant_mission')->middleware('check.permission:view instantmission');
    // Route::any('/instant_mission/create', [InstantmissionController::class, 'create'])->name('instant_mission.create')->middleware('check.permission:create instantmission');
    // Route::any('/instant_mission/edit/{id}', [InstantmissionController::class, 'edit'])->name('instant_mission.edit')->middleware('check.permission:edit instantmission');
    // Route::any('/instant_mission/show/{id}', [InstantmissionController::class, 'show'])->name('instant_mission.show')->middleware('check.permission:view instantmission');
    // Route::any('/instant_mission/update/{id}', [InstantmissionController::class, 'update'])->name('instant_mission.update')->middleware('check.permission:edit instantmission');
    // Route::any('/instant_mission/store', [InstantmissionController::class, 'store'])->name('instant_mission.store')->middleware('check.permission:create instantmission');
    // Route::any('/getGroups/{id}', [InstantmissionController::class, 'getGroups'])->name('instant_mission.getGroups')->middleware('check.permission:view instantmission');
    // Route::any('/getInspector/{team_id}/{group_id}', [InstantmissionController::class, 'getInspector'])->name('instant_mission.getInspector')->middleware('check.permission:view instantmission');





    // export
    //Start Export routes
    Route::get('/export/all', [outgoingController::class, 'index'])->name('Export.index')->middleware('check.permission:view outgoings');
    Route::get('/export/{id}/edit', [outgoingController::class, 'edit'])->name('Export.edit')->middleware('check.permission:edit outgoings');
    Route::get('/export/{id}/show', [outgoingController::class, 'show'])->name('Export.show')->middleware('check.permission:view outgoings');
    Route::post('/export/{id}', [outgoingController::class, 'update'])->name('Export.update')->middleware('check.permission:edit outgoings');
    Route::get('/export/create', [outgoingController::class, 'create'])->name('Export.create')->middleware('check.permission:create outgoings');
    Route::post('/export', [outgoingController::class, 'store'])->name('Export.store')->middleware('check.permission:edit outgoings');

    Route::get('/export/All/Archive', [outgoingController::class, 'getExportInActive'])->name('Export.view.archive')->middleware('check.permission:archive outgoings');
    Route::get('exports/get/active', [outgoingController::class, 'getExportActive'])->name('exports.view.all')->middleware('check.permission:view outgoings');
    Route::post('export/archive/add', [outgoingController::class, 'addToArchive'])->name('export.archive.add')->middleware('check.permission:add_archive outgoings');
    Route::get('export/AllArchives', [outgoingController::class, 'showArchive'])->name('Export.AllArchive')->middleware('check.permission:archive outgoings');
    //external users
    Route::get('external/users', [outgoingController::class, 'getExternalUsersAjax'])->name('external.users')->middleware('check.permission:view exportuser');
    Route::post('exportuser/ajax', [outgoingController::class, 'addUaersAjax'])->name('userexport.ajax')->middleware('check.permission:edit exportuser');
    //outgingfiles
    Route::get('export/{id}/upload', [outgoingController::class, 'uploadFiles'])->name('Export.upload.files')->middleware('check.permission:edit outgoing_files');
    Route::get('export/{id}/vieFiles', [outgoingController::class, 'showFiles'])->name('Export.view.files')->middleware('check.permission:view outgoing_files');
    // Route::post('/testUpload', [outgoingController::class, 'testUpload'])->name('testUpload')->middleware('check.permission:view ExternalDepartment');
    Route::get('/downlaodfile/{id}', [outgoingController::class, 'downlaodfile'])->name('downlaodfile')->middleware('check.permission:download outgoing_files');
    //End Export routes

    Route::get('generateNumber/{counter}', [outgoingController::class, 'generateUniqueNumber']);
    Route::get('getLatest', [outgoingController::class, 'getTheLatestExport']);


    // getDepartment
    Route::get('api/department/{uuid}', [DepartmentController::class, 'getDepartment'])->name('api.department')->middleware('check.permission:view departements');
    Route::get('api/sub_department/{uuid}', [DepartmentController::class, 'getSub_Department'])
        ->name('api.sub_department')
        ->middleware('check.permission:view departements');
    Route::get('/sub_departments/{uuid}', [DepartmentController::class, 'index_1'])->name('sub_departments.index')->middleware('check.permission:view departements');
    Route::get('/sub_departments/create/{uuid}', [DepartmentController::class, 'create_1'])->name('sub_departments.create')->middleware('check.permission:create departements');
    Route::post('/sub_departments', [DepartmentController::class, 'store_1'])->name('sub_departments.store')->middleware('check.permission:edit departements');
    Route::get('/sub_departments/{department}/edit', [DepartmentController::class, 'edit_1'])->name('sub_departments.edit')->middleware('check.permission:edit departements');
    Route::put('/sub_departments/{department}', [DepartmentController::class, 'update_1'])->name('sub_departments.update')->middleware('check.permission:edit departements');
    // Route::post('departments_store', [DepartmentController::class, 'store'])->middleware('check.permission:view departements');
    // Route::put('departments_update/{department}', [DepartmentController::class, 'update']);
    // Route::delete('departments_delete/{department}', [DepartmentController::class, 'destroy']);
    Route::get('/departments/{uuid}', [DepartmentController::class, 'index'])->name('departments.index')->middleware('check.permission:view departements');
    Route::get('/department/add/create/{uuid}', [DepartmentController::class, 'create'])->name('department.create')->middleware('check.permission:create departements');
    Route::get('/departments/show/{department}', [DepartmentController::class, 'show'])->name('departments.show')->middleware('check.permission:view departements');
    Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store')->middleware('check.permission:edit departements');
    Route::get('/departments/{department}/edit', [DepartmentController::class, 'edit'])->name('departments.edit')->middleware('check.permission:edit departements');
    Route::put('/departments/{department}', [DepartmentController::class, 'update'])->name('departments.update')->middleware('check.permission:edit departements');
    Route::get('departments/delete/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy')->middleware('check.permission:delete departements');;
    // Route::resource('postmans', PostmanController::class);
    Route::get('/postmans/create', [PostmanController::class, 'create'])->name('postmans.create')->middleware('check.permission:create Postman');
    Route::post('/postmans', [PostmanController::class, 'store'])->name('postmans.store')->middleware('check.permission:edit Postman');
    Route::get('/postmans/{postman}/edit', [PostmanController::class, 'edit'])->name('postmans.edit')->middleware('check.permission:edit Postman');
    Route::put('/postmans/{postman}', [PostmanController::class, 'update'])->name('postmans.update')->middleware('check.permission:edit Postman');

    //start jobs
    Route::get('setting/jobs', [settingController::class, 'getAlljob'])->name('setting.getAlljob')->middleware('check.permission:view job');
    Route::get('setting/jobs/all', [settingController::class, 'indexjob'])->name('job.index')->middleware('check.permission:view job');
    Route::post('setting/jobs/add', [settingController::class, 'addjob'])->name('job.add')->middleware('check.permission:edit job');
    //Route::get('setting/jobs/create', [settingController::class,'createjob'])->name('job.create');
    Route::post('setting/jobs/update', [settingController::class, 'updatejob'])->name('job.update')->middleware('check.permission:edit job');
    //Route::get('setting/jobs/edit/{id}', [settingController::class,'editjob'])->name('job.edit');
    Route::get('setting/jobs/show/{id}', [settingController::class, 'showjob'])->name('job.show')->middleware('check.permission:view job');
    Route::post('setting/jobs/delete', [settingController::class, 'deletejob'])->name('job.delete')->middleware('check.permission:delete job');
    //end jobs



    Route::get('/settings', [settingController::class, 'allSettings'])->name('settings.index')->middleware('check.permission:view Setting');
    Route::get('get_settings', [settingController::class, 'getSettings'])->name('get.settings')->middleware('check.permission:view Setting');
    Route::post('/setting/store', [settingController::class, 'CreateSetting'])->name('setting.store')->middleware('check.permission:create Setting');
    Route::post('/setting/update', [settingController::class, 'UpdateSetting'])->name('setting.update')->middleware('check.permission:edit Setting');
    Route::get('/setting/delete', [settingController::class, 'deleteSetting'])->name('setting.delete')->middleware('check.permission:delete Setting');

    Route::get('/get-grades', [UserController::class, 'getGradesByViolationType'])->name('get.grades');

    //start gards
    Route::get('setting/grads', [settingController::class, 'getAllgrads'])->name('setting.getAllgrads')->middleware('check.permission:view grade');
    Route::get('setting/grads/all', [settingController::class, 'indexgrads'])->name('grads.index')->middleware('check.permission:view grade');
    Route::post('setting/grads/add', [settingController::class, 'addgrads'])->name('grads.add')->middleware('check.permission:edit grade');
    Route::post('setting/grads/update', [settingController::class, 'updategrads'])->name('grads.update')->middleware('check.permission:edit grade');
    Route::get('setting/grads/show/{id}', [settingController::class, 'showgrads'])->name('grads.show')->middleware('check.permission:view grade');
    Route::post('setting/grads/delete', [settingController::class, 'deletegrads'])->name('grads.delete')->middleware('check.permission:delete grade');
    //end grads
    //start Nationality
    Route::get('setting/nationality', [settingController::class, 'getAllNationality'])->name('nationality.getAllNationality')->middleware('check.permission:view job');
    Route::get('setting/nationality/all', [settingController::class, 'indexbationality'])->name('nationality.index')->middleware('check.permission:view job');
    Route::get('setting/nationality/create', [settingController::class, 'createnationality'])->name('setting.createnationality')->middleware('check.permission:view job');

    Route::post('setting/nationality/add', [settingController::class, 'addNationality'])->name('nationality.add')->middleware('check.permission:edit job');
    Route::get('setting/nationality/edit', [settingController::class, 'editnationality'])->name('setting.editnationality')->middleware('check.permission:view job');

    Route::post('setting/nationality/update', [settingController::class, 'updatenationality'])->name('nationality.update')->middleware('check.permission:edit job');
    Route::get('setting/nationality/show/{id}', [settingController::class, 'shownationality'])->name('nationality.show')->middleware('check.permission:view job');
    Route::post('setting/nationality/delete', [settingController::class, 'deletenationality'])->name('nationality.delete')->middleware('check.permission:delete job');
    //end nationality
    //Start qualifications -------- Need middleware for gard
    Route::get('setting/qualifications/all', [qualificationController::class, 'index'])->name('qualifications.index')->middleware('check.permission:view Qualification');
    Route::get('setting/qualifications/ajax', [qualificationController::class, 'getqualification'])->name('getAllqualification')->middleware('check.permission:view Qualification');
    Route::post('setting/qualifications/create', [qualificationController::class, 'store'])->name('qualification.store')->middleware('check.permission:create Qualification');
    Route::post('setting/qualifications/edit', [qualificationController::class, 'update'])->name('qualification.update')->middleware('check.permission:edit Qualification');
    // Route::post('setting/qualifications/delete', [qualificationController::class, 'destroy'])->name('qualification.delete')->middleware('check.permission:view Qualification');

    //End qualifications
    //start government
    Route::get('setting/government', [regionsController::class, 'getAllgovernment'])->name('setting.getAllgovernment')->middleware('check.permission:view Government');
    Route::get('setting/government/all', [regionsController::class, 'indexgovernment'])->name('government.all')->middleware('check.permission:view Government');
    Route::post('setting/government/add', [regionsController::class, 'addgovernment'])->name('government.add')->middleware('check.permission:edit Government');
    Route::get('setting/government/create', [regionsController::class, 'creategovernment'])->name('government.create')->middleware('check.permission:create Government');
    Route::post('setting/government/update', [regionsController::class, 'updategovernment'])->name('government.update')->middleware('check.permission:edit Government');
    Route::get('setting/government/edit/{id}', [regionsController::class, 'editgovernment'])->name('government.edit')->middleware('check.permission:edit Government');
    Route::get('setting/government/show/{id}', [regionsController::class, 'showgovernment'])->name('government.show')->middleware('check.permission:view Government');
    //endgovernment
    //Start Regions
    Route::get('setting/Regions/all/{id}', [regionsController::class, 'index'])->name('regions.index')->middleware('check.permission:view Region');
    Route::get('setting/Regions/ajax', [regionsController::class, 'getregions'])->name('getAllregions')->middleware('check.permission:view Region');
    Route::get('setting/RegionBygovernment', [regionsController::class, 'getregionBygovernment'])->name('getAllregionsBygovernment')->middleware('check.permission:view Region');
    Route::post('setting/Regions/create', [regionsController::class, 'store'])->name('regions.store')->middleware('check.permission:create Region');
    Route::post('setting/Regions/edit', [regionsController::class, 'update'])->name('regions.update')->middleware('check.permission:edit Region');
    // Route::get('setting/Regions/all/{id}', [regionsController::class, 'index'])->name('regions.index')->middleware('check.permission:view Region');
    // Route::get('setting/Regions/ajax', [regionsController::class, 'getregions'])->name('getAllregions')->middleware('check.permission:view Region');
    // Route::get('setting/RegionBygovernment', [regionsController::class, 'getregionBygovernment'])->name('getAllregionsBygovernment')->middleware('check.permission:view Region');
    // Route::post('setting/Regions/create', [regionsController::class, 'store'])->name('regions.store')->middleware('check.permission:create Region');
    // Route::post('setting/Regions/edit', [regionsController::class, 'update'])->name('regions.update')->middleware('check.permission:edit Region');
    // Route::post('setting/Regions/delete', [regionsController::class, 'destroy'])->name('regions.delete')->middleware('check.permission:view Region');
    //End Regions
    //Start sectors
    Route::get('sectors/all', [sectorsController::class, 'index'])->name('sectors.index')->middleware('check.permission:view Sector');
    Route::get('sectors/ajax', [sectorsController::class, 'getsectors'])->name('getAllsectors')->middleware('check.permission:view Sector');
    // Route::get('sectors', [sectorsController::class, 'getregionBygovernment'])->name('getAllregionsBygovernment')->middleware('check.permission:view Region');
    Route::get('sectors/create', [sectorsController::class, 'create'])->name('sectors.create')->middleware('check.permission:create Sector');
    Route::post('sectors/add', [sectorsController::class, 'store'])->name('sectors.store')->middleware('check.permission:create Sector');

    //End sectors

    //End points
    //End sectors
    Route::get('sectors/all', [sectorsController::class, 'index'])->name('sectors.index')->middleware('check.permission:view Sector');
    Route::get('sectors/ajax', [sectorsController::class, 'getsectors'])->name('getAllsectors')->middleware('check.permission:view Sector');
    Route::get('sectors/show/{sector}', [sectorsController::class, 'show'])->name('sectors.show')->middleware('check.permission:view Sector');
    Route::get('sectors/create', [sectorsController::class, 'create'])->name('sectors.create')->middleware('check.permission:create Sector');
    Route::post('sectors/add', [sectorsController::class, 'store'])->name('sectors.store')->middleware('check.permission:create Sector');
    Route::get('sectors/edit/{sector}', [sectorsController::class, 'edit'])->name('sectors.edit')->middleware('check.permission:edit Sector');

    Route::post('sectors/update/{sector}', [sectorsController::class, 'update'])->name('sectors.update')->middleware('check.permission:edit Sector');
    // //End sectors







    Route::post('postman/ajax', [IoTelegramController::class, 'addPostmanAjax'])->name('postman.ajax')->middleware('check.permission:create Postman');
    Route::get('postmans', [IoTelegramController::class, 'getPostmanAjax'])->name('postman.get')->middleware('check.permission:view Postman');
    Route::post('department/ajax', [IoTelegramController::class, 'addExternalDepartmentAjax'])->name('department.ajax')->middleware('check.permission:create ExternalDepartment');
    Route::get('external/departments', [IoTelegramController::class, 'getExternalDepartments'])->name('external.departments')->middleware('check.permission:view ExternalDepartment');
    Route::get('internal/departments', [IoTelegramController::class, 'getDepartments'])->name('internal.departments')->middleware('check.permission:view departements');
    Route::get('iotelegrams', [IoTelegramController::class, 'index'])->name('iotelegrams.list')->middleware('check.permission:view Iotelegram');
    Route::get('iotelegrams/get/{id?}', [IoTelegramController::class, 'getIotelegrams'])->name('iotelegrams.get')->middleware('check.permission:view Iotelegram');
    Route::get('iotelegram/add', [IoTelegramController::class, 'create'])->name('iotelegrams.add')->middleware('check.permission:create Iotelegram');
    Route::post('iotelegram/store', [IoTelegramController::class, 'store'])->name('iotelegram.store')->middleware('check.permission:edit Iotelegram');
    Route::get('iotelegram/edit/{id}', [IoTelegramController::class, 'edit'])->name('iotelegram.edit')->middleware('check.permission:edit Iotelegram');
    Route::post('iotelegram/update/{id}', [IoTelegramController::class, 'update'])->name('iotelegram.update')->middleware('check.permission:edit Iotelegram');
    Route::get('iotelegram/show/{id}', [IoTelegramController::class, 'show'])->name('iotelegram.show')->middleware('check.permission:view Iotelegram');
    Route::get('iotelegram/archives', [IoTelegramController::class, 'archives'])->name('iotelegram.archives')->middleware('check.permission:archive Iotelegram');
    Route::get('iotelegram/archives/get', [IoTelegramController::class, 'getArchives'])->name('iotelegram.archives.get')->middleware('check.permission:archive Iotelegram');
    Route::get('iotelegram/archive/{id}', [IoTelegramController::class, 'AddArchive'])->name('iotelegram.archive.add')->middleware('check.permission:add_archive Iotelegram');
    Route::get('iotelegram/downlaod/{id}', [IoTelegramController::class, 'downlaodfile'])->name('iotelegram.downlaodfile')->middleware('check.permission:download Iotelegram');


    // ->middleware('check.permission:fetch ReservationAllowance');
    // ->middleware('check.permission:statistic ReservationAllowance');
    
    // ->middleware('check.permission:report ReservationAllowance');
    Route::get('/employees/by-department/{departmentId}', [DepartmentController::class, 'getEmployeesByDepartment'])->middleware('check.permission:view departements');




    /**
     * Search From Home
     */
    Route::get('/search/{search}/{q?}', [SearchController::class, 'index'])->name('search');
    Route::get('/searchUsers/users/{id}/{q?}', [SearchController::class, 'getUsers'])->name('search.users')->middleware('check.permission:view User');
    Route::get('/searchDept/departments/{q?}', [SearchController::class, 'getDepartments'])->name('search.departments');







    //reservation_allowances
    Route::any('/reservation_allowances', [ReservationAllowanceController::class, 'index'])->name('reservation_allowances.index')->middleware('check.permission:view ReservationAllowance');
    Route::any('/reservation_allowances/create', [ReservationAllowanceController::class, 'create'])->name('reservation_allowances.create')->middleware('check.permission:create ReservationAllowance');
    Route::any('/reservation_allowances/store', [ReservationAllowanceController::class, 'store'])->name('reservation_allowances.store')->middleware('check.permission:store ReservationAllowance');
    Route::any('/reservation_allowances/create_all', [ReservationAllowanceController::class, 'create_all'])->name('reservation_allowances.create.all')->middleware('check.permission:create ReservationAllowance');
    Route::any('/reservation_allowances/get_crate_all_form/{sector}/{department}', [ReservationAllowanceController::class, 'get_crate_all_form'])->name('reservation_allowances.get_crate_all_form')->middleware('check.permission:create ReservationAllowance');
    Route::any('/reservation_allowances/get_check_sector_department/{sector}/{department}/{civilNumber}', [ReservationAllowanceController::class, 'get_check_sector_department'])->name('reservation_allowances.get_check_sector_department')->middleware('check.permission:view ReservationAllowance');
    Route::any('/reservation_allowances/index_data/{sector}/{departement}/{date}', [ReservationAllowanceController::class, 'index_data'])->name('reservation_allowances.index_data')->middleware('check.permission:view ReservationAllowance');
    Route::any('/reservation_allowances/check_store', [ReservationAllowanceController::class, 'check_store'])->name('reservation_allowances.check_store')->middleware('check.permission:view ReservationAllowance');
    Route::any('/reservation_allowances/store_all', [ReservationAllowanceController::class, 'store_all'])->name('reservation_allowances.store.all')->middleware('check.permission:store ReservationAllowance');
    Route::any('/reservation_allowances/update', [ReservationAllowanceController::class, 'update'])->name('reservation_allowances.edit')->middleware('check.permission:update ReservationAllowance');
    Route::any('/reservation_allowances/edit/{id}', [ReservationAllowanceController::class, 'edit'])->name('reservation_allowances.update')->middleware('check.permission:edit ReservationAllowance');
    Route::any('/reservation_allowances/getAll', [ReservationAllowanceController::class, 'getAll'])->name('reservation_allowances.getAll')->middleware('check.permission:view ReservationAllowance');
    Route::any('/reservation_allowances/getAllWithMonth', [ReservationAllowanceController::class, 'getAllWithMonth'])->name('reservation_allowances.getAllWithMonth')->middleware('check.permission:view ReservationAllowance');
    Route::any('/reservation_allowances/get_departement/{id}/{type}', [ReservationAllowanceController::class, 'get_departement'])->name('reservation_allowances.get_departement')->middleware('check.permission:view ReservationAllowance');
    Route::any('/reservation_allowances/search_employee', [ReservationAllowanceController::class, 'search_employee'])->name('reservation_allowances.search_employee')->middleware('check.permission:view ReservationAllowance');
    Route::any('/reservation_allowances/get_search_employee/{sector_id}/{departement_id}', [ReservationAllowanceController::class, 'get_search_employee'])->name('reservation_allowances.get_search_employee')->middleware('check.permission:view ReservationAllowance');
    Route::any('/reservation_allowances/search_employee_new', [ReservationAllowanceController::class, 'search_employee_new'])->name('reservation_allowances.search_employee_new')->middleware('check.permission:view ReservationAllowance');
    Route::any('/reservation_allowances/add_reservation_allowances_employess/{type}/{id}', [ReservationAllowanceController::class, 'add_reservation_allowances_employess'])->name('reservation_allowances.add_reservation_allowances_employess')->middleware('check.permission:create ReservationAllowance');
    Route::any('/reservation_allowances/view_reservation_allowances_employess', [ReservationAllowanceController::class, 'view_reservation_allowances_employess'])->name('reservation_allowances.view_reservation_allowances_employess')->middleware('check.permission:creategit  ReservationAllowance');
    Route::any('/reservation_allowances/confirm_reservation_allowances/{date}/{sector_id}/{departement_id}', [ReservationAllowanceController::class, 'confirm_reservation_allowances'])->name('reservation_allowances.confirm_reservation_allowances')->middleware('check.permission:view ReservationAllowance');
    Route::any('/reservation_allowances/create_employee_new', [ReservationAllowanceController::class, 'create_employee_new'])->name('reservation_allowances.create_employee_new')->middleware('check.permission:create ReservationAllowance');
    Route::any('/reservation_allowances/create_employee_all', [ReservationAllowanceController::class, 'create_employee_all'])->name('reservation_allowances.create_employee_all')->middleware('check.permission:create ReservationAllowance');

    //reserv statics
 Route::group(['middleware' => ['check.permission:statistic ReservationAllowance']], function () {
    
    //statistics
    Route::get('/statistics', [statisticController::class, 'index'])->name('statistic.show');
    Route::get('/statistics/search', [statisticController::class, 'getFilteredData'])->name('statistic.search');
    //reservation statics for departments per sector

    Route::get('/statistics_department/{sector_id}', [ReservationStaticsController::class, 'static'])->name('Reserv_statistic_department.index');
    Route::get('/statistics_department/getAll/{sector_id}', [ReservationStaticsController::class, 'getAll'])->name('Reserv_statistic.getAll');
    Route::get('/all-department-employees/{departmentId}', [ReservationStaticsController::class, 'departmentEmployeesPage'])->name('all.department.employees.page');
    Route::get('/all-department-employees-data/{departmentId}', [ReservationStaticsController::class, 'getDepartmentEmployees'])->name('all.department.employees.data');
    Route::get('/department-not-received/{departmentId}', [ReservationStaticsController::class, 'notReceivedEmployeesPage'])->name('department.not_received.page');
    Route::get('/department-not-received-data/{departmentId}', [ReservationStaticsController::class, 'getNotReceivedEmployees'])->name('department.not_received.data');
    //reservation statics for supdepartmenst per department
    Route::get('/statistics_subdepartments/{department_id}', [SubDepartmentStatsController::class, 'index'])->name('statistics_subdepartments.index');
    Route::get('/statistics_subdepartments/getAll/{department_id}', [SubDepartmentStatsController::class, 'getAll'])->name('statistics_subdepartments.getAll');
    Route::get('/subdepartment-employees/{subDepartmentId}', [SubDepartmentStatsController::class, 'subDepartmentEmployeesPage'])->name('subdepartment.employees.page');
    Route::get('/subdepartment-employees-data/{subDepartmentId}', [SubDepartmentStatsController::class, 'getSubDepartmentEmployees'])->name('subdepartment.employees.data');
    Route::get('/subdepartment-not-received/{subDepartmentId}', [SubDepartmentStatsController::class, 'notReceivedEmployeesPage'])->name('subdepartment.not_received.page');
    Route::get('/subdepartment-not-received-data/{subDepartmentId}', [SubDepartmentStatsController::class, 'getNotReceivedEmployees'])->name('subdepartment.not_received.data');
    // resrvation statics per subdepartment
    Route::get('/subdepartment_statistics/{subDepartmentId}', [SubDepartmentReservationController::class, 'static'])->name('subdepartment_reservation.index');
    Route::get('/subdepartment_statistics/getAll/{subDepartmentId}', [SubDepartmentReservationController::class, 'getAll'])->name('subdepartment_reservation.getAll');
    //reservation statics per persons in selected sector
    Route::get('/sector-employees/{sectorId}', [SectorEmployeesDetailsController::class, 'index'])->name('sectorEmployees.index');
    Route::get('/sector-employees/data/{sectorId}', [SectorEmployeesDetailsController::class, 'getData'])->name('sectorEmployees.getData');
    Route::get('/sector/{sectorId}/printReport', [SectorEmployeesDetailsController::class, 'printReport'])->name('sectorEmployees.printReport');
    Route::get('/sector-employees/{sectorId}/not-reserved', [SectorEmployeesDetailsController::class, 'notReservedUsers'])->name('sector_employees.not_reserved');
    Route::get('/sector-employees/{sectorId}/not-reserved-data', [SectorEmployeesDetailsController::class, 'getNotReservedData'])->name('sector_employees.not_reserved_data');
    Route::get('/sector-users/{sectorId}', [SectorEmployeesDetailsController::class, 'sectorUsersPage'])->name('sector.users.page');
    Route::get('/sector-users-data/{sectorId}', [SectorEmployeesDetailsController::class, 'getSectorUsers'])->name('sector.users.data');
    Route::get('/employee-allowance-details/{employeeId}', [SectorEmployeesDetailsController::class, 'allowanceDetailsPage'])->name('employee.allowance.details.page');
    Route::get('/employee-allowance-details-data/{employeeId}', [SectorEmployeesDetailsController::class, 'getAllowanceDetails'])->name('employee.allowance.details.data');
    //reservation statics per persons in selected department
    Route::get('/department-employees/{department_id}', [DepartmentEmployeesDetailsController::class, 'index'])->name('department.employees');
    Route::get('/department-employees/data/{department_id}', [DepartmentEmployeesDetailsController::class, 'getData'])->name('department.employees.getData');
    Route::get('/employee-allowance-details/{employeeId}', [DepartmentEmployeesDetailsController::class, 'allowanceDetailsPage'])->name('employee.allowance.details.page');
    Route::get('/employee-allowance-details-data/{employeeId}', [DepartmentEmployeesDetailsController::class, 'getAllowanceDetails'])->name('employee.allowance.details.data');
       //reservation statics for sectors
    Route::get('/statistics_sector', [ReserveSectorController::class, 'static'])->name('Reserv_statistic_sector.index');
    Route::get('/statistics_sector/search', [ReserveSectorController::class, 'getFilteredData'])->name('Reserv_statistic_sector.search');
    Route::any('/statistics_sector/getAll', [ReserveSectorController::class, 'getAll'])->name('Reserv_statistic_sector.getAll');

});
    Route::get('/get-manager-details/{id}', [DepartmentController::class, 'getManagerDetails']);
    Route::get('/get-manager-sector-details/{id}/{sector}', [sectorsController::class, 'getManagerSectorDetails']);
    Route::get('/get-allowance-sector', [SectorsController::class, 'getAllowance']);
    Route::get('/get-allowance-department', [DepartmentController::class, 'getAllowancedepart']);
    Route::any('/employee_search/getAll', [UserController::class, 'getAll'])->name('employee_search.getAll');

    //reserv fetch
Route::group(['middleware' => ['check.permission:search ReservationAllowance']], function () {
    Route::get('/reservation_fetch', [ReserveFetchController::class, 'static'])->name('reservation_fetch.index');
    Route::get('/reservation_fetch/search', [ReserveFetchController::class, 'getFilteredData'])->name('reservation_fetch.search');
    Route::any('/reservation_fetch/getAll', [ReserveFetchController::class, 'getAll'])->name('reservation_fetch.getAll');
    Route::get('/reservation_fetch/print', [ReserveFetchController::class, 'printReport'])->name('reservation_fetch.print');
    Route::any('/reservations/last-month', [ReserveFetchController::class, 'getLastMonth'])->name('reservation_fetch.getLastMonth');
    Route::any('/reservations/last-three-months', [ReserveFetchController::class, 'getLastThreeMonths'])->name('reservation_fetch.getLastThreeMonths');
    Route::any('/reservations/last-six-months', [ReserveFetchController::class, 'getLastSixMonths'])->name('reservation_fetch.getLastSixMonths');
    Route::any('/reservations/last-year', [ReserveFetchController::class, 'getLastYear'])->name('reservation_fetch.getLastYear');
    Route::any('/reservations/other-dates', [ReserveFetchController::class, 'getCustomDateRange'])->name('reservation_fetch.getCustomDateRange');
});

    //reserv reports
Route::group(['middleware' => ['check.permission:report ReservationAllowance']], function () {

    Route::get('reservation_report', [ReservationReportController::class, 'index'])->name('reserv_report.index');
    Route::get('reservation_report/getReportData', [ReservationReportController::class, 'getReportData'])->name('reservation_report.getReportData');
    Route::get('reservation_report/print', [ReservationReportController::class, 'printReport'])->name('reservation_report.print');
    Route::get('reservation_report/sector/{sectorId}/details', [ReservationReportController::class, 'showSectorDetails'])->name('reservation_report.sector_details');
    Route::get('reservation_report/sector/{sectorId}/details_data', [ReservationReportController::class, 'getSectorDetailsData'])->name('reservation_report.sector_details_data');
    Route::get('reservation_report/sector/{sectorId}/print', [ReservationReportController::class, 'printSectorDetails'])->name('reservation_report.sector_details_print');
    Route::get('reservation_report/sector/{sectorId}/departments', [ReservationReportController::class, 'showMainDepartmentDetails'])->name('reservation_report.sector_main_departments');
    Route::get('reservation_report/sector/{sectorId}/departments/print', [ReservationReportController::class, 'printMainDepartmentDetails'])->name('reservation_report.sector_main_departments_print');
    Route::get('reservation_report/main_department/{departmentId}/sub_departments', [ReservationReportController::class, 'showSubDepartments'])->name('reservation_report.main_department_sub_departments');
    Route::get('reservation_report/main_department/{departmentId}/sub_departments/print', [ReservationReportController::class, 'printSubDepartmentsDetails'])->name('reservation_report.main_department_sub_departments_print');
    Route::get('reservation_report/main_department/{departmentId}/employees', [ReservationReportController::class, 'showMainDepartmentEmployees'])->name('reservation_report.main_department_employees');
    Route::get('reservation_report/main_department/{departmentId}/employees_data', [ReservationReportController::class, 'getMainDepartmentEmployeesData'])->name('reservation_report.main_department_employees_data');
    Route::get('reservation_report/user/{userId}/details', [ReservationReportController::class, 'showUserDetails'])->name('reservation_report.user_details');
    Route::get('reservation_report/main_department/{departmentId}/employees/print', [ReservationReportController::class, 'printMainDepartmentEmployees'])->name('reservation_report.main_department_employees_print');
    Route::get('reservation_report/sub_department/{subDepartmentId}/employees', [ReservationReportController::class, 'showSubDepartmentEmployees'])->name('reservation_report.sub_department_employees');
    Route::get('reservation_report/sub_department/{subDepartmentId}/employees/print', [ReservationReportController::class, 'printSubDepartmentEmployees'])->name('reservation_report.sub_department_employees_print');
    Route::get('/reservation_report/user/{userId}/details', [ReservationReportController::class, 'showUserDetails'])->name('reservation_report.user_details');
    Route::get('/reservation_report/user/{userId}/details_data', [ReservationReportController::class, 'getUserDetailsData'])->name('reservation_report.user_details_data');
});



    Route::get('/file-import', [UserController::class, 'importView'])->name('import-view');
    Route::post('/import', [UserController::class, 'import'])->name('import');
    Route::get('/export-users', [UserController::class, 'exportUsers'])->name('export-users');
    Route::get('print-users', [UserController::class, 'printUsers'])->name('print-users');
    Route::get('download-template', [UserController::class, 'downloadTemplate'])->name('download-template');
});




// // view All Models permission
// Route::middleware(['auth', 'check.permission:view Rule,view Permission,view departements'])->group(function () {
// });
// // create All Models permission
// Route::middleware(['auth', 'check.permission:create Permission,create Rule,create departements'])->group(function () {
// });
// // edit All Models permission
// Route::middleware(['auth', 'check.permission:edit Rule,edit Permission,edit departements'])->group(function () {

//     // Route::resource('permissions', PermissionController::class);
//     // Route::resource('rules', RuleController::class);
// });


// //permission
// Route::any('/permission_destroy',[PermissionController::class, 'destroy'])->name('permission.destroy');
// Route::any('/permission_view',[PermissionController::class, 'show'])->name('permission.view');





//role
// Route::any('/rule_destroy',[RuleController::class, 'destroy'])->name('rule.destroy');
// Route::any('/rule_view',[RuleController::class, 'show'])->name('rule.view');

// department
// Route::resource('departments', DepartmentController::class);

// Department routes


//Start Export routes


//End Export routes
//setting start
// Route::resource('setting', settingController::class);
// Route::get('setting', [settingController::class,'index'])->name('setting.index');
// Route::get('setting/all/grade', [settingController::class, 'getAllGrade'])->name('setting.getAllGrade');
// Route::get('setting/all/job', [settingController::class, 'getAllJob'])->name('setting.getAllJob');
// Route::get('setting/all/vacation', [settingController::class, 'getAllVacation'])->name('setting.getAllVacation');
// Route::get('setting/all/government', [settingController::class, 'getAllgovernment'])->name('setting.getAllgovernment');


// Route::post('jobs/add', [settingController::class,'addJob'])->name('jobs.add');
// Route::post('jobs', [settingController::class,'editJob'])->name('jobs.edit');
// Route::post('jobs/delete', [settingController::class,'deletejob'])->name('jobs.delete');


// Route::post('grade/add', [settingController::class,'addgrade'])->name('grade.add');
// Route::post('grade', [settingController::class,'editgrade'])->name('grade.edit');
// Route::post('grade/delete', [settingController::class,'deletegrade'])->name('grade.delete');


// Route::post('vacationType/add', [settingController::class,'addVacation'])->name('vacationType.add');
// Route::post('vacationType', [settingController::class,'editVacation'])->name('vacation.edit');
// Route::post('vacationType/delete', [settingController::class,'deleteVacation'])->name('vacation.delete');


// Route::post('government/add', [settingController::class,'addgovernment'])->name('government.add');
// Route::post('government', [settingController::class,'editgovernment'])->name('government.edit');
// Route::post('government/delete', [settingController::class,'deletegovernment'])->name('government.delete');
