<?php

namespace App\Http\Controllers\Production;

use DB;

use App\Http\Controllers\Controller;
use App\Services\TokenService;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\GasMeterData;
use App\Models\WaterMeterData;

class MeterDataController extends Controller
{
    /**
     * Get GasMeterData from GasMeterData table.
     *
     * @return json response.
     */
    public function getGasMeterData(Request $request)
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
            $sqlGasMetersData = "SELECT * FROM GasMeterData WHERE CheckTime >= DATEADD(day, -" . $daysToSync . ", GETDATE())";
            
            $sqlLast = "SELECT * FROM GasMeterData n WHERE CheckTime = (SELECT MAX(CheckTime) as CheckTime FROM GasMeterData GROUP BY Lease HAVING Lease=n.Lease)";
            
            $sql = $sqlGasMetersData . " UNION " . $sqlLast . " ORDER BY CheckTime DESC";
            $allGasMetersData = DB::select($sql);
            
            foreach($allGasMetersData as $meterData){
                $alldata = array();
                
                $alldata['dataID'] = $meterData->DataID;
                $alldata['userid'] = $meterData->UserID;
                $alldata['checkTime'] = $meterData->CheckTime == null ? null : Carbon::parse($meterData->CheckTime)->format('m/d/Y H:i:s');
                $alldata['deviceID'] = $meterData->DeviceID;
                $alldata['idGasMeter'] = $meterData->IDGasMeter;                    
                $alldata['lease'] = $meterData->Lease;
                $alldata['wellNumber'] = $meterData->WellNumber;
                $alldata['meterProblem'] = $meterData->MeterProblem;
                $alldata['linePressure'] = $meterData->LinePressure;
                $alldata['currentFlow'] = $meterData->CurrentFlow;
                $alldata['yesterdayFlow'] = $meterData->YesterdayFlow;
                $alldata['diffPressure'] = $meterData->DiffPressure;
                $alldata['comments'] = $meterData->Comments;
                $alldata['entryTime'] = $meterData->EntryTime == null ? null : Carbon::parse($meterData->EntryTime)->format('m/d/Y H:i:s');
                $alldata['meterCumVol'] = $meterData->MeterCumVol;
                
                $responsedata[] = $alldata;
            }
            
            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get all Gas MeterData',
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
     * Get WaterMeterData from WaterMeterData table.
     *
     * @return json response.
     */
    public function getWaterMeterData(Request $request)
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
            $sqlWaterMetersData = "SELECT * FROM WaterMeterData WHERE CheckTime >= DATEADD(day, -" . $daysToSync . ", GETDATE())";

            $sqlLastWMD = "SELECT * FROM WaterMeterData n WHERE CheckTime = (SELECT MAX(CheckTime) as CheckTime FROM WaterMeterData GROUP BY Lease HAVING Lease=n.Lease)";
            
            $sql = $sqlWaterMetersData . " UNION " . $sqlLastWMD . " ORDER BY CheckTime DESC";

            $allMetersData = DB::select($sql);
            
