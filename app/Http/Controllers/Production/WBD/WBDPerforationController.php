<?php

namespace App\Http\Controllers\Production\WBD;

use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Services\TokenService;
use Carbon\Carbon;

use App\Models\WBD\WBDPerfs;

class WBDPerforationController extends Controller
{

    public function getWBDPerfs(Request $request)
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
            $arrPerforations = WBDPerfs::leftJoin('WBDPerfZones', 'WBDPerfs.PerfZoneID', '=', 'WBDPerfZones.PerfZoneID')
                                ->select('WBDPerfs.*', 'WBDPerfZones.PerfZoneStart', 'WBDPerfZones.PerfZoneEnd')
                                ->get();            

            foreach ($arrPerforations as $perforation) {

                $alldata = array();

                $alldata['perfID'] = $perforation->ID;
                $alldata['lease'] = $perforation->LeaseID;
                $alldata['wellNum'] = $perforation->WellNum;
                $alldata['perfDate'] = $perforation->PerfDate == null ? null : Carbon::parse($perforation->PerfDate)->format('m/d/Y H:i:s');
                $alldata['perfDesc'] = $perforation->PerfDesc; 
                $alldata['wellPerf'] = $perforation->WellPerf;
                $alldata['perfZoneStart'] = $perforation->PerfZoneStart;
                $alldata['perfZoneEnd'] = $perforation->PerfZoneEnd;

                $responsedata[] = $alldata;
            }

            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get WBDPerforations',
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
