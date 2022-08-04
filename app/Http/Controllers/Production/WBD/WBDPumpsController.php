<?php

namespace App\Http\Controllers\Production\WBD;

use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Services\TokenService;
use Carbon\Carbon;

use App\Models\WBD\WBDPumps;

class WBDPumpsController extends Controller
{

    public function getWBDPumps(Request $request)
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
            $arrPumps = WBDPumps::leftJoin('WBDPumpType', 'WBDPumps.PumpTypeID', '=', 'WBDPumpType.PumpTypeID')
                            ->where('PumpDateOut', '=', null)
                            ->where('PumpDateIn', '<>', null)
                            ->select('WBDPumps.*', 'WBDPumpType.PumpTypeName')
                            ->orderBy('PumpDateIn', 'DESC')
                            ->get();            

            foreach ($arrPumps as $pump) {

                $alldata = array();

                $alldata['pumpID'] = $pump->PumpID;
                $alldata['lease'] = $pump->WBLeaseID;
                $alldata['wellNum'] = $pump->WBLeaseWell;
                $alldata['pumpTypeName'] = $pump->PumpTypeName;
                $alldata['pumpDesc'] = $pump->PumpDesc;
                $alldata['pumpDateIn'] = $pump->PumpDateIn == null ? null : Carbon::parse($pump->PumpDateIn)->format('m/d/Y H:i:s');
                
                $alldata['infoSource'] = $pump->InfoSource;
                $alldata['infoNotes'] = $pump->InfoNotes;
               
                $responsedata[] = $alldata;
            }

            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get WBDPumps',
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
