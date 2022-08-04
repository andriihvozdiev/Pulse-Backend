<?php

namespace App\Http\Controllers\Production;

use DB;

use App\Http\Controllers\Controller;
use App\Services\TokenService;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\WellheadData;

class WellheadDataController extends Controller
{
    /**
     * Get Wellhead data from WellheadData table.
     *
     * @return json response.
     */
    public function getWellheadData(Request $request)
    {

        $data = array();
        $responsedata = array();
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
          
            $sqlWellheadData = "SELECT * FROM WellheadData WHERE CheckTime >= DATEADD(day, -" . $daysToSync . ", GETDATE())";     
                            
            $sqlLastWD = "SELECT * FROM WellheadData n 
                WHERE CheckTime = (SELECT MAX(CheckTime) as CheckTime FROM WellheadData GROUP BY Lease, WellNumber HAVING Lease=n.Lease AND WellNumber=n.WellNumber)";
            
            $sqlLastWD_Choke = "SELECT * FROM WellheadData n 
                WHERE CheckTime = (SELECT MAX(CheckTime) as CheckTime FROM WellheadData WHERE Choke IS NOT NULL GROUP BY Lease, WellNumber HAVING Lease=n.Lease AND WellNumber=n.WellNumber)";
            
            $sqlLastWD_ProdType = "SELECT * FROM WellheadData n 
                WHERE CheckTime = (SELECT MAX(CheckTime) as CheckTime FROM WellheadData WHERE ProdType IS NOT NULL GROUP BY Lease, WellNumber HAVING Lease=n.Lease AND WellNumber=n.WellNumber)";
            
            $sqlLastWD_PumpSize = "SELECT * FROM WellheadData n 
                WHERE CheckTime = (SELECT MAX(CheckTime) as CheckTime FROM WellheadData WHERE PumpSize IS NOT NULL GROUP BY Lease, WellNumber HAVING Lease=n.Lease AND WellNumber=n.WellNumber)";
            
            $sqlLastWD_SPM = "SELECT * FROM WellheadData n 
                WHERE CheckTime = (SELECT MAX(CheckTime) as CheckTime FROM WellheadData WHERE SPM IS NOT NULL GROUP BY Lease, WellNumber HAVING Lease=n.Lease AND WellNumber=n.WellNumber)";
            
            $sqlLastWD_StrokeSize = "SELECT * FROM WellheadData n 
                WHERE CheckTime = (SELECT MAX(CheckTime) as CheckTime FROM WellheadData WHERE StrokeSize IS NOT NULL GROUP BY Lease, WellNumber HAVING Lease=n.Lease AND WellNumber=n.WellNumber)";
            
            $sqlLastWD_Time = "SELECT * FROM WellheadData n 
                WHERE CheckTime = (SELECT MAX(CheckTime) as CheckTime FROM WellheadData WHERE (TimeOff IS NOT NULL AND TimeOn IS NOT NULL) GROUP BY Lease, WellNumber HAVING Lease=n.Lease AND WellNumber=n.WellNumber)";
            
            $sqlLastWD_CP = "SELECT * FROM WellheadData n 
                WHERE CheckTime = (SELECT MAX(CheckTime) as CheckTime FROM WellheadData WHERE CasingPressure IS NOT NULL GROUP BY Lease, WellNumber HAVING Lease=n.Lease AND WellNumber=n.WellNumber)";
            
            $sqlLastWD_TP = "SELECT * FROM WellheadData n 
                WHERE CheckTime = (SELECT MAX(CheckTime) as CheckTime FROM WellheadData WHERE TubingPressure IS NOT NULL GROUP BY Lease, WellNumber HAVING Lease=n.Lease AND WellNumber=n.WellNumber)";
            
            $sqlLastWD_SP = "SELECT * FROM WellheadData n 
                WHERE CheckTime = (SELECT MAX(CheckTime) as CheckTime FROM WellheadData WHERE BradenheadPressure IS NOT NULL GROUP BY Lease, WellNumber HAVING Lease=n.Lease AND WellNumber=n.WellNumber)";
            
            $sqlLastWD_Cut = "SELECT * FROM WellheadData n 
                WHERE CheckTime = (SELECT MAX(CheckTime) as CheckTime FROM WellheadData WHERE (OilCut IS NOT NULL OR WaterCut IS NOT NULL OR EmulsionCut IS NOT NULL) GROUP BY Lease, WellNumber HAVING Lease=n.Lease AND WellNumber=n.WellNumber)";
            
            $sqlLastWD_Pound = "SELECT * FROM WellheadData n 
                WHERE CheckTime = (SELECT MAX(CheckTime) as CheckTime FROM WellheadData WHERE Pound IS NOT NULL GROUP BY Lease, WellNumber HAVING Lease=n.Lease AND WellNumber=n.WellNumber)";
            
            $sqlLastWD_ESPHz = "SELECT * FROM WellheadData n 
                WHERE CheckTime = (SELECT MAX(CheckTime) as CheckTime FROM WellheadData WHERE ESPHz IS NOT NULL GROUP BY Lease, WellNumber HAVING Lease=n.Lease AND WellNumber=n.WellNumber)";
            
            $sqlLastWD_ESPAmp = "SELECT * FROM WellheadData n 
                WHERE CheckTime = (SELECT MAX(CheckTime) as CheckTime FROM WellheadData WHERE ESPAmp IS NOT NULL GROUP BY Lease, WellNumber HAVING Lease=n.Lease AND WellNumber=n.WellNumber)";
            
            $sql = $sqlWellheadData . " UNION " . $sqlLastWD . " UNION " . $sqlLastWD . " UNION " . $sqlLastWD_Choke . " UNION " . $sqlLastWD_ProdType . " UNION " . $sqlLastWD_PumpSize . " UNION " . $sqlLastWD_SPM . " UNION " . $sqlLastWD_StrokeSize . " UNION " . $sqlLastWD_Time . " UNION " . $sqlLastWD_CP . " UNION " . $sqlLastWD_TP . " UNION " . $sqlLastWD_SP . " UNION " . $sqlLastWD_Cut . " UNION " . $sqlLastWD_Pound . " UNION " . $sqlLastWD_ESPHz . " UNION " . $sqlLastWD_ESPAmp . " ORDER BY CheckTime DESC";
            
			$allWellheadData = DB::select($sql);
			
            foreach($allWellheadData as $wellheadData){
                $alldata = array();
                
                $alldata['dataID'] = $wellheadData->DataID;
                $alldata['userid'] = $wellheadData->UserID;
                $alldata['checkTime'] = Carbon::parse($wellheadData->CheckTime)->format('m/d/Y H:i:s');
                $alldata['deviceID'] = $wellheadData->DeviceID;
                $alldata['lease'] = $wellheadData->Lease;
                $alldata['wellNumber'] = $wellheadData->WellNumber;
                $alldata['wellOn'] = $wellheadData->WellOn;
                $alldata['wellProblem'] = $wellheadData->WellProblem;
                $alldata['prodType'] = $wellheadData->ProdType;
                $alldata['choke'] = $wellheadData->Choke;
                $alldata['pumpSize'] = $wellheadData->PumpSize;
                $alldata['spm'] = $wellheadData->SPM;
                $alldata['strokeSize'] = $wellheadData->StrokeSize;
                $alldata['timeOn'] = $wellheadData->TimeOn; 
                $alldata['timeOff'] = $wellheadData->TimeOff;
                $alldata['casingPressure'] = $wellheadData->CasingPressure;
                $alldata['tubingPressure'] = $wellheadData->TubingPressure;
                $alldata['bradenheadPressure'] = $wellheadData->BradenheadPressure;
                
                $alldata['waterCut'] = $wellheadData->WaterCut;
                $alldata['emulsionCut'] = $wellheadData->EmulsionCut;
                $alldata['oilCut'] = $wellheadData->OilCut;
                $alldata['comments'] = $wellheadData->Comments;
                $alldata['pound'] = $wellheadData->Pound;
                $alldata['entryTime'] = $wellheadData->EntryTime == null ? null : Carbon::parse($wellheadData->EntryTime)->format('m/d/Y H:i:s');
                $alldata['statusArrival'] = $wellheadData->StatusArrival;
                $alldata['statusDepart'] = $wellheadData->StatusDepart;
                $alldata['espHz'] = $wellheadData->ESPHz;
                $alldata['espAmp'] = $wellheadData->ESPAmp;

                $responsedata[] = $alldata;
            }

            
            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get all Wellhead data',
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
     * Upload WellheadData to WellheadData table.
     *
     * @return json response.
     */
    public function uploadWellheadData(Request $request)
    {
        $data = array();
        $responsedata = array();
        $status = 200;

        $arrWellheadData = $request->wellheaddata;
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
          
            for ($i = 0; $i < count($arrWellheadData); $i++) {
                $dicWellheadData = $arrWellheadData[$i];

                $dataID = $dicWellheadData['dataID'];
                $entryTime = Carbon::parse($dicWellheadData['entryTime']);
                $checkTime = Carbon::parse($dicWellheadData['checkTime']);
                $lease = $dicWellheadData['lease'];
                $deviceID = $dicWellheadData['deviceID'];

                $wellNumber = isset($dicWellheadData['wellNumber']) ? $dicWellheadData['wellNumber'] : null;
                $wellProblem = isset($dicWellheadData['wellProblem']) ? $dicWellheadData['wellProblem'] : null;

                $prodType = $dicWellheadData['prodType'];
                $choke = isset($dicWellheadData['choke']) ? $dicWellheadData['choke'] : null;
                $pumpSize = isset($dicWellheadData['pumpSize']) ? $dicWellheadData['pumpSize'] : null;
                $spm = isset($dicWellheadData['spm']) ? $dicWellheadData['spm'] : null;
                $strokeSize = isset($dicWellheadData['strokeSize']) ? $dicWellheadData['strokeSize'] : null;
                $timeOn = isset($dicWellheadData['timeOn']) ? $dicWellheadData['timeOn'] : null;
                $timeOff = isset($dicWellheadData['timeOff']) ? $dicWellheadData['timeOff'] : null;
                $casingPressure = isset($dicWellheadData['casingPressure']) ? $dicWellheadData['casingPressure'] : null;
                $tubingPressure = isset($dicWellheadData['tubingPressure']) ? $dicWellheadData['tubingPressure'] : null;
                $bradenheadPressure = isset($dicWellheadData['bradenheadPressure']) ? $dicWellheadData['bradenheadPressure'] : null;

                $waterCut = isset($dicWellheadData['waterCut']) ? $dicWellheadData['waterCut'] : null;
                $emulsionCut = isset($dicWellheadData['emulsionCut']) ? $dicWellheadData['emulsionCut'] : null;
                $oilCut = isset($dicWellheadData['oilCut']) ? $dicWellheadData['oilCut'] : null;
                $pound = isset($dicWellheadData['pound']) ? $dicWellheadData['pound'] : null;
                $statusArrival = $dicWellheadData['statusArrival'];
                $statusDepart = $dicWellheadData['statusDepart'];
                $espHz = isset($dicWellheadData['espHz']) ? $dicWellheadData['espHz'] : null;
                $espAmp = isset($dicWellheadData['espAmp']) ? $dicWellheadData['espAmp'] : null;

                $comments = isset($dicWellheadData['comments']) ? $dicWellheadData['comments'] : null;
                $userid = $dicWellheadData['userid'];
                                
                
                $arrWellheadDataList = WellheadData::where('Lease', $lease)
                    ->where('WellNumber', $wellNumber)
                    ->where('DataID', $dataID)
                    ->get();

                if (!($arrWellheadDataList->isEmpty())) {
                    $oldWellheadData = $arrWellheadDataList->first();
                    
                    $oldWellheadData['DeviceID'] = $deviceID;
                    $oldWellheadData['EntryTime'] = $entryTime;
                    $oldWellheadData['CheckTime'] = $checkTime;
                    $oldWellheadData['Lease'] = $lease;
                    $oldWellheadData['WellNumber'] = $wellNumber;                    
                    $oldWellheadData['WellProblem'] = $wellProblem;

                    $oldWellheadData['ProdType'] = $prodType;
                    $oldWellheadData['Choke'] = $choke;
                    $oldWellheadData['PumpSize'] = $pumpSize;
                    $oldWellheadData['SPM'] = $spm;
                    $oldWellheadData['StrokeSize'] = $strokeSize;
                    $oldWellheadData['TimeOn'] = $timeOn;
                    $oldWellheadData['TimeOff'] = $timeOff;
                    $oldWellheadData['CasingPressure'] = $casingPressure;
                    $oldWellheadData['TubingPressure'] = $tubingPressure;
                    $oldWellheadData['BradenheadPressure'] = $bradenheadPressure;

                    $oldWellheadData['WaterCut'] = $waterCut;
                    $oldWellheadData['EmulsionCut'] = $emulsionCut;
                    $oldWellheadData['OilCut'] = $oilCut;
                    $oldWellheadData['Pound'] = $pound;
                    $oldWellheadData['StatusArrival'] = $statusArrival;
                    $oldWellheadData['StatusDepart'] = $statusDepart;
                    $oldWellheadData['ESPHz'] = $espHz;
                    $oldWellheadData['ESPAmp'] = $espAmp;

                    $oldWellheadData['Comments'] = $comments;
                    $oldWellheadData['UserID'] = $userid;
                    
                    if ($oldWellheadData->save())
                    {
                        $data = array(
                            'status' => 200,
                            'success' => true,
                            'message' => 'Added a WellheadData successfully.',
                        ); 
                    } 
                    else 
                    {
                        $status = 400;
                        $data = array(
                            'status' => 400,
                            'success' => true,
                            'message' => 'Failed adding a new WellheadData.',
                        ); 
                        return response(json_encode($data), $status);
                    }

                    
                } else {
                    $newWellheadData = new WellheadData;                    

                    $newWellheadData['DeviceID'] = $deviceID;
                    $newWellheadData['EntryTime'] = $entryTime;
                    $newWellheadData['CheckTime'] = $checkTime;
                    $newWellheadData['Lease'] = $lease;
                    $newWellheadData['WellNumber'] = $wellNumber;                    
                    $newWellheadData['WellProblem'] = $wellProblem;

                    $newWellheadData['ProdType'] = $prodType;
                    $newWellheadData['Choke'] = $choke;
                    $newWellheadData['PumpSize'] = $pumpSize;
                    $newWellheadData['SPM'] = $spm;
                    $newWellheadData['StrokeSize'] = $strokeSize;
                    $newWellheadData['TimeOn'] = $timeOn;
                    $newWellheadData['TimeOff'] = $timeOff;
                    $newWellheadData['CasingPressure'] = $casingPressure;
                    $newWellheadData['TubingPressure'] = $tubingPressure;
                    $newWellheadData['BradenheadPressure'] = $bradenheadPressure;

                    $newWellheadData['WaterCut'] = $waterCut;
                    $newWellheadData['EmulsionCut'] = $emulsionCut;
                    $newWellheadData['OilCut'] = $oilCut;
                    $newWellheadData['Pound'] = $pound;
                    $newWellheadData['StatusArrival'] = $statusArrival;
                    $newWellheadData['StatusDepart'] = $statusDepart;
                    $newWellheadData['ESPHz'] = $espHz;
                    $newWellheadData['ESPAmp'] = $espAmp;

                    $newWellheadData['Comments'] = $comments;
                    $newWellheadData['UserID'] = $userid;

                                        
                    if ($newWellheadData->save())
                    {
                        $data = array(
                            'status' => 200,
                            'success' => true,
                            'message' => 'Added a new WellheadData successfully.',
                        ); 

                    } 
                    else 
                    {
                        $status = 400;
                        $data = array(
                            'status' => 400,
                            'success' => true,
                            'message' => 'Failed adding a new WellheadData.',
                        ); 
                        return response(json_encode($data), $status);
                    }
                }

            }
            
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
