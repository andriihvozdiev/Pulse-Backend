<?php

namespace App\Http\Controllers\Production\WBD;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TokenService;
use Carbon\Carbon;

use App\Models\WBD\WBDInfo;

class WBDInfoController extends Controller
{

    public function getWBDInfo(Request $request)
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
            $arrInfo = WBDInfo::leftJoin('WBDInfoSource', 'WBDInfo.InfoSource', '=', 'WBDInfoSource.SourceID')
                            ->get();            

            foreach ($arrInfo as $info) {

                $alldata = array();

                $alldata['infoID'] = $info->InfoID;
                $alldata['tblName'] = $info->TblName;
                $alldata['recordID'] = $info->RecordID;
                $alldata['lease'] = $info->Lease;
                $alldata['wellNum'] = $info->WellNum;
                $alldata['infoSource'] = $info->InfoSource;
                $alldata['infoSourceType'] = $info->InfoSourceType;
                $alldata['infoDate'] = $info->InfoDate == null? null : Carbon::parse($info->InfoDate)->format('m/d/Y H:i:s');
                $alldata['infoNotes'] = $info->InfoNotes;
                
                $responsedata[] = $alldata;
            }

            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get WBDInfo',
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
