<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TokenService;

use App\Models\ScheduleType;

class ScheduleTypeController extends Controller
{
    /**
     * Get ScheduleTypes from tblScheduleType table.
     *
     * @return json response.
     */
    public function getAllScheduleTypes(Request $request)
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
            
            $scheduleTypes = ScheduleType::get();

            foreach($scheduleTypes as $scheduleType){
                
                $alldata = array();

                $alldata['scheduleTypeID'] = $scheduleType->ScheduleTypeID;
                $alldata['type'] = $scheduleType->Type;
                $alldata['category'] = $scheduleType->Category;
                
                $responsedata[] = $alldata;
            }

            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get all Schedule Types',
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
