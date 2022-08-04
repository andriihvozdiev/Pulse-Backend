<?php

namespace App\Http\Controllers\Production\WBD;

use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Services\TokenService;

use App\Models\WBD\WBDTreatments;

class WBDTreatmentsController extends Controller
{

    public function getWBDTreatments(Request $request)
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
            $arrTreatments = WBDTreatments::get();            

            foreach ($arrTreatments as $treatments) {

                $alldata = array();

                $alldata['treatmentID'] = $treatments->ID;
                $alldata['lease'] = $treatments->LeaseID;
                $alldata['wellNum'] = $treatments->WellNum;
                $alldata['treatmentDate'] = $treatments->TreatmentDate;
                $alldata['treatmentDesc'] = $treatments->TreatmentDesc;
                $alldata['treatmentNotes'] = $treatments->TreatmentNotes; 
                
                $responsedata[] = $alldata;
            }

            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get WBDTreatments',
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
