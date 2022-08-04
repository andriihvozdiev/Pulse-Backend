<?php

namespace App\Http\Controllers\Production\WBD;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TokenService;
use Carbon\Carbon;

use App\Models\WBD\WBDPlugs;

class WBDPlugsController extends Controller
{

    public function getWBDPlugs(Request $request)
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
            $arrWBDPlugs = WBDPlugs::leftJoin('WBDPlugType', 'WBDPlugs.PlugType', '=', 'WBDPlugType.PlugTypeID')
                            ->leftJoin('WBDPlugModel', 'WBDPlugs.PlugModel', '=', 'WBDPlugModel.PlugModelID')
                            ->where('WellPacker', '=', 0)
                            ->where('PlugDateOut', '=', null)
                            ->orderBy('PlugDepth', 'ASC')->get();            

            foreach ($arrWBDPlugs as $plugs) {

                $alldata = array();

                $alldata['plugID'] = $plugs->ID;
                $alldata['lease'] = $plugs->WBLeaseID;
                $alldata['wellNum'] = $plugs->WBLeaseWell;
                $alldata['plugType'] = $plugs->PlugTypeDesc;
                $alldata['plugModel'] = $plugs->PlugModelDesc;
                $alldata['plugDepth'] = $plugs->PlugDepth;
                $alldata['plugDateIn'] = $plugs->PlugDateIn == null ? null : Carbon::parse($plugs->PlugDateIn)->format('m/d/Y H:i:s');
                $alldata['comments'] = $plugs->Comments;

                $alldata['infoSource'] = $plugs->InfoSource;
                $alldata['infoDate'] = $plugs->InfoDate;
                $alldata['infoNotes'] = $plugs->InfoNotes;

                $responsedata[] = $alldata;
            }

            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get WBDPlugs',
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
