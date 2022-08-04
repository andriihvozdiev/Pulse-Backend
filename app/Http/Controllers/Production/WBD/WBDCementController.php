<?php

namespace App\Http\Controllers\Production\WBD;

use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Services\TokenService;
use Carbon\Carbon;

use App\Models\WBD\WBDCement;

class WBCementController extends Controller
{

    public function getWBDCement(Request $request)
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
            $arrCement = WBDCasingTubing::get();            

            foreach ($arrCement as $cement) {

                $alldata = array();

                $alldata['cementID'] = $casingTubing->CementID;
                $alldata['leaseID'] = $casingTubing->LeaseID;
                $alldata['wellNum'] = $casingTubing->WellNum;
                $alldata['cmtSxQty'] = $casingTubing->CmtSxQty;
                $alldata['cmtVolSlurry'] = $casingTubing->CmtVolSlurry;
                $alldata['cementJobDesc'] = $casingTubing->CementJobDesc;
                
                $alldata['cmtToC'] = $casingTubing->CmtToC;
                $alldata['cmtCalcToC'] = $casingTubing->CmtCalcToC;
                $alldata['cmtVerifiedToC'] = $casingTubing->CmtVerifiedToC;
                $alldata['cmtVerificatioType'] = $casingTubing->CmtVerificatioType;

                $alldata['timeStamp'] = $casingTubing->TimeStamp == null? null : Carbon::parse($casingTubing->TimeStamp)->format('m/d/Y H:i:s');
                $alldata['entryPerson'] = $casingTubing->EntryPerson;
                $alldata['entryUserID'] = $casingTubing->EntryUserID;
                $alldata['editTimeStamp'] = $casingTubing->EditTimeStamp == null ? null : Carbon::parse($casingTubing->EditTimeStamp)->format('m/d/Y H:i:s');
                $alldata['editPerson'] = $casingTubing->EditPerson;
                $alldata['editUserID'] = $casingTubing->EditUserID;
                $alldata['infoSource'] = $casingTubing->InfoSource;
                $alldata['infoDate'] = $casingTubing->InfoDate == null ? null : Carbon::parse($casingTubing->InfoDate)->format('m/d/Y H:i:s');
                $alldata['infoNotes'] = $casingTubing->InfoNotes;

                $responsedata[] = $alldata;
            }

            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get WBDCement',
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
   
}
