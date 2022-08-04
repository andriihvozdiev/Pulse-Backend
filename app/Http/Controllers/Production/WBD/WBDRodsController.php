<?php

namespace App\Http\Controllers\Production\WBD;

use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Services\TokenService;
use Carbon\Carbon;

use App\Models\WBD\WBDRods;

class WBDRodsController extends Controller
{

    public function getWBDRods(Request $request)
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
            $arrWBDRods = WBDRods::leftJoin('ListLength', 'WBDRods.LengthID', '=', 'ListLength.LengthID')
                            ->leftJoin('ListDiameters', 'WBDRods.ExtSizeID', '=', 'ListDiameters.SizeID')
                            ->leftJoin('ListRodCoupling', 'WBDRods.CouplingID', '=', 'ListRodCoupling.CouplingID')
                            ->leftJoin('tblRodType', 'WBDRods.RodTypeID', '=', 'tblRodType.RodTypeID')
                            ->where('WBRodsDtOut', '=', null)
                            ->select('WBDRods.*', 'ListLength.Length as Length', 'ListDiameters.Size as ExtSize', 
                                'ListRodCoupling.CouplingCode as CouplingCode', 'tblRodType.RodType as RodType')
                            ->orderBy('WBRodsDtIn', 'DESC')->get();            

            foreach ($arrWBDRods as $wbdRod) {

                $alldata = array();

                $alldata['wbRodsID'] = $wbdRod->WBRodsID;
                $alldata['lease'] = $wbdRod->WBLease;
                $alldata['wellNum'] = $wbdRod->WBWell;
                $alldata['segmentID'] = $wbdRod->WBSegmentID;
                $alldata['segmentOrder'] = $wbdRod->WBSegmentOrder;

                $alldata['wbRodsQty'] = $wbdRod->WBRodsQty;
                $alldata['wbRodsDesc'] = $wbdRod->WBRodsDesc;
                $alldata['wbRodsDtIn'] = $wbdRod->WBRodsDtIn == null ? null : Carbon::parse($wbdRod->WBRodsDtIn)->format('m/d/Y H:i:s');
                // $alldata['wbRodsDtOut'] = $wbdRod->WBRodsDtOut == null ? null : Carbon::parse($wbdRod->WBRodsDtOut)->format('m/d/Y H:i:s');
                $alldata['invRodsQty'] = $wbdRod->InvRodsQty;
                $alldata['length'] = $wbdRod->Length;
                $alldata['extSizeID'] = $wbdRod->ExtSizeID;
                $alldata['extSize'] = $wbdRod->ExtSize;
                $alldata['couplingCode'] = $wbdRod->CouplingCode;
                $alldata['rodType'] = $wbdRod->RodType;
                $alldata['infoSource'] = $wbdRod->InfoSource;
                
                
                $responsedata[] = $alldata;
            }

            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get WBDRods',
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
