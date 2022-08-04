<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Services\TokenService;

use App\Models\ListWellProblem;

class ListWellProblemController extends Controller
{
    /**
     * Get ListWellProblem from ListWellProblem table.
     *
     * @return json response.
     */
    public function getListWellProblem(Request $request)
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
            $listWellProblem = ListWellProblem::orderBy('Reason', 'ASC')->get();

            foreach($listWellProblem as $wellProblem){
                $alldata = array();
                $alldata['reasonCode'] = $wellProblem->ReasonCode;
                $alldata['reason'] = $wellProblem->Reason;

                $responsedata[] = $alldata;
            }

            
            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get all ListWellProblem',
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
