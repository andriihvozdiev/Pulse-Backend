<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\RigReportsModels\RigReports;
use App\Models\RigReportsModels\RigReportsPump;
use App\Models\RigReportsModels\ListPumpType;
use App\Models\RigReportsModels\ListPumpSize;

use App\Services\TokenService;

class RigReportsPumpController extends Controller
{
    /**
     * Get data from tblRigReportsPump table.
     *
     * @return json response.
     */
    public function getRigReportsPump(Request $request)
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
            $arrRigReportsPump = RigReportsPump::get();

            foreach($arrRigReportsPump as $rigReportPump){

                $alldata = array();

                $alldata['reportID'] = $rigReportPump->ReportID;
                $alldata['reportAppID'] = $rigReportPump->ReportAppID;
              
                $alldata['pumpID'] = $rigReportPump->PumpID;
                $alldata['pumpSize'] = $rigReportPump->PumpSize;
                $alldata['pumpType'] = $rigReportPump->PumpType;
                $alldata['pumpLength'] = $rigReportPump->PumpLength;
                $alldata['pumpOrder'] = $rigReportPump->PumpOrder;
                $alldata['inOut'] = $rigReportPump->InOut;

                $responsedata[] = $alldata;
            }

            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get all RigReportsPump',
                'data' => $responsedata,
            ); 
            
        } catch (Illuminate\Database\QueryException $e) {
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
     * Get PumpType from ListDHPumpType.
     *
     * @return json response.
     */
    public function getPumpType(Request $request)
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
            $arrPumpType = ListPumpType::get();

            foreach($arrPumpType as $pumpType){

                $alldata = array();

                $alldata['pumpType'] = $pumpType->PumpType;
                                
                $responsedata[] = $alldata;
            }

            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get all PumpType',
                'data' => $responsedata,
            ); 
            
        } catch (Illuminate\Database\QueryException $e) {
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
     * Get PumpSize from ListDownholePumps table.
     *
     * @return json response.
     */
    public function getPumpSize(Request $request)
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
            $arrPumpSize = ListPumpSize::get();

            foreach($arrPumpSize as $pumpSize){

                $alldata = array();

                $alldata['size'] = $pumpSize->Size;
                $alldata['nominalSize'] = $pumpSize->NominalSize;
                
                $responsedata[] = $alldata;
            }

            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get all PumpSize',
                'data' => $responsedata,
            ); 
            
        } catch (Illuminate\Database\QueryException $e) {
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
     * Upload RigReportsPump to tblRigReportsPump
     *
     * @return json response.
     */
    public function uploadRigReportsPump(Request $request)
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
        
        $arrPump = $request->pump;

        try {

            for ($i = 0; $i < count($arrPump); $i++) {
                $dicPump = $arrPump[$i];

                $reportID = isset($dicRods['reportID']) ? $dicRods['reportID'] : null;
                $pumpSize = $dicPump['pumpSize'];
                $pumpType = $dicPump['pumpType'];
                $pumpLength = $dicPump['pumpLength'];
                $pumpOrder = $dicPump['pumpOrder'];
                $inOut = $dicPump['inOut'];
                $reportAppID = isset($dicPump['reportAppID']) ? $dicPump['reportAppID'] : null;

                
                $arrReports = RigReports::where('ReportAppID', $reportAppID)->get();

                if (!$arrReports->isEmpty()) {

                    $reports = $arrReports->first();
                    $reportID = $reportAppID == null ? $reportID : $reports->ReportID;

                    $existingPump = RigReportsPump::where('ReportID', $reportID)
                            ->where('PumpSize', $pumpSize)
                            ->where('PumpType', $pumpType)
                            ->where('PumpLength', $pumpLength)
                            ->where('InOut', $inOut)
                            ->get();

                    if (!$existingPump->isEmpty()) {
                        $oldPump = $existingPump->first();
                        
                        $oldPump['PumpOrder'] = $pumpOrder;
                        
                        if ($oldPump->save())
                        {
                            $data = array(
                                'status' => 200,
                                'success' => true,
                                'message' => 'Upgrade Pump successfully.',
                            ); 
                        } 
                        else 
                        {
                            $status = 400;
                            $data = array(
                                'status' => 400,
                                'success' => true,
                                'message' => 'Failed adding a new Pump.',
                            ); 
                            return response(json_encode($data), $status);
                        }
                        
                    } else {
                        $newPump = new RigReportsPump;                    

                        $newPump['ReportID'] = $reportID;
                        $newPump['PumpSize'] = $pumpSize;
                        $newPump['PumpType'] = $pumpType;
                        $newPump['PumpLength'] = $pumpLength;
                        $newPump['PumpOrder'] = $pumpOrder;
                        $newPump['ReportAppID'] = $reportAppID;
                        $newPump['InOut'] = $inOut;

                        if ($newPump->save())
                        {
                            $data = array(
                                'status' => 200,
                                'success' => true,
                                'message' => 'Added a new Pump successfully.',
                            ); 
                        } 
                        else 
                        {
                            $status = 400;
                            $data = array(
                                'status' => 400,
                                'success' => true,
                                'message' => 'Failed adding a new Pump.',
                            ); 
                            return response(json_encode($data), $status);
                        }
                    }

                } else {
                    $status = 400;
                    $data = array(
                        'status' => 400,
                        'success' => true,
                        'message' => 'Cannot find correct RigReports',
                    ); 
                    return response(json_encode($data), $status);
                }
            }
            
        } catch (Illuminate\Database\QueryException $e) {
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
