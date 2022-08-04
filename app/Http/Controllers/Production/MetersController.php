<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Services\TokenService;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

use App\Models\Meters;

class MetersController extends Controller
{
   
    /**
     * Get all meters from tblMeters table.
     *
     * @return json response.
     */
    public function getAllMeters(Request $request)
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
            $allMetersData = Meters::get();

            foreach($allMetersData as $meterData){
                $alldata = array();
                
                $alldata['meterID'] = $meterData->MeterID;
                $alldata['meterLease'] = $meterData->MeterLease;
                $alldata['meterWell'] = $meterData->MeterWell;
                $alldata['meterName'] = $meterData->MeterName;
                $alldata['active'] = $meterData->Active;
                $alldata['meterType'] = $meterData->MeterType;
                $alldata['waterMeterID'] = $meterData->WaterMeterID;
                $alldata['gasMeterID'] = $meterData->GasMeterID;
                $alldata['appName'] = $meterData->AppName;
                
                $responsedata[] = $alldata;
            }
            
            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get all GasMeter data',
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
