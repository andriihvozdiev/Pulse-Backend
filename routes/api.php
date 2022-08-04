 <?php

use Illuminate\Http\Request;

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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::group(['prefix' => 'auth'], function(){
	Route::post('/login', 'Auth\LoginController@login');
	Route::post('/loginwithuserid', 'Auth\LoginController@loginWithUserID');
	Route::post('/signup', 'Auth\RegisterController@signup');
	Route::post('/changepassword', 'Auth\ResetPasswordController@changePassword');
	Route::post('/forgotpassword', 'Auth\ForgotPasswordController@forgotPassword');
	Route::post('/resetpassword', 'Auth\ResetPasswordController@resetPassword');	
});


// Get common data
Route::group(['prefix' => 'base'], function(){
	Route::post('/getpersonnel', 'Common\PersonnelController@getPersonnel');
	Route::post('/getallleaseroutes', 'Common\LeaseRoutesController@getAllLeaseRoutes');
	Route::post('/getalllistcompanies', 'Common\ListCompanyController@getAllListCompanies');
	Route::post('/getallscheduletypes', 'Common\ScheduleTypeController@getAllScheduleTypes');
	Route::post('/getlistmeterproblem', 'Common\ListMeterProblemController@getListMeterProblem');
	Route::post('/getlistwellproblem', 'Common\ListWellProblemController@getListWellProblem');
});


// Productions data
Route::group(['prefix' => 'production'], function(){
	Route::post('/getalllease', 'Production\ProductionHomeController@getAllLease');
	Route::post('/getprodfields', 'Production\ProductionHomeController@getPulseProdField');
	Route::post('/getproductions', 'Production\ProductionHomeController@getProductions');
	Route::post('/getproductionfields', 'Production\ProductionHomeController@getProductionFields');
	Route::post('/getproductionavg', 'Production\ProductionAvgController@getProductionAvg');
	Route::post('/getproductionavgfield', 'Production\ProductionAvgController@getProductionAvgField');
	Route::post('/getleaseswithpermission', 'Production\ListAssetLocationsController@getLeasesWithPermission');
	
	Route::group(['prefix' => 'tanks'], function(){
		Route::post('/gettanks', 'Production\TankDataController@getTanks');
		Route::post('/gettankgaugeentry', 'Production\TankDataController@getTankGaugeEntry');
		Route::post('/getruntickets', 'Production\RunTicketsController@getRunTickets');
		Route::post('/uploadruntickets', 'Production\RunTicketsController@uploadRunTickets');

		Route::post('/gettankstrappings', 'Production\TankDataController@getTankStrappings');
		Route::post('/uploadtankgaugeentries', 'Production\TankDataController@uploadTankGaugeEntries');
	});

	Route::group(['prefix' => 'meters'], function(){
		Route::post('/getmeters', 'Production\MetersController@getAllMeters');
		Route::post('/getgasmeterdata', 'Production\MeterDataController@getGasMeterData');
		Route::post('/getwatermeterdata', 'Production\MeterDataController@getWaterMeterData');

		Route::post('/uploadgasmeterdata', 'Production\MeterDataController@uploadGasMeterData');
		Route::post('/uploadwatermeterdata', 'Production\MeterDataController@uploadWaterMeterData');
	});

	Route::group(['prefix' => 'wells'], function(){
		Route::post('/getwelllist', 'Production\WellListController@getWellList');
		Route::post('/getwellheaddata', 'Production\WellheadDataController@getWellheadData');
		Route::post('/uploadwellheaddata', 'Production\WellheadDataController@uploadWellheadData');
	});

	Route::group(['prefix' => 'WBD'], function(){

		Route::post('/getcasingtubing', 'Production\WBD\WBDCasingTubingController@getWBDCasingTubing');
		Route::post('/getsurveys', 'Production\WBD\WBDSurveysController@getWBDSurveys');
		Route::post('/getplugs', 'Production\WBD\WBDPlugsController@getWBDPlugs');
		Route::post('/getrods', 'Production\WBD\WBDRodsController@getWBDRods');
		Route::post('/getpumps', 'Production\WBD\WBDPumpsController@getWBDPumps');
		Route::post('/gettreatments', 'Production\WBD\WBDTreatmentsController@getWBDTreatments');
		Route::post('/getperfs', 'Production\WBD\WBDPerforationController@getWBDPerfs');
		Route::post('/getinfo', 'Production\WBD\WBDInfoController@getWBDInfo');
		Route::post('/getinfosource', 'Production\WBD\WBDInfoSourceController@getWBDInfoSource');
	});
});


// Operations data
Route::group(['prefix' => 'operations'], function(){
	Route::group(['prefix' => 'invoices'], function(){
		Route::post('/getinvoices', 'Operations\InvoicesController@getAllInvoices');
		Route::post('/getspecificinvoices', 'Operations\InvoicesController@getSpecificInvoices');
		Route::post('/getinvoiceaccounts', 'Operations\InvoiceAccountController@getInvoiceAccounts');

		Route::post('/uploadinvoices', 'Operations\InvoicesController@uploadInvoices');
		Route::post('/uploadinvoicesdetail', 'Operations\InvoicesDetailController@uploadInvoicesDetail');
		Route::post('/uploadinvoicespersonnel', 'Operations\InvoicesPersonnelController@uploadInvoicesPersonnel');
	});

	// RigReports data
	Route::group(['prefix' => 'rigreports'], function(){
		Route::post('/getrigreports', 'Operations\RigReportsController@getRigReports');
		Route::post('/getrigreportswithwelllist', 'Operations\RigReportsController@getRigReportsWithWellList');
		Route::post('/getrigreportsrods', 'Operations\RigReportsRodsController@getRigReportsRods');
		Route::post('/getrodlength', 'Operations\RigReportsRodsController@getRodLength');
		Route::post('/getrodtype', 'Operations\RigReportsRodsController@getRodType');
		Route::post('/getrodsize', 'Operations\RigReportsRodsController@getRodSize');

		Route::post('/getrigreportspump', 'Operations\RigReportsPumpController@getRigReportsPump');
		Route::post('/getpumptype', 'Operations\RigReportsPumpController@getPumpType');
		Route::post('/getpumpsize', 'Operations\RigReportsPumpController@getPumpSize');

		Route::post('/getrigreportstubing', 'Operations\RigReportsTubingController@getRigReportsTubing');
		Route::post('/gettubingsize', 'Operations\RigReportsTubingController@getTubingSize');
		Route::post('/gettubingtype', 'Operations\RigReportsTubingController@getTubingType');
		Route::post('/gettubinglength', 'Operations\RigReportsTubingController@getTubingLength');

		Route::post('/uploadrigreports', 'Operations\RigReportsController@uploadRigReports');
		Route::post('/uploadrigreportsrods', 'Operations\RigReportsRodsController@uploadRigReportsRods');
		Route::post('/uploadrigreportspump', 'Operations\RigReportsPumpController@uploadRigReportsPump');
		Route::post('/uploadrigreportstubing', 'Operations\RigReportsTubingController@uploadRigReportsTubing');
	});

 	Route::group(['prefix' => 'schedules'], function(){
 		Route::post('/getschedules', 'Operations\ScheduleController@getAllSchedules');
		Route::post('/uploadschedules', 'Operations\ScheduleController@uploadSchedules');
 	});
});


// Toolbox
Route::group(['prefix' => 'toolbox'], function(){
	Route::post('/getwaterpumpinfo', 'Toolbox\WaterPumpInfoController@getWaterPumpInfo');
});


