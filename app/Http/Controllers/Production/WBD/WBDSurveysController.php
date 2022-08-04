<?php

namespace App\Http\Controllers\Production\WBD;

use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Services\TokenService;
use Carbon\Carbon;

use Illuminate\Support\Facades\DB;
use App\Models\WBD\WBDSurveys;

class WBDSurveysController extends Controller
{

    public function getWBDSurveys(Request $request)
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

            // $arrMaxMDs = array();
            // $arrMDs = WBDSurveys::select(DB::raw('max(MD) as MD'))
            //             ->groupBy('MinHoleSize')
            //             ->get();
            // foreach ($arrMDs as $md) {
            //     $arrMaxMDs[] = $md->MD;
            // }
            // $arrWBDRods = WBDSurveys::whereIn('MD', $arrMaxMDs)
            //         ->get();   

            $arrWBDRods = WBDSurveys::get();

            foreach ($arrWBDRods as $wbdRod) {

                $alldata = array();

                $alldata['surveyPointID'] = $wbdRod->SurveyPointID;
                $alldata['lease'] = $wbdRod->LeaseID;
                $alldata['wellNum'] = $wbdRod->LeaseWell;
                $alldata['surveyPointDate'] = $wbdRod->SurveyPointDate == null ? null : Carbon::parse($wbdRod->SurveyPointDate)->format('m/d/Y H:i:s');
                $alldata['md'] = $wbdRod->MD;
                $alldata['inclination'] = $wbdRod->Inclination;
                $alldata['trueAzimuth'] = $wbdRod->TrueAzimuth;
                $alldata['tvd'] = $wbdRod->TVD;
                $alldata['minHoleSize'] = $wbdRod->MinHoleSize;
                $alldata['dogLegSeverity'] = $wbdRod->DogLegSeverity;
                $alldata['comments'] = $wbdRod->Comments;
                                
                $responsedata[] = $alldata;
            }

            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get WBDSurveys',
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
