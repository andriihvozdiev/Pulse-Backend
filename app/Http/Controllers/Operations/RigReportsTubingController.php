<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\RigReportsModels\RigReports;
use App\Models\RigReportsModels\RigReportsTubing;
use App\Models\RigReportsModels\TubingSize;
use App\Models\RigReportsModels\TubingType;
use App\Models\RigReportsModels\TubingLength;

use App\Services\TokenService;

class RigReportsTubingController extends Controller
{
    /**
     * Get RigReportsTubing from tblRigReportsTubing table.
     *
     * @return json response.
     */
    public function getRigReportsTubing(Request $request)
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
            $arrRigReportsTubing = RigReportsTubing::get();

            foreach($arrRigReportsTubing as $rigReportTubing){

                $alldata = array();

                $alldata['reportID'] = $rigReportTubing->ReportID;
                $alldata['reportAppID'] = $rigReportTubing->ReportAppID;
              
                $alldata['tubingID'] = $rigReportTubing->TubingID;
                $alldata['tubingSize'] = $rigReportTubing->TubingSize;
                $alldata['tubingType'] = $rigReportTubing->TubingType;
                $alldata['tubingLength'] = $rigReportTubing->TubingLength;
                $alldata['tubingCount'] = $rigReportTubing->TubingCount;
                $alldata['tubingFootage'] = $rigReportTubing->TubingFootage;
                $alldata['tubingOrder'] = $rigReportTubing->TubingOrder;
                $alldata['inOut'] = $rigReportTubing->InOut;
                
                $responsedata[] = $alldata;
            }

            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get all RigReportsTubing',
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
     * Get TubingSize from tblTbgSize table.
     *
     * @return json response.
     */
    public function getTubingSize(Request $request)
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
            $arrTubingSize = TubingSize::get();

            foreach($arrTubingSize as $tubingSize){

                $alldata = array();

                $alldata['tbgSizeID'] = $tubingSize->TbgSizeID;
                $alldata['tbgSize'] = $tubingSize->TbgSize;                
                $alldata['nominalSize'] = $tubingSize->NominalSize;                
                
                $responsedata[] = $alldata;
            }

            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get all TubingSize',
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
     * Get Tubing Type from tblTbgType table.
     *
     * @return json response.
     */
    public function getTubingType(Request $request)
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
            $arrTubingType = TubingType::get();

            foreach($arrTubingType as $tubingType){

                $alldata = array();

                $alldata['tbgTypeID'] = $tubingType->TbgTypeID;
                $alldata['tbgType'] = $tubingType->TbgType;
                
                $responsedata[] = $alldata;
            }

            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get all TubingType',
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
     * Get Tubing Length from tblTbgLength table.
     *
     * @return json response.
     */
    public function getTubingLength(Request $request)
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
            $arrTubingLength = TubingLength::get();

            foreach($arrTubingLength as $tubingLength){

                $alldata = array();

                $alldata['tbgLengthID'] = $tubingLength->TbgLengthID;
                $alldata['tbgLength'] = $tubingLength->TbgLength;
                $alldata['nominalSize'] = $tubingLength->NominalSize;
                
                $responsedata[] = $alldata;
            }

            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get all TubingLength',
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
     * Upload RigReportsTubing from tblRigReportsTubing table.
     *
     * @return json response.
     */
    public function uploadRigReportsTubing(Request $request)
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

        $arrTubing = $request->tubing;

        try {

            for ($i = 0; $i < count($arrTubing); $i++) {
                $dicTubing = $arrTubing[$i];

                $reportID = isset($dicRods['reportID']) ? $dicRods['reportID'] : null;
                $tubingSize = $dicTubing['tubingSize'];
                $tubingType = $dicTubing['tubingType'];
                $tubingLength = $dicTubing['tubingLength'];
                $tubingCount = $dicTubing['tubingCount'];
                $tubingFootage = isset($dicTubing['tubingFootage']) ? $dicTubing['tubingFootage'] : null;
                $tubingOrder = $dicTubing['tubingOrder'];
                $inOut = $dicTubing['inOut'];
                $reportAppID = isset($dicTubing['reportAppID']) ? $dicTubing['reportAppID'] : null;

                $arrReports = RigReports::where('ReportAppID', $reportAppID)->get();

                if (!$arrReports->isEmpty()) {

                    $reports = $arrReports->first();
                    $reportID = $reportAppID == null ? $reportID : $reports->ReportID;

                    $existingTubing = RigReportsTubing::where('ReportID', $reportID)
                            ->where('TubingSize', $tubingSize)
                            ->where('TubingType', $tubingType)
                            ->where('TubingLength', $tubingLength)
                            ->where('TubingCount', $tubingCount)
                            ->where('InOut', $inOut)
                            ->get();

                    if (!$existingTubing->isEmpty()) {
                        $oldTubing = $existingTubing->first();
                        

                        $oldTubing['TubingFootage'] = $tubingFootage;
                        $oldTubing['TubingOrder'] = $tubingOrder;

                        if ($oldTubing->save())
                        {
                            $data = array(
                                'status' => 200,
                                'success' => true,
                                'message' => 'Upgrade Tubing successfully.',
                            ); 
                        } 
                        else 
                        {
                            $status = 400;
                            $data = array(
                                'status' => 400,
                                'success' => true,
                                'message' => 'Failed adding a new Tubing.',
                            ); 
                            return response(json_encode($data), $status);
                        }

                        
                    } else {
                        $newTubing = new RigReportsTubing;   

                        $newTubing['ReportID'] = $reportID;
                        $newTubing['TubingSize'] = $tubingSize;
                        $newTubing['TubingType'] = $tubingType;
                        $newTubing['TubingLength'] = $tubingLength;
                        $newTubing['TubingCount'] = $tubingCount;
                        $newTubing['TubingFootage'] = $tubingFootage;
                        $newTubing['TubingOrder'] = $tubingOrder;
                        $newTubing['ReportAppID'] = $reportAppID;
                        $newTubing['InOut'] = $inOut;

                        if ($newTubing->save())
                        {
                            $data = array(
                                'status' => 200,
                                'success' => true,
                                'message' => 'Added a new Tubing successfully.',
                            ); 
                        } 
                        else 
                        {
                            $status = 400;
                            $data = array(
                                'status' => 400,
                                'success' => true,
                                'message' => 'Failed adding a new Tubing.',
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
