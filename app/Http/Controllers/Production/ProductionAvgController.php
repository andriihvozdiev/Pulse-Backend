<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Services\TokenService;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ProductionAvg;
use App\Models\ProductionAvgField;
use Carbon\Carbon;

class ProductionAvgController extends Controller
{
    /**
     * Get Production Avg from tblProductinAvg table.
     *
     * @return json response.
     */
    public function getProductionAvg(Request $request)
    {
        $data = array();
        $responsedata = array();
        $status = 200;

        $userid = $request->userid;
        
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
            $arrProductionAvg = ProductionAvg::get();

            foreach ($arrProductionAvg as $productionAvg) {
                $alldata = array();

                $alldata['prodID'] =  $productionAvg->ProdID;
                $alldata['lease'] = $productionAvg->Lease;
                $alldata['leaseName'] = $productionAvg->LeaseName;
                $alldata['productionDate'] = $productionAvg->ProductionDate == null ? null : Carbon::parse($productionAvg->ProductionDate)->format('m/d/Y H:i:s');
                $alldata['allocatedVolume'] = $productionAvg->AllocatedVolume;
                $alldata['oil7'] = $productionAvg->Oil7;
                $alldata['oil30'] = $productionAvg->Oil30;
                $alldata['oil365'] = $productionAvg->Oil365;
                $alldata['gas7'] = $productionAvg->Gas7;
                $alldata['gas30'] = $productionAvg->Gas30;
                $alldata['gas365'] = $productionAvg->Gas365;
                $alldata['water7'] = $productionAvg->Water7;
                $alldata['water30'] = $productionAvg->Water30;
                $alldata['water365'] = $productionAvg->water365;
                $alldata['oilP30'] = $productionAvg->OilP30;
                $alldata['gasP30'] = $productionAvg->GasP30;
                $alldata['waterP30'] = $productionAvg->WaterP30;
                
                $responsedata[] = $alldata;
            }
            
            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get All Production Average',
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

    public function getProductionAvgField(Request $request) {
        $data = array();
        $responsedata = array();
        $status = 200;

        $userid = $request->userid;
        
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
            $arrProductionAvgField = ProductionAvgField::get();

            foreach ($arrProductionAvgField as $productionAvgField) {
                $alldata = array();

                $alldata['prodID'] =  $productionAvgField->ProdID;
                $alldata['lease'] = $productionAvgField->Lease;
                $alldata['leaseName'] = $productionAvgField->LeaseName;
                $alldata['leaseField'] = $productionAvgField->LeaseField;
                $alldata['productionDate'] = $productionAvgField->ProductionDate == null ? null : Carbon::parse($productionAvg->ProductionDate)->format('m/d/Y H:i:s');
                $alldata['allocatedVolume'] = $productionAvgField->AllocatedVolume;
                $alldata['oil7'] = $productionAvgField->Oil7;
                $alldata['oil30'] = $productionAvgField->Oil30;
                $alldata['oil365'] = $productionAvgField->Oil365;
                $alldata['gas7'] = $productionAvgField->Gas7;
                $alldata['gas30'] = $productionAvgField->Gas30;
                $alldata['gas365'] = $productionAvgField->Gas365;
                $alldata['water7'] = $productionAvgField->Water7;
                $alldata['water30'] = $productionAvgField->Water30;
                $alldata['water365'] = $productionAvgField->water365;
                $alldata['oilP30'] = $productionAvgField->OilP30;
                $alldata['gasP30'] = $productionAvgField->GasP30;
                $alldata['waterP30'] = $productionAvgField->WaterP30;
                
                $responsedata[] = $alldata;
            }
            
            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get All Production Average Field',
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