            foreach($allMetersData as $meterData){
                $alldata = array();
                $meterData = get_object_vars($meterData);
                
                $alldata['checkTime'] = Carbon::parse($meterData['CheckTime'])->format('m/d/Y H:i:s');
                $alldata['deviceID'] = $meterData['DeviceID'];
                $alldata['userid'] = $meterData['UserID'];
                $alldata['lease'] = $meterData['Lease'];
                $alldata['meterNum'] = $meterData['MeterNum'];
                $alldata['location'] = $meterData['Location'];
                $alldata['meterProblem'] = $meterData['MeterProblem'];
                $alldata['totalVolume'] = $meterData['TotalVolume'];
                $alldata['currentFlow'] = $meterData['CurrentFlow'];
                $alldata['yesterdayFlow'] = $meterData['YesterdayFlow'];
                $alldata['comments'] = $meterData['Comments'];
                $alldata['netFlow'] = $meterData['NetFlow'];
                $alldata['entryTime'] = $meterData['EntryTime'] == null ? null : Carbon::parse($meterData['EntryTime'])->format('m/d/Y H:i:s');
                $alldata['24HrVol'] = $meterData['24HrVol'];
                $alldata['24HrTime'] = $meterData['24HrTime'] == null ? null : Carbon::parse($meterData['24HrTime'])->format('m/d/Y H:i:s');;
                $alldata['wmdID'] = $meterData['WMDID'];
                $alldata['resetVolume'] = $meterData['ResetVolume'] == null ? null : $meterData['ResetVolume'];

                $responsedata[] = $alldata;
            }
            
            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get all Water MeterData',
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
     * Upload GasMeterData to GasMeterData table.
     *
     * @param  Request  $request
     */
    public function uploadGasMeterData(Request $request)
    {

        $data = array();
        $responsedata = array();
        $status = 200;

        $arrMeterData = $request->gasmeterdata;
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
            
            for ($i = 0; $i < count($arrMeterData); $i++) {
                $dicGasMeterData = $arrMeterData[$i];

                $dataID = $dicGasMeterData['dataID'];
                $entryTime = Carbon::parse($dicGasMeterData['entryTime']);
                $checkTime = Carbon::parse($dicGasMeterData['checkTime']);
                $lease = $dicGasMeterData['lease'];
                $deviceID = $dicGasMeterData['deviceID'];

                $idGasMeter = $dicGasMeterData['idGasMeter'];
                $meterProblem = isset($dicGasMeterData['meterProblem']) ? $dicGasMeterData['meterProblem'] : null;

                $currentFlow = isset($dicGasMeterData['currentFlow']) ? $dicGasMeterData['currentFlow'] : null;
                $yesterdayFlow = isset($dicGasMeterData['yesterdayFlow']) ? $dicGasMeterData['yesterdayFlow'] : null;
                $meterCumVol = isset($dicGasMeterData['meterCumVol']) ? $dicGasMeterData['meterCumVol'] : null;
                $linePressure = isset($dicGasMeterData['linePressure']) ? $dicGasMeterData['linePressure'] : null;
                $diffPressure = isset($dicGasMeterData['diffPressure']) ? $dicGasMeterData['diffPressure'] : null;
                $comments = isset($dicGasMeterData['comments']) ? $dicGasMeterData['comments'] : null;
                
                $userid = $dicGasMeterData['userid'];
                
                $arrGasMeterDataList = GasMeterData::where('Lease', $lease)
                    ->where('IDGasMeter', $idGasMeter)
                    ->where('DataID', $dataID)
                    ->get();

                if (!($arrGasMeterDataList->isEmpty())) {
                    $oldGasMeterData = $arrGasMeterDataList->first();
                    
                    $oldGasMeterData['DeviceID'] = $deviceID;
                    $oldGasMeterData['EntryTime'] = $entryTime;
                    $oldGasMeterData['CheckTime'] = $checkTime;
                    $oldGasMeterData['Lease'] = $lease;
                    $oldGasMeterData['IDGasMeter'] = $idGasMeter;                    
                    $oldGasMeterData['MeterProblem'] = $meterProblem;

                    $oldGasMeterData['CurrentFlow'] = $currentFlow;
                    $oldGasMeterData['YesterdayFlow'] = $yesterdayFlow;
                    $oldGasMeterData['MeterCumVol'] = $meterCumVol;
                    $oldGasMeterData['LinePressure'] = $linePressure;
                    $oldGasMeterData['DiffPressure'] = $diffPressure;
                    $oldGasMeterData['Comments'] = $comments;
                    
                    $oldGasMeterData['UserID'] = $userid;

                    if ($oldGasMeterData->save())
                    {
                        $data = array(
                            'status' => 200,
                            'success' => true,
                            'message' => 'Updated a GasMeterData successfully.',
                        ); 
                    } 
                    else 
                    {
                        $status = 400;
                        $data = array(
                            'status' => 400,
                            'success' => true,
                            'message' => 'Failed adding a new GasMeterData.',
                        ); 
                        return response(json_encode($data), $status);
                    }

                    
                } else {
                    $newGasMeterData = new GasMeterData;                    

                    $newGasMeterData['DeviceID'] = $deviceID;
                    $newGasMeterData['EntryTime'] = $entryTime;
                    $newGasMeterData['CheckTime'] = $checkTime;
                    $newGasMeterData['Lease'] = $lease;
                    $newGasMeterData['IDGasMeter'] = $idGasMeter;                    
                    $newGasMeterData['MeterProblem'] = $meterProblem;

                    $newGasMeterData['CurrentFlow'] = $currentFlow;
                    $newGasMeterData['YesterdayFlow'] = $yesterdayFlow;
                    $newGasMeterData['MeterCumVol'] = $meterCumVol;
                    $newGasMeterData['LinePressure'] = $linePressure;
                    $newGasMeterData['DiffPressure'] = $diffPressure;
                    $newGasMeterData['Comments'] = $comments;
                    
                    $newGasMeterData['UserID'] = $userid;
                    
                    if ($newGasMeterData->save())
                    {
                        $data = array(
                            'status' => 200,
                            'success' => true,
                            'message' => 'Added a new GasMeterData successfully.',
                        ); 

                    } 
                    else 
                    {
                        $status = 400;
                        $data = array(
                            'status' => 400,
                            'success' => true,
                            'message' => 'Failed adding a new GasMeterData.',
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


    /**
     * Get WaterMeterData to WaterMeterData table.
     *
     * @return json response.
     */
    public function uploadWaterMeterData(Request $request)
    {

        $data = array();
        $responsedata = array();
        $status = 200;

        $arrMeterData = $request->watermeterdata;
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
            
            for ($i = 0; $i < count($arrMeterData); $i++) {
                $dicWaterMeterData = $arrMeterData[$i];

                $wmdID = $dicWaterMeterData['wmdID'];
                $entryTime = Carbon::parse($dicWaterMeterData['entryTime']);
                $checkTime = Carbon::parse($dicWaterMeterData['checkTime']);
                $lease = $dicWaterMeterData['lease'];
                $deviceID = $dicWaterMeterData['deviceID'];

                $meterNum = isset($dicWaterMeterData['meterNum']) ? $dicWaterMeterData['meterNum'] : 0;
                $meterProblem = isset($dicWaterMeterData['meterProblem']) ? $dicWaterMeterData['meterProblem'] : null;

                $currentFlow = isset($dicWaterMeterData['currentFlow']) ? $dicWaterMeterData['currentFlow'] : null;
                $yesterdayFlow = isset($dicWaterMeterData['yesterdayFlow']) ? $dicWaterMeterData['yesterdayFlow'] : null;
                $totalVolume = isset($dicWaterMeterData['totalVolume']) ? $dicWaterMeterData['totalVolume'] : null;
                $resetVolume = isset($dicWaterMeterData['resetVolume']) ? $dicWaterMeterData['resetVolume'] : null;
                $comments = isset($dicWaterMeterData['comments']) ? $dicWaterMeterData['comments'] : null;
                
                $userid = $dicWaterMeterData['userid'];
                                
                
                $arrWaterMeterDataList = WaterMeterData::where('Lease', $lease)
                    ->where('MeterNum', $meterNum)
                    ->where('WMDID', $wmdID)
                    ->get();

                if (!($arrWaterMeterDataList->isEmpty())) {
                    $oldWaterMeterData = $arrWaterMeterDataList->first();
                    
                    $oldWaterMeterData['DeviceID'] = $deviceID;
                    $oldWaterMeterData['EntryTime'] = $entryTime;
                    $oldWaterMeterData['CheckTime'] = $checkTime;
                    $oldWaterMeterData['Lease'] = $lease;
                    $oldWaterMeterData['MeterNum'] = $meterNum;                    
                    $oldWaterMeterData['MeterProblem'] = $meterProblem;

                    $oldWaterMeterData['CurrentFlow'] = $currentFlow;
                    $oldWaterMeterData['YesterdayFlow'] = $yesterdayFlow;
                    $oldWaterMeterData['TotalVolume'] = $totalVolume;
                    $oldWaterMeterData['ResetVolume'] = $resetVolume;
                    $oldWaterMeterData['Comments'] = $comments;
                    
                    $oldWaterMeterData['UserID'] = $userid;

                    if ($oldWaterMeterData->save())
                    {
                        $data = array(
                            'status' => 200,
                            'success' => true,
                            'message' => 'Updated a WaterMeterData successfully.',
                        ); 
                    } 
                    else 
                    {
                        $status = 400;
                        $data = array(
                            'status' => 400,
                            'success' => true,
                            'message' => 'Failed adding a new WaterMeterData.',
                        ); 
                        return response(json_encode($data), $status);
                    }

                    
                } else {
                    $newWaterMeterData = new WaterMeterData;                    

                    $newWaterMeterData['DeviceID'] = $deviceID;
                    $newWaterMeterData['EntryTime'] = $entryTime;
                    $newWaterMeterData['CheckTime'] = $checkTime;
                    $newWaterMeterData['Lease'] = $lease;
                    $newWaterMeterData['MeterNum'] = $meterNum;                    
                    $newWaterMeterData['MeterProblem'] = $meterProblem;

                    $newWaterMeterData['CurrentFlow'] = $currentFlow;
                    $newWaterMeterData['YesterdayFlow'] = $yesterdayFlow;
                    $newWaterMeterData['TotalVolume'] = $totalVolume;
                    $newWaterMeterData['ResetVolume'] = $resetVolume;
                    $newWaterMeterData['Comments'] = $comments;
                    
                    $newWaterMeterData['UserID'] = $userid;
                    
                    if ($newWaterMeterData->save())
                    {
                        $data = array(
                            'status' => 200,
                            'success' => true,
                            'message' => 'Added a new WaterMeterData successfully.',
                        ); 

                    } 
                    else 
                    {
                        $status = 400;
                        $data = array(
                            'status' => 400,
                            'success' => true,
                            'message' => 'Failed adding a new WaterMeterData.',
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
