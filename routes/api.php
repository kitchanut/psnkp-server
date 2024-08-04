<?php

use App\Models\RequestLog;
use App\Models\RequestUpdate;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::get('/login', function () {
    return response()->json(['error' => 'Unauthorized'], 401);
})->name('login');

Route::get('/updateLog', function () {
    $RequestLogs = RequestLog::where([['type', 'อัพเดทข้อมูล'], ['working_id', NULL]])->get();
    foreach ($RequestLogs as $key => $value) {
        $RequestUpdate = RequestUpdate::find($value->ref_id);
        if ($RequestUpdate) {
            $RequestLog = RequestLog::find($value->id);
            $RequestLog->working_id = $RequestUpdate->working_id;
            $RequestLog->save();
        } else {
            $RequestLog = RequestLog::find($value->id);
            $RequestLog->working_id = 0;
            $RequestLog->save();
        }
    }
    return response()->json("ok");
});

Route::group([
    'middleware' => ['api', 'auth:api'],
], function ($router) {

    // Auth
    Route::post('auth/login', 'AuthController@login')->withoutMiddleware(['auth:api']);
    Route::post('auth/register', 'AuthController@register')->withoutMiddleware(['auth:api']);
    Route::post('auth/logout', 'AuthController@logout');
    Route::post('auth/refresh', 'AuthController@refresh')->withoutMiddleware(['auth:api']);
    Route::get('me', 'AuthController@me');

    // Setting
    Route::apiResource('outlaycost', 'OutlayCostController');
    Route::apiResource('income', 'IncomeController');
    Route::apiResource('search_term', 'SearchTermController');
    Route::apiResource('low_cars', 'LowCarsController');
    Route::apiResource('add_money', 'AddMoneyController');
    Route::apiResource('promotion', 'PromotionController');
    Route::apiResource('color', 'ColorController');
    Route::apiResource('bank', 'BankController');
    Route::apiResource('bank_branch', 'BankBranchController');
    Route::apiResource('fuel', 'FuelController');
    Route::apiResource('amount_down', 'AmountDownController');
    Route::apiResource('amount_slacken', 'AmountSlackenController');
    Route::apiResource('partner_company', 'PartnerCompanyController');
    Route::apiResource('partner_car', 'PartnerCarController');
    Route::apiResource('partner_technician', 'PartnerTechnicianController');
    Route::apiResource('repair', 'RepairController');
    Route::apiResource('middle_price', 'MiddlePriceController');
    Route::apiResource('unit', 'UnitController');
    Route::apiResource('car_lift', 'CarLiftController');
    Route::apiResource('car_part_types', 'CarPartTypeController');
    Route::apiResource('car_part', 'CarPartController');
    Route::apiResource('car_type', 'CarTypeController');
    Route::apiResource('car_model', 'CarModelController');
    Route::apiResource('car_series', 'CarSeriesController');
    Route::apiResource('repair_price', 'RepairPriceController');
    Route::apiResource('car_serie_sub', 'CarSerieSubController');
    Route::apiResource('car', 'CarController');
    Route::apiResource('file_cars', 'FileCarController');
    Route::apiResource('branches', 'BranchController');
    Route::apiResource('branch_teams', 'BranchTeamController');
    Route::apiResource('user_teams', 'UserTeamController');
    Route::apiResource('user_groups', 'UserGroupController');
    Route::apiResource('users', 'UserController');

    Route::get('low_cars/{id}', 'LowCarsController@show');
    Route::delete('low_cars/{id}', 'LowCarsController@destroy');
    Route::get('showLowCars', 'LowCarsController@showLowCars');

    Route::post('showCar', 'CarController@showCar');
    Route::post('ImageCar/{id}', 'CarController@showImageCar')->withoutMiddleware(['auth:api']);
    Route::post('change_fist_img', 'CarController@change_fist_img');
    Route::post('delete_img_car', 'CarController@deleteImageCar');
    Route::post('deleteAllImgCar', 'CarController@deleteAllImgCar');
    Route::post('deleteFolder', 'CarController@deleteFolder');
    Route::get('reRollCar/{idCar}', 'CarController@reRollCar');
    Route::get('update_amountPrice', 'CarController@update_amountPrice');

    Route::post('file_car', 'FileCarController@file_car');
    Route::post('upload_file_car', 'FileCarController@upload_file_car');
    Route::post('delete_file_car', 'FileCarController@delete_file_car');
    Route::post('change_date_file_car', 'FileCarController@change_date_file_car');


    Route::apiResource('geographies', 'GeographieController');
    Route::get('selectOnGeographies', 'GeographieController@selectOnGeographies');
    Route::apiResource('provinces', 'ProvinceController');
    Route::get('selectOnProvinces', 'ProvinceController@selectOnProvinces');
    Route::apiResource('amphures', 'AmphureController');
    Route::get('selectOnAmphures', 'AmphureController@selectOnAmphures');

    Route::apiResource('districts', 'DistrictController');
    Route::get('selectOnDistricts', 'DistrictController@selectOnDistricts');

    //info car
    Route::get('getAllinfo/{car_id}/{user_group_permission}', 'CarController@getAllinfo');

    // Withdraw
    Route::apiResource('withdraw_part', 'WithdrawPartController');
    Route::post('withdrawWhere', 'WithdrawPartController@withdrawWhere');

    //Withdraw money with branch

    Route::post('outlaycost_where', 'OutlayCostController@outlaycost_where');
    Route::post('outlaycost_car', 'OutlayCostController@outlaycost_car');
    Route::post('outlaycost_getwithTime', 'OutlayCostController@outlaycost_getwithTime');

    Route::post('uploadFile_outlay', 'OutlayCostController@uploadFile_outlay');
    Route::post('delete_uploadFile_outlay', 'OutlayCostController@delete_uploadFile_outlay');
    Route::post('cancle_uploadFile_outlay', 'OutlayCostController@cancle_uploadFile_outlay');
    Route::get('delete_outlay/{id}', 'OutlayCostController@delete_outlay');
    Route::get('comfirm_outlay/{id}', 'OutlayCostController@comfirm_outlay');

    Route::post('income_where', 'IncomeController@income_where');
    Route::post('income_car', 'IncomeController@income_car');

    Route::post('uploadFile_income', 'IncomeController@uploadFile_income');
    Route::post('delete_uploadFile_income', 'IncomeController@delete_uploadFile_income');
    Route::post('cancle_uploadFile_income', 'IncomeController@cancle_uploadFile_income');
    Route::get('delete_income/{id}', 'IncomeController@delete_income');
    Route::get('comfirm_income/{id}', 'IncomeController@comfirm_income');


    // Select auto (PO)
    Route::post('selectOnBranch/{id}', 'BranchController@selectOnBranch');

    // Dropdown Select backend (system)
    Route::get('SelectOnSearch_term', 'SearchTermController@SelectOnSearch_term');
    Route::get('selectOnAmountSlacken', 'AmountSlackenController@selectOnAmountSlacken');
    Route::get('selectOnAmountDown', 'AmountDownController@selectOnAmountDown');
    Route::get('selectOnPartnerCompany', 'PartnerCompanyController@selectOnPartnerCompany');
    Route::get('selectOnPartnerCar', 'PartnerCarController@selectOnPartnerCar');
    Route::get('SelectOnBranches', 'BranchController@SelectOnBranches');
    Route::get('SelectOnBranchTeams', 'BranchTeamController@SelectOnBranchTeams');
    Route::get('SelectOnProvince', 'BranchController@getProvince');
    Route::get('SelectOnUserGroups', 'UserGroupController@SelectOnUserGroups');
    Route::get('SelectOnUserTeams', 'UserTeamController@SelectOnUserTeams');
    Route::get('SelectOnSale', 'UserController@SelectOnSale');
    Route::get('SelectOnTechnicianBuild/{branch_id}', 'UserController@SelectOnTechnicianBuild');
    Route::get('SelectOnCars', 'CarController@SelectOnCar');
    Route::get('SelectOnCarAll', 'CarController@SelectOnCarAll');
    Route::get('SelectAllCars', 'CarController@SelectAllCars');
    Route::get('SelectCarNo', 'CarController@SelectCarNo')->withoutMiddleware(['auth:api']);
    Route::get('SelectOnCarLifts', 'CarLiftController@SelectOnCarLift');
    Route::get('SelectOnCarType', 'CarTypeController@SelectOnCarType');
    Route::get('SelectOnCarModel', 'CarModelController@SelectOnCarModel');
    Route::get('SelectOnCarSeries', 'CarSeriesController@SelectOnCarSeries');
    Route::get('SelectOnCarSerieSubs', 'CarSerieSubController@SelectOnCarSerieSubs');
    Route::get('SelectOnUnit', 'UnitController@SelectOnUnit');
    Route::get('SelectOnBank', 'BankController@SelectOnBank')->withoutMiddleware(['auth:api']);
    Route::get('SelectOnBank_branch', 'BankBranchController@SelectOnBank_branch')->withoutMiddleware(['auth:api']);

    Route::get('SelectOnFuel', 'FuelController@SelectOnFuel');
    Route::get('SelectOnPartnerTech', 'PartnerTechnicianController@SelectOnPartnerTech');

    Route::get('SelectOnColor', 'ColorController@SelectOnColor');
    Route::get('SelectOnCarPartType', 'CarPartTypeController@SelectOnCarPartType');
    Route::get('SelectOnCarParts', 'CarPartController@SelectOnCarParts');
    Route::get('SelectOnRepair', 'RepairController@SelectOnRepair');

    // Working
    Route::apiResource('working', 'WorkingController')->withoutMiddleware(['auth:api']);
    Route::get('working_allData', 'WorkingController@working_allData');
    Route::post('workWhere', 'WorkingController@workWhere');
    Route::post('workWhereClose', 'WorkingController@workWhereClose');
    Route::post('selectWhereCancle', 'WorkingController@selectWhereCancle');
    Route::post('get_work_cancel', 'WorkingController@working_cancel');
    Route::post('work_cancel', 'WorkingController@work_cancel');
    Route::post('working_search_id/{id}', 'WorkingController@work_where_id');
    Route::post('CheckWorking/{name}/{tel}', 'WorkingController@CheckWorking');
    Route::post('updateStatusWorking/{workingID}/', 'WorkingController@updateStatusWorking');
    Route::post('followWork', 'WorkingController@followWork');
    Route::post('commission_month_by_team_branch', 'WorkingController@commission_month_by_team_branch');
    Route::post('updateNote/{workingID}/', 'WorkingController@updateNote');
    Route::get('followDown', 'WorkingController@followDown');
    Route::get('activeWorkingID', 'WorkingController@activeWorkingID')->withoutMiddleware(['auth:api']);




    // Booking
    Route::apiResource('booking', 'BookingController');
    Route::post('checkBooking/{idWork}/{idCar}/{idCustomer}', 'BookingController@checkBooking');
    Route::post('printBooking/{idBooking}', 'BookingController@printBooking');


    // Financial
    Route::apiResource('financial', 'FinancialController');
    Route::post('checkFinancial/{idWork}/{payment_type}', 'FinancialController@checkFinancial');
    Route::post('addFinancial/{idWork}', 'FinancialController@addFinancial');
    Route::post('editFinancial/{idWork}/{payment_type}', 'FinancialController@editFinancial');
    Route::post('printFinancial/{idFinancial}', 'FinancialController@printFinancial');
    Route::post('allFinancialonWork/{idWork}', 'FinancialController@allFinancialonWork');
    Route::post('financial/indexTime', 'FinancialController@indexTime');

    // Negotiation
    Route::apiResource('negotiations', 'NegotiationController');

    // Contract
    Route::apiResource('contract', 'ContractController');
    Route::post('checkContract/{idWork}/{idCar}/{idCustomer}', 'ContractController@checkContract');
    Route::post('printContract/{idContract}', 'ContractController@printContract');


    // InsurCertificate
    Route::apiResource('insurcertificate', 'InsuranceCertificateController');
    Route::post('checkInsurCertificate/{idWork}/{jobType}', 'InsuranceCertificateController@checkInsurCertificate');
    Route::post('printInsurCertificate/{idInsu}', 'InsuranceCertificateController@printInsurCertificate');


    //Job Technician
    Route::apiResource('technician', 'JobTechnicianController');
    Route::post('JobTechnicianWhere', 'JobTechnicianController@JobTechnicianWhere');
    Route::post('checkTechnician/{idWork}/{idCar}', 'JobTechnicianController@checkTechnician');
    Route::post('SelectOnJob/{idJob}', 'JobTechnicianController@SelectOnJob');
    Route::post('updateOnJob/{idJob}/{job_status}', 'JobTechnicianController@updateOnJob');
    Route::post('JobTechnicianWhereCar/{idCar}', 'JobTechnicianController@JobTechnicianWhereCar');
    Route::post('RepairPrice/{idCar}', 'RepairPriceController@RepairPrice');

    //Job Technician out
    Route::apiResource('job_pathner_technician', 'JobTechnicianPathnerController');
    Route::post('PathnerJobTechnicianWhere', 'JobTechnicianPathnerController@PathnerJobTechnicianWhere');
    Route::post('JobTechnicianWhereCar_out/{idCar}', 'JobTechnicianPathnerController@JobTechnicianWhereCar_out');


    //ReceivingMoney
    Route::apiResource('receiving_money', 'ReceivingMoneyController');
    Route::post('checkReceivingMoney/{idWork}/{car_no}/{receivingMoney_type}', 'ReceivingMoneyController@check_receiving_money');
    Route::post('addReceivingMoneyWithCarNo/{car_no}/{receivingMoney_type}', 'ReceivingMoneyController@addReceivingMoneyWithCarNo');
    Route::post('commission', 'ReceivingMoneyController@commission');
    Route::post('printReceivingMoney/{id}', 'ReceivingMoneyController@printReceivingMoney');
    Route::get('receiving_money/getWithWorkID/{work_id}', 'ReceivingMoneyController@getWithWorkID');
    Route::get('receiving_money/getDataWithCarNo/{car_no}', 'ReceivingMoneyController@getDataWithCarNo');

    //Appointment
    Route::apiResource('appointment', 'AppointmentController');
    Route::post('checkAppointment/{idWork}/', 'AppointmentController@checkAppointment');

    //Pre Approve
    Route::get('pre_approves/checkPreApprove/{working_id}', 'PreApproveController@checkPreApprove');
    Route::apiResource('pre_approves', 'PreApproveController');

    //Appointment Bank
    Route::apiResource('appointment_bank', 'AppointmentBankController');
    Route::post('checkAppointmentBank/{idWork}/', 'AppointmentBankController@checkAppointmentBank');


    // Purchases
    Route::apiResource('purchase', 'PurchaseController');
    Route::post('purchase_where/{po_number}', 'PurchaseController@where_po_number');

    // Customer
    Route::apiResource('customer', 'CustomerController');
    Route::apiResource('customer_detail', 'CustomerDetailController');
    Route::apiResource('customer_visit', 'CustomerVisitController');
    Route::post('customerVisitWhere', 'CustomerVisitController@customerVisitWhere');


    // Stock
    Route::apiResource('stock_part', 'StockPartController');
    Route::post('StockOnPart/{id}', 'StockPartController@StockOnPart');
    Route::post('StockOnCar', 'CarController@StockOnCar')->withoutMiddleware(['auth:api']);



    //Upload image
    Route::post('uploadImgCars', 'CarController@uploadImgCars');



    // Dropdown Select by id backend (system)
    Route::post('SelectOnCarSerie/{car_types_id}/{car_models_id}', 'CarSeriesController@SelectOnCarSerie');
    Route::post('SelectOnCarSerieOnly/{car_models_id}', 'CarSeriesController@SelectOnCarSerieOnly');
    Route::post('SelectOnCarSerieSub/{id}', 'CarSerieSubController@SelectOnCarSerieSub');
    Route::post('SelectCustomer/{id}', 'CustomerController@SelectCustomer');
    Route::post('SelectCustomerDetail/{id}', 'CustomerDetailController@SelectCustomerDetail');
    Route::post('SelectOnCar/{id}', 'CarController@SelectDetailCar');

    // Report
    Route::post('get_report_addmoney', 'AddMoneyController@get_report_addmoney');
    Route::post('report_withdraw_money', 'OutlayCostController@report_withdraw_money');
    Route::post('report_income', 'IncomeController@report_income');

    Route::post('transition_working', 'TransitionWorkingController@index');
    Route::post('working_where/{idWork}', 'TransitionWorkingController@working_where_id');
    Route::post('transition_purchase', 'TransitionPurchaseController@index');
    Route::post('po_number_where/{idPO}', 'TransitionPurchaseController@where_id');
    Route::post('transition_cars', 'TransitionCarController@index');
    Route::post('car_regis/{branch_id}', 'TransitionCarController@car_regis');
    Route::post('transition_car_where/{idCar}', 'TransitionCarController@where_car');
    Route::post('transition_stock_parts', 'TransitionStockPartController@index');
    Route::post('transition_stock_parts_where/{idPart}', 'TransitionStockPartController@where_car_part');
    // Route::post('transition_withdraw_parts', 'TransitionWithdrawPartController@index');
    Route::apiResource('transition_withdraw_parts', 'TransitionWithdrawPartController');
    Route::post('transition_withdraw_parts_where/{idPart}', 'TransitionWithdrawPartController@where_car_part');
    Route::apiResource('transition_repair', 'TransitionJobtechnicianController');
    Route::post('transition_repair_where/{idJob}', 'TransitionJobtechnicianController@where_job');

    // Report
    Route::post('report_commission', 'ReportController@report_commission');
    Route::post('booking_car', 'ReportController@booking_car');
    Route::post('report_purchase_car', 'ReportController@report_purchase_car');
    Route::post('report_sale_car', 'ReportController@report_sale_car');
    Route::post('report_work_cancle', 'ReportController@report_work_cancle');
    Route::post('report_booking_duplicate', 'ReportController@report_booking_duplicate');
    Route::post('report_profit', 'ReportController@report_profit');



    //Dashboard
    Route::post('dashboard_saleByBranch', 'DashboardController@dashboard_saleByBranch');
    Route::post('dashboard_manager_doughnut_stock', 'DashboardController@dashboard_manager_doughnut_stock');
    // Route::post('dashboard_manager_pie', 'DashboardController@dashboard_manager_pie');
    Route::post('dashboard_manager_bar_visit', 'DashboardController@dashboard_manager_bar_visit');
    Route::post('dashboard_manager_bar_visit_car_type', 'DashboardController@dashboard_manager_bar_visit_car_type');
    Route::post('dashboard_manager_bar_visit_car_model', 'DashboardController@dashboard_manager_bar_visit_car_model');


    Route::post('dashboard_manager_bar_top_car_serie', 'DashboardController@dashboard_manager_bar_top_car_serie');

    Route::post('dashboard_manager_bar_visit_car_serie', 'DashboardController@dashboard_manager_bar_visit_car_serie');
    Route::post('dashboard_manager_bar_visit_car_slacken', 'DashboardController@dashboard_manager_bar_visit_car_slacken');
    Route::post('dashboard_manager_bar_visit_car_down', 'DashboardController@dashboard_manager_bar_visit_car_down');
    Route::post('dashboard_manager_bar_car', 'DashboardController@dashboard_manager_bar_car');
    Route::post('dashboard_sale_bar', 'DashboardController@dashboard_sale_bar');
    Route::post('dashboard_sale_doughnut', 'DashboardController@dashboard_sale_doughnut');

    Route::get('dashboard_inventory_car', 'DashboardController@dashboard_inventory_car');
    Route::get('dashboard_car_registration', 'DashboardController@dashboard_car_registration');
    Route::get('dashboard_car_insurances', 'DashboardController@dashboard_car_insurances');



    // Dropdown Select in shop
    Route::get('SelectOnBranchesShop/{province_id}', 'BranchController@SelectOnBranches_where')->withoutMiddleware(['auth:api']);
    Route::get('SelectOnCarTypeShop', 'CarTypeController@SelectOnCarType')->withoutMiddleware(['auth:api']);
    Route::get('SelectOnCarModelShop', 'CarModelController@SelectOnCarModel')->withoutMiddleware(['auth:api']);
    Route::get('SelectOnCarSeriesShop', 'CarSeriesController@SelectOnCarSeries')->withoutMiddleware(['auth:api']);
    Route::get('SelectOnCarSeriesSubShop', 'CarSerieSubController@SelectOnCarSerieSubs')->withoutMiddleware(['auth:api']);
    Route::get('selectOnAmountDownShop', 'AmountDownController@selectOnAmountDown')->withoutMiddleware(['auth:api']);
    Route::get('selectOnAmountSlackenShop', 'AmountSlackenController@selectOnAmountSlacken')->withoutMiddleware(['auth:api']);


    // Car in shop
    Route::post('showSearchCars', 'CarController@showSearchCars')->withoutMiddleware(['auth:api']);
    Route::get('showLastCars', 'CarController@showLastCars')->withoutMiddleware(['auth:api']);
    Route::get('showAllCars', 'CarController@showAllCars')->withoutMiddleware(['auth:api']);
    Route::get('showDownCars', 'CarController@showDownCars')->withoutMiddleware(['auth:api']);
    Route::get('showDownSlackens', 'CarController@showDownSlackens')->withoutMiddleware(['auth:api']);
    Route::post('showForYouCars/{id}', 'CarController@showForYouCars')->withoutMiddleware(['auth:api']);

    Route::get('getImagePromotion', 'PromotionController@getImagePromotion')->withoutMiddleware(['auth:api']);
    Route::get('getIconType', 'CarTypeController@SelectOnCarType')->withoutMiddleware(['auth:api']);
    Route::get('getImageModel', 'CarModelController@getImageModel')->withoutMiddleware(['auth:api']);
    Route::get('getProvince', 'BranchController@getProvince')->withoutMiddleware(['auth:api']);
    Route::post('infoCar/{id}', 'CarController@infoCar')->withoutMiddleware(['auth:api']);

    Route::get('insurances/getByCar/{car_id}', 'InsuranceController@getByCar');
    Route::apiResource('insurances', 'InsuranceController');

    Route::get('installments/getByWorkingID/{working_id}', 'InstallmentController@getByWorkingID');
    Route::get('installments/getByUser/{user_group_permission}/{user_id}/{branch_id}', 'InstallmentController@getByUser');
    Route::apiResource('installments', 'InstallmentController');

    Route::get('installment_payments/getByInstallmentID/{installment_id}', 'InstallmentPaymentController@getByInstallmentID');
    Route::apiResource('installment_payments', 'InstallmentPaymentController');


    // Reqest
    Route::apiResource('user_line', 'UserLineController');

    Route::post('request_booking/countData', 'RequestBookingController@countData');
    Route::post('request_booking/indexCustom', 'RequestBookingController@indexCustom');
    Route::delete('request_booking/cancle/{id}', 'RequestBookingController@cancle');
    Route::apiResource('request_booking', 'RequestBookingController')->withoutMiddleware(['auth:api']);

    Route::post('request_appointment/countData', 'RequestAppointmentController@countData');
    Route::post('request_appointment/indexCustom', 'RequestAppointmentController@indexCustom');
    Route::delete('request_appointment/cancle/{id}', 'RequestAppointmentController@cancle');
    Route::apiResource('request_appointment', 'RequestAppointmentController')->withoutMiddleware(['auth:api']);

    Route::post('request_sign_deposit/countData', 'RequestSignDepositController@countData');
    Route::post('request_sign_deposit/indexCustom', 'RequestSignDepositController@indexCustom');
    Route::delete('request_sign_deposit/cancle/{id}', 'RequestSignDepositController@cancle');
    Route::apiResource('request_sign_deposit', 'RequestSignDepositController')->withoutMiddleware(['auth:api']);

    Route::post('request_sign/countData', 'RequestSignController@countData');
    Route::post('request_sign/indexCustom', 'RequestSignController@indexCustom');
    Route::delete('request_sign/cancle/{id}', 'RequestSignController@cancle');
    Route::apiResource('request_sign', 'RequestSignController')->withoutMiddleware(['auth:api']);

    Route::post('request_bankApprove/countData', 'RequestBankApproveController@countData');
    Route::post('request_bankApprove/indexCustom', 'RequestBankApproveController@indexCustom');
    Route::delete('request_bankApprove/cancle/{id}', 'RequestBankApproveController@cancle');
    Route::apiResource('request_bankApprove', 'RequestBankApproveController')->withoutMiddleware(['auth:api']);

    Route::post('request_release/countData', 'RequestReleaseController@countData');
    Route::post('request_release/indexCustom', 'RequestReleaseController@indexCustom');
    Route::delete('request_release/cancle/{id}', 'RequestReleaseController@cancle');
    Route::apiResource('request_release', 'RequestReleaseController')->withoutMiddleware(['auth:api']);

    Route::post('request_changeCar/countData', 'RequestChangeCarController@countData');
    Route::post('request_changeCar/indexCustom', 'RequestChangeCarController@indexCustom');
    Route::delete('request_changeCar/cancle/{id}', 'RequestChangeCarController@cancle');
    Route::apiResource('request_changeCar', 'RequestChangeCarController')->withoutMiddleware(['auth:api']);

    Route::post('request_change_customer/countData', 'RequestChangeCustomerController@countData');
    Route::post('request_change_customer/indexCustom', 'RequestChangeCustomerController@indexCustom');
    Route::delete('request_change_customer/cancle/{id}', 'RequestChangeCustomerController@cancle');
    Route::apiResource('request_change_customer', 'RequestChangeCustomerController')->withoutMiddleware(['auth:api']);

    Route::post('request_update/countData', 'RequestUpdateController@countData');
    Route::post('request_update/indexCustom', 'RequestUpdateController@indexCustom');
    Route::delete('request_update/cancle/{id}', 'RequestUpdateController@cancle');
    Route::apiResource('request_update', 'RequestUpdateController')->withoutMiddleware(['auth:api']);

    Route::post('request_cancle/countData', 'RequestCancleController@countData');
    Route::post('request_cancle/indexCustom', 'RequestCancleController@indexCustom');
    Route::delete('request_cancle/cancle/{id}', 'RequestCancleController@cancle');
    Route::apiResource('request_cancle', 'RequestCancleController')->withoutMiddleware(['auth:api']);

    Route::post('request_money/countData', 'RequestMoneyController@countData');
    Route::post('request_money/indexCustom', 'RequestMoneyController@indexCustom');
    Route::delete('request_money/cancle/{id}', 'RequestMoneyController@cancle');
    Route::apiResource('request_money', 'RequestMoneyController')->withoutMiddleware(['auth:api']);

    Route::post('request_moneyWithdraw/countData', 'RequestMoneyWithdrawController@countData');
    Route::post('request_moneyWithdraw/indexCustom', 'RequestMoneyWithdrawController@indexCustom');
    Route::delete('request_moneyWithdraw/cancle/{id}', 'RequestMoneyWithdrawController@cancle');
    Route::apiResource('request_moneyWithdraw', 'RequestMoneyWithdrawController')->withoutMiddleware(['auth:api']);

    Route::post('request_log/countData', 'RequestLogController@countData');
    Route::post('request_log/indexCustom', 'RequestLogController@indexCustom');
    Route::delete('request_log/cancle/{id}', 'RequestLogController@cancle');
    Route::apiResource('request_log', 'RequestLogController')->withoutMiddleware(['auth:api']);

    //BOT
    Route::post('linebot', 'LinebotController@store')->withoutMiddleware(['auth:api']);
    Route::post('liff/check_register', 'LinebotController@check_register')->withoutMiddleware(['auth:api']);

    Route::post('price_record/update/{id}', 'PriceRecordController@update');
    Route::apiResource('price_record', 'PriceRecordController');

    Route::apiResource('uploads', 'UploadController');

    Route::get('getTest', function () {
        return response()->json("ok1");
    })->withoutMiddleware(['auth:api']);
});
