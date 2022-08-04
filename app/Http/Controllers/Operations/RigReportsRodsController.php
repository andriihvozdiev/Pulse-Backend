<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\RigReportsModels\RigReports;
use App\Models\RigReportsModels\RigReportsRods;
use App\Models\RigReportsModels\RodLength;
use App\Models\RigReportsModels\RodType;
use App\Models\RigReportsModels\RodSize;

use App\Services\TokenService;

class RigReportsRodsController extends Controller
{
    /**
     * Get Rods data from tblRigReportsRods table.
     *
     * @return json response.
     */
    public function getRigReportsRods(Request $request)
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
            $arrRigReportsRods = RigReportsRods::get();

            foreach($arrRigReportsRods as $rigReportRods){

                $alldata = array();

                $alldata['reportID'] = $rigReportRods->ReportID;
                $alldata['reportAppID'] = $rigReportRods->ReportAppID;
                
                $alldata['rodID'] = $rigReportRods->RodID;
                $alldata['rodSize'] = $rigReportRods->RodSize;
                $alldata['rodType'] = $rigReportRods->RodType;
                $alldata['rodLength'] = $rigReportRods->RodLength;
                $alldata['rodCount'] = $rigReportRods->RodCount;
                $alldata['rodFootage'] = $rigReportRods->RodFootage;
                $alldata['rodOrder'] = $rigReportRods->RodOrder;
                $alldata['inOut'] = $rigReportRods->InOut;
                $responsedata[] = $alldata;
            }

            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get all RigReportsRods',
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
     * Get RodLength from tblRodLength table.
     *
     * @return json response.
     */
    public function getRodLength(Request $request)
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
            $arrRodLength = RodLength::get();

            foreach($arrRodLength as $rodLength){

                $alldata = array();

                $alldata['rodLengthID'] = $rodLength->RodLengthID;
                $alldata['rodSize'] = $rodLength->RodSize;                
                $alldata['nominalSize'] = $rodLength->NominalSize;

                $responsedata[] = $alldata;
            }

            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get all RodLength',
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
     * Get RodType from tblRodType table.
     *
     * @return json response.
     */
    public function getRodType(Request $request)
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
            $arrRodType = RodType::get();

            foreach($arrRodType as $rodType){

                $alldata = array();

                $alldata['rodTypeID'] = $rodType->RodTypeID;
                $alldata['rodType'] = $rodType->RodType;                
                
                $responsedata[] = $alldata;
            }

            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get all RodType',
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
     * Get RodSize from tblRodSize table.
     *
     * @return json response.
     */
    public function getRodSize(Request $request)
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
            $arrRodSize = RodSize::get();

            foreach($arrRodSize as $rodSize){

                $alldata = array();

                $alldata['rodSizeID'] = $rodSize->RodSizeID;
                $alldata['rodSize'] = $rodSize->RodSize;                
                $alldata['nominalSize'] = $rodSize->NominalSize;                
                
                $responsedata[] = $alldata;
            }

            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get all RodSize',
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
     * Upload RigReportsRods from tblRigReportsRods table.
     *
     * @return json response.
     */
    public function uploadRigReportsRods(Request $request)
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

        $arrRods = $request->rods;

        try {

            for ($i = 0; $i < count($arrRods); $i++) {
                $dicRods = $arrRods[$i];

                $reportID = isset($dicRods['reportID']) ? $dicRods['reportID'] : null;
                $rodSize = $dicRods['rodSize'];
                $rodType = $dicRods['rodType'];
                $rodLength = $dicRods['rodLength'];
                $rodCount = $dicRods['rodCount'];
                $rodFootage = isset($dicRods['rodFootage']) ? $dicRods['rodFootage'] : null;
                $rodOrder = $dicRods['rodOrder'];
                $inOut = $dicRods['inOut'];
                $reportAppID = isset($dicRods['reportAppID']) ? $dicRods['reportAppID'] : null;
                                
                $arrReports = RigReports::where('ReportAppID', $reportAppID)->get();

                if (!$arrReports->isEmpty()) {

                    $reports = $arrReports->first();
                    $reportID = $reportAppID == null ? $reportID : $reports->ReportID;                    

                    $existingRods = RigReportsRods::where('ReportID', $reportID)
                            ->where('RodSize', $rodSize)
                            ->where('RodType', $rodType)
                            ->where('RodLength', $rodLength)
                            ->where('RodCount', $rodCount)
                            ->where('InOut', $inOut)
                            ->get();

                    if (!$existingRods->isEmpty()) {
                        $oldRods = $existingRods->first();
                        

                        $oldRods['RodFootage'] = $rodFootage;
                        $oldRods['RodOrder'] = $rodOrder;

                        if ($oldRods->save())
                        {
                            $data = array(
                                'status' => 200,
                                'success' => true,
                                'message' => 'Added a new Rods successfully.',
                            ); 
                        } 
                        else 
                        {
                            $status = 400;
                            $data = array(
                                'status' => 400,
                                'success' => true,
                                'message' => 'Failed adding a new Rods.',
                            ); 
                            return response(json_encode($data), $status);
                        }

                        
                    } else {
                        $newRods = new RigReportsRods;                    

                        $newRods['ReportID'] = $reportID;
                        $newRods['RodSize'] = $rodSize;
                        $newRods['RodType'] = $rodType;
                        $newRods['RodLength'] = $rodLength;
                        $newRods['RodCount'] = $rodCount;
                        $newRods['RodFootage'] = $rodFootage;
                        $newRods['RodOrder'] = $rodOrder;
                        $newRods['ReportAppID'] = $reportAppID;
                        $newRods['InOut'] = $inOut;

                        if ($newRods->save())
                        {
                            $data = array(
                                'status' => 200,
                                'success' => true,
                                'message' => 'Added a new Rods successfully.',
                            ); 
                        } 
                        else 
                        {
                            $status = 400;
                            $data = array(
                                'status' => 400,
                                'success' => true,
                                'message' => 'Failed adding a new Rods.',
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
