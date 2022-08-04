<?php

namespace App\Http\Controllers\Production;

use DB;
use App\Quotation;

use App\Http\Controllers\Controller;
use App\Services\TokenService;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

use App\Models\Production;
use App\Models\PulseProdHome;
use App\Models\PulseProdField;
use App\Models\ProductionField;
use App\Models\ListCompany;
use App\Models\LeaseRoutes;


class ProductionHomeController extends Controller
{
	 
	protected function validator(Request $request)
	{
		return Validator::make($request->all(), [
			'userid' => 'required',
			'routes' => 'required',
			'operatings' => 'required',
			'isemptypermission' => 'required'
		]);
	}

	/**
	 * Get all Lease from tblPulseProdHome table.
	 *
	 * @return json response.
	 */
	public function getAllLease(Request $request)
	{
		$data = array();
		$responsedata = array();
		$status = 200;

		$sort_validator = $this->validator($request);
		if ($sort_validator->fails()) {
			$status = 400;
			$data = array(
				'status' => 400,
				'success' => false,
				'message' => 'Wrong params',
				'data' => $sort_validator->errors()
			);
			return response(json_encode($data), $status);
		}

		$userid = $request->userid;
		$routes = $request->routes;
		$operatings = $request->operatings;
		$owns = $request->owns;
		$isemptypermission = $request->isemptypermission;
			
		// check access token
		$token = '';
		$headers = apache_request_headers();
		foreach ($headers as $header => $value) {
			if (strcasecmp($header, 'accesstoken') == 0) {
				$token = $value;                
			}
		}

		if (!(TokenService::validateToken($token, $userid))) {
			$status = 401;
			$data = array(
				'status' => 401,
				'success' => false,
				'message' => 'Unathorized'
			);
			return response(json_encode($data), $status);
		}

		try {
			$pulseProdHomes = array();

			if ($isemptypermission == "true") {
				$pulseProdHomes = PulseProdHome::orderBy('LeaseName', 'ASC')->get(); 
			} else {
				$pulseProdHomes = PulseProdHome::join('ListAssetLocations', 'tblPulseProdHome.Lease', '=', 'ListAssetLocations.GrandparentPropNum')
					->where(function($query) use($routes, $operatings, $owns){
						$query->whereIn('ListAssetLocations.Route', $routes)
								->whereIn('ListAssetLocations.OpCompany', $operatings)           
								->whereIn('ListAssetLocations.OwnCompany', $owns);
					})->orderBy('LeaseName', 'ASC')
					->select('tblPulseProdHome.*')
					->get();
			}


			foreach($pulseProdHomes as $production){
				$alldata = array();

				$alldata['prodID'] =  $production->ProdID;
				$alldata['lease'] = $production->Lease;
				$alldata['leaseName'] = $production->LeaseName;
				// $alldata['date'] = $production->DateCom;
				$alldata['date'] = $production->DateAct == null ? null : Carbon::parse($production->DateAct)->format('m/d/Y H:i:s');
				$alldata['oilVol'] = $production->OilVol;
				$alldata['gasVol'] = $production->GasVol;
				$alldata['waterVol'] = $production->WaterVol;
				$alldata['allocatedVolume'] = $production->AllocatedVolume;
				$alldata['route'] = $production->Route;
				$alldata['operator'] = $production->Operator;
				$alldata['owner'] = $production->Owner;
				$alldata['oilComments'] = $production->OilComments;
				$alldata['gasComments'] = $production->GasComments;
				$alldata['waterComments'] = $production->WaterComments;
				$alldata['wellheadComments'] = $production->WellheadComments;
				$alldata['wellheadData'] = $production->WellheadData;

				$responsedata[] = $alldata;
			}

			$data = array(
				'status' => 200,
				'success' => true,
				'message' => 'Get all Lease',
				'data' => $responsedata,
			);

		} catch (ModelNotFoundException $e) {
			$status = 400;
			$data = array(
				'status' => 400,
				'success' => false,
				'message' => 'Invalid Arguments.'
			);
		}

		return response(json_encode($data), $status);
	}

	/**
	 * Get PulseProdField from tblPulseProdField
	 * 
	 */
	public function getPulseProdField(Request $request) 
	{
		$data = array();
		$responsedata = array();
		$status = 200;

		$userid = $request->userid;
			
		// check access token
		$token = '';
		$headers = apache_request_headers();
		foreach ($headers as $header => $value) {
			if (strcasecmp($header, 'accesstoken') == 0) {
				$token = $value;                
			}
		}

		if (!(TokenService::validateToken($token, $userid))) {
			$status = 401;
			$data = array(
				'status' => 401,
				'success' => false,
				'message' => 'Unathorized'
			);
			return response(json_encode($data), $status);
		}

		try {
			$pulseProdFields = array();

			$pulseProdFields = PulseProdField::orderBy('LeaseName', 'ASC')->get(); 
			
			foreach($pulseProdFields as $field){
				$alldata = array();

				$alldata['prodID'] =  $field->ProdID;
				$alldata['lease'] = $field->Lease;
				$alldata['leaseName'] = $field->LeaseName;
				$alldata['fieldName'] = $field->FieldName;
				$alldata['oilVol'] = $field->OilVol;
				$alldata['oilCalcType'] = $field->OilCalcType;
				$alldata['gasVol'] = $field->GasVol;
				$alldata['gasCalcType'] = $field->GasCalcType;
				$alldata['waterVol'] = $field->WaterVol;
				$alldata['waterCalcType'] = $field->WaterCalcType;

				$responsedata[] = $alldata;
			}

			$data = array(
				'status' => 200,
				'success' => true,
				'message' => 'Get all Prod Fields',
				'data' => $responsedata,
			);

		} catch (ModelNotFoundException $e) {
			$status = 400;
			$data = array(
				'status' => 400,
				'success' => false,
				'message' => 'Invalid Arguments.'
			);
		}

		return response(json_encode($data), $status);
	}


