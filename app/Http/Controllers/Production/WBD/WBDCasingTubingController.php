<?php

namespace App\Http\Controllers\Production\WBD;

use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Services\TokenService;
use Carbon\Carbon;

use App\Models\WBD\WBDCasingTubing;

class WBDCasingTubingController extends Controller
{

    public function getWBDCasingTubing(Request $request)
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
            $arrCasingTubing = WBDCasingTubing::leftJoin('ListLength', 'WBDCasingTubing.LengthID', '=', 'ListLength.LengthID')
                                    ->leftJoin('ListDiameters', 'WBDCasingTubing.ExtSizeID', '=', 'ListDiameters.SizeID')
                                    ->leftJoin('ListTubularWeights', 'WBDCasingTubing.WeightID', '=', 'ListTubularWeights.TubularWeightID')
                                    ->leftJoin('ListInternalCoating', 'WBDCasingTubing.CoatingID', '=', 'ListInternalCoating.InternalCoatingID')
                                    ->leftJoin('ListThreads', 'WBDCasingTubing.ThreadID', '=', 'ListThreads.ThreadID')
                                    ->leftJoin('ListTubularType', 'WBDCasingTubing.TubularTypeID', '=', 'ListTubularType.TubularTypeID')
                                    ->where('WBDateOut', '=', null)
                                    ->select('WBDCasingTubing.*', 'ListLength.Length as Length', 'ListDiameters.Size as ExtSize', 
                                        'ListTubularWeights.Weight as Weight', 'ListInternalCoating.InternalCoating as Coating', 
                                        'ListThreads.ThreadType as ThreadType', 'ListTubularType.TubularGrade as TubularType')
                                    ->orderBy('WBDateIn', 'DESC')->get();            

            foreach ($arrCasingTubing as $casingTubing) {

                $alldata = array();

                $alldata['wbCasingTubingID'] = $casingTubing->WBCasingTubingID;
                $alldata['lease'] = $casingTubing->WBLease;
                $alldata['wellNum'] = $casingTubing->WBWell;
                $alldata['segmentID'] = $casingTubing->WBSegmentID;
                $alldata['segmentOrder'] = $casingTubing->WBSegmentOrder;

                $alldata['wbQty'] = $casingTubing->WBQty;
                $alldata['wbDateIn'] = $casingTubing->WBDateIn == null ? null : Carbon::parse($casingTubing->WBDateIn)->format('m/d/Y H:i:s');
                // $alldata['wbDateOut'] = $casingTubing->WBDateOut == null ? null : Carbon::parse($casingTubing->WBDateOut)->format('m/d/Y H:i:s');
                $alldata['invQty'] = $casingTubing->InvQty;
                $alldata['wbCasing'] = $casingTubing->WBCasing;
                $alldata['wbTubing'] = $casingTubing->WBTubing;
                $alldata['length'] = $casingTubing->Length;
                $alldata['extSizeID'] = $casingTubing->ExtSizeID;
                $alldata['extSize'] = $casingTubing->ExtSize;
                $alldata['weight'] = $casingTubing->Weight;
                $alldata['coating'] = $casingTubing->Coating;
                $alldata['threadType'] = $casingTubing->ThreadType;
                $alldata['tubularType'] = $casingTubing->TubularType;
                $alldata['invLinker'] = $casingTubing->InvLinker;
                $alldata['startDepth'] = $casingTubing->StartDepth;
                $alldata['endDepth'] = $casingTubing->EndDepth;
                $alldata['cmtSxQty'] = $casingTubing->CmtSxQty;
                $alldata['cmtVolSlurry'] = $casingTubing->CmtVolSlurry;
                $alldata['cmtDesc'] = $casingTubing->CmtDesc;
                $alldata['cmtToC'] = $casingTubing->CmtToC;
                $alldata['cmtCalcToC'] = $casingTubing->CmtCalcToC;
                $alldata['cmtVerifiedToC'] = $casingTubing->CmtVerifiedToC;
                $alldata['cmtVerificationType'] = $casingTubing->CmtVerificationType;
                
                $alldata['infoSource'] = $casingTubing->InfoSource;
                $alldata['infoDate'] = $casingTubing->InfoDate == null ? null : Carbon::parse($casingTubing->InfoDate)->format('m/d/Y H:i:s');

                $responsedata[] = $alldata;
            }

            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get WBDCasingTubing',
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
