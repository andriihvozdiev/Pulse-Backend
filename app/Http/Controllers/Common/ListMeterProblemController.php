<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Services\TokenService;

use App\Models\ListMeterProblem;

class ListMeterProblemController extends Controller
{
   
    /**
     * Get ListMeterProblem from ListMeterProblem table.
     *
     * @return json response.
     */
    public function getListMeterProblem(Request $request)
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
            $listMeterProblem = ListMeterProblem::orderBy('Reason', 'ASC')->get();

            foreach($listMeterProblem as $meterProblem){
                $alldata = array();
                $alldata['reasonCode'] = $meterProblem->ReasonCode;
                $alldata['reason'] = $meterProblem->Reason;
                
                $responsedata[] = $alldata;
            }

            
            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get all ListMeterProblem',
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
