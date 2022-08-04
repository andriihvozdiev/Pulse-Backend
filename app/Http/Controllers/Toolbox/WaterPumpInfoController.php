<?php

namespace App\Http\Controllers\Toolbox;

use App\Http\Controllers\Controller;
use App\Services\TokenService;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

use App\Models\WaterPumpInfo;

class WaterPumpInfoController extends Controller
{
    /**
     * Get WaterPumpInfo from tblWaterPumpInfo table.
     *
     * @return json response.
     */
    public function getWaterPumpInfo(Request $request)
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
            
            $arrWaterPumpInfo = WaterPumpInfo::get();

            foreach($arrWaterPumpInfo as $waterPumpInfo){
                $alldata = array();
                
                $alldata['pumpID'] = $waterPumpInfo->PumpID;
                $alldata['brand'] = $waterPumpInfo->Brand;
                $alldata['pumpType'] = $waterPumpInfo->PumpType;
                $alldata['plungerAction'] = $waterPumpInfo->PlungerAction;
                $alldata['pumpTitle'] = $waterPumpInfo->PumpTitle;
                $alldata['oldName'] = $waterPumpInfo->OldName;
                $alldata['newName'] = $waterPumpInfo->NewName;
                $alldata['minPlungerD'] = $waterPumpInfo->MinPlungerD;
                $alldata['maxPlungerD'] = $waterPumpInfo->MaxPlungerD;
                $alldata['stroke'] = $waterPumpInfo->Stroke;
                $alldata['maxPressure'] = $waterPumpInfo->MaxPressure;
                $alldata['maxGPM'] = $waterPumpInfo->MaxGPM;
                $alldata['maxBPD'] = $waterPumpInfo->MaxBPD; 
                $alldata['maxBHP'] = $waterPumpInfo->MaxBHP;
                $alldata['rpMatBHP'] = $waterPumpInfo->RPMatBHP; 
                $alldata['plungerLoad'] = $waterPumpInfo->PlungerLoad;
                $alldata['fluidEnd'] = $waterPumpInfo->FluidEnd; 
                $alldata['crankExtD'] = $waterPumpInfo->CrankExtD; 
                $alldata['crankExtL'] = $waterPumpInfo->CrankExtL; 
                $alldata['keywayWidth'] = $waterPumpInfo->KeywayWidth; 
                $alldata['keywayDepth'] = $waterPumpInfo->KeywayDepth; 
                $alldata['maxSheaveD'] = $waterPumpInfo->MaxSheaveD; 
                $alldata['crankcaseOilCap'] = $waterPumpInfo->CrankcaseOilCap; 
                
                $responsedata[] = $alldata;
            }

            
            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get all WaterPumpInfo data',
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