	/**
	 * Get Productions from tblProduction.
	 *
	 * @return json response.
	 */
	public function getProductions(Request $request)
	{
		$data = array();
		$responsedata = array();
		$totalCount = 0;
		$status = 200;

		$userid = $request->userid;
		
		$token = '';
		$daysToSync = 7;
		$headers = apache_request_headers();
		foreach ($headers as $header => $value) {
				
			if (strcasecmp($header, 'accesstoken') == 0) {
				$token = $value;                
			}
			if (strcasecmp($header, 'DaysToSync') == 0) {
				$daysToSync = $value;
			}
		}

		if (!(TokenService::validateToken($token, $userid))) {
			$status = 401;
			$data = array(
				'status' => 401,
				'success' => false,
				'message' => 'Unathorized'
			);
			return response(json_encode($data), $status);
		}

		try {
			$sqlProductions = "SELECT * FROM tblProduction WHERE ProductionDate >= DATEADD(day, -" . $daysToSync . ", GETDATE())";

			$sqlLastProductions = "SELECT * FROM tblProduction n WHERE ProductionDate = (SELECT MAX(ProductionDate) as ProductionDate FROM tblProduction GROUP BY Lease HAVING Lease=n.Lease)";

			$sql = $sqlProductions . " UNION " . $sqlLastProductions . " ORDER BY ProductionDate DESC";
			$productions = DB::select($sql);
						
			foreach($productions as $production){
					
				$alldata = array();

				$alldata['prodID'] =  $production->ProdID;
				$alldata['lease'] = $production->Lease;
				$alldata['leaseName'] = $production->LeaseName;
				$alldata['productionDate'] = $production->ProductionDate == null ? null : Carbon::parse($production->ProductionDate)->format('m/d/Y H:i:s');
				$alldata['oilVol'] = $production->OilVolume;
				$alldata['gasVol'] = $production->GasVolume;
				$alldata['waterVol'] = $production->WaterVolume;
				$alldata['allocatedVolume'] = $production->AllocatedVolume;
				$alldata['oilComments'] = $production->OilComments;
				$alldata['gasComments'] = $production->GasComments;
				$alldata['waterComments'] = $production->WaterComments;
				$alldata['wellheadComments'] = $production->WellheadComments;
				$alldata['comments'] = $production->Comments;
				$alldata['wellheadData'] = $production->WellheadData;

				$responsedata[] = $alldata;            
			}
			
			$data = array(
				'status' => 200,
				'success' => true,
				'message' => 'Get all Productions',
				'data' => $responsedata,
			);    
				
		} catch (ModelNotFoundException $e) {
			error_log($e);
			$status = 400;
			$data = array(
				'status' => 400,
				'success' => false,
				'message' => 'Invalid Arguments.'
			);
		}

		return response(json_encode($data), $status);
	}

	/**
	 * Get ProductionField from tblProductionField
	 * 
	 */
	public function getProductionFields(Request $request)
	{
		$data = array();
		$responsedata = array();
		$status = 200;

		$userid = $request->userid;
			
		// check access token
		$token = '';
		$daysToSync = 7;
		$headers = apache_request_headers();
		foreach ($headers as $header => $value) {
			if (strcasecmp($header, 'accesstoken') == 0) {
				$token = $value;                
			}
			if (strcasecmp($header, 'DaysToSync') == 0) {
                $daysToSync = $value;
            }
		}

		if (!(TokenService::validateToken($token, $userid))) {
			$status = 401;
			$data = array(
				'status' => 401,
				'success' => false,
				'message' => 'Unathorized'
			);
			return response(json_encode($data), $status);
		}

		try {
			$sqlProductionFields = "SELECT * FROM tblProductionField WHERE ProductionDate >= DATEADD(day, -" . $daysToSync . ", GETDATE())";
            
            $sqlLast = "SELECT * FROM tblProductionField n WHERE ProductionDate = (SELECT MAX(ProductionDate) as ProductionDate FROM tblProductionField GROUP BY Lease HAVING Lease=n.Lease)";
            $sql = $sqlProductionFields . " UNION " . $sqlLast . " ORDER BY ProductionDate DESC";
            $allProductionFields = DB::select($sql);
			
			foreach($allProductionFields as $field){
				$alldata = array();

				$alldata['prodID'] =  $field->ProdID;
				$alldata['lease'] = $field->Lease;
				$alldata['leaseName'] = $field->LeaseName;
				$alldata['leaseField'] = $field->LeaseField;
				$alldata['oilVol'] = $field->OilVolume;
				$alldata['gasVol'] = $field->GasVolume;
				$alldata['waterVol'] = $field->WaterVolume;
				$alldata['productionDate'] = $field->ProductionDate == null ? null : Carbon::parse($field->ProductionDate)->format('m/d/Y H:i:s');
				$alldata['oilComments'] = $field->OilComments;
				$alldata['gasComments'] = $field->GasComments;
				$alldata['waterComments'] = $field->WaterComments;
				$alldata['wellheadComments'] = $field->WellheadComments;
				$alldata['comments'] = $field->Comments;
				$alldata['wellheadData'] = $field->WellheadData;
				
				$responsedata[] = $alldata;
			}

			$data = array(
				'status' => 200,
				'success' => true,
				'message' => 'Get all Production Fields',
				'data' => $responsedata,
			);

		} catch (ModelNotFoundException $e) {
			$status = 400;
			$data = array(
				'status' => 400,
				'success' => false,
				'message' => 'Invalid Arguments.'
			);
		}

		return response(json_encode($data), $status);
	}

}
