<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Services\TokenService;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\WellList;

class WellListController extends Controller
{
    /**
     * Get WellList from WellList table.
     *
     * @return json response.
     */
    public function getWellList(Request $request)
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

            $arrWellList = WellList::join('ListAssetLocations', 'WellList.Lease', '=', 'ListAssetLocations.PropNum')
                ->select('WellList.*', 'ListAssetLocations.GrandparentPropNum')
                ->get();

            foreach($arrWellList as $wellList){
                $alldata = array();
                
                $alldata['wellID'] = $wellList->WellID;
                $alldata['lease'] = $wellList->Lease;
                $alldata['wellNumber'] = $wellList->WellNumber;
                $alldata['prodCat'] = $wellList->ProdCat;
                $alldata['RRCLease'] = $wellList->RRCLease;
                $alldata['grandparentPropNum'] = $wellList->GrandparentPropNum;

                $responsedata[] = $alldata;
            }

            
            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get all WellList data',
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
