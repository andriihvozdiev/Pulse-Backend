<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Services\TokenService;

use App\Models\Personnel;

class PersonnelController extends Controller
{
    /**
     * Get personnel from Personnel table.
     *
     * @return json response.
     */
    public function getPersonnel(Request $request)
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
            
            $arrPersonnels = Personnel::get();
            foreach($arrPersonnels as $personnel){
                
                $alldata = array();

                $alldata['userid'] = $personnel->userid;
                $alldata['employeeName'] = $personnel->EmployeeName;
                $alldata['email'] = $personnel->Email;
                $alldata['department'] = $personnel->Department;
                $alldata['active'] = $personnel->Active;
                $alldata['invPersonnel'] = $personnel->InvPersonnel;
                $alldata['primaryApp'] = $personnel->PrimaryApp;
                $alldata['secondaryApp'] = $personnel->SecondaryApp;
                $alldata['outsideBillApp'] = $personnel->OutsideBillApp;
                $alldata['noBillApp'] = $personnel->NoBillApp;

                $responsedata[] = $alldata;
            }

            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get all peoples',
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
