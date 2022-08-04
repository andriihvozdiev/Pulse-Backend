<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Services\TokenService;

use App\Models\ListAssetLocations;

class ListAssetLocationsController extends Controller
{
    /**
     * Get Lease from ListAssetLocations table.
     *
     * @return json response.
     */
    public function getLeasesWithPermission(Request $request)
    {
        $data = array();
        $responsedata = array();
        $status = 200;

        $routes = $request->routes;
        $operatings = $request->operatings;
        $owns = $request->owns;
        $isemptypermission = $request->isemptypermission;
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
            $arrLeases = array();

            if ($isemptypermission == 'true') {
                
                $arrLeases = ListAssetLocations::where('Active',true)
                        ->orderby('CodeProperty', 'ASC')
                        ->get();  
            } else {
                
                $arrLeases = ListAssetLocations::where(function($query) use($routes, $operatings, $owns){
                            $query->whereIn('Route', $routes)
                                ->whereIn('OpCompany', $operatings)           
                                ->whereIn('OwnCompany', $owns)
                                ->where('Active',true);
                        })->orderby('CodeProperty', 'ASC')
                        ->get();    
            }
            

            foreach ($arrLeases as $leaseData) {

                $alldata = array();

                $alldata['propNum'] = $leaseData->PropNum;
                $alldata['parentPropNum'] = $leaseData->ParentPropNum;
                $alldata['grandparentPropNum'] = $leaseData->GrandparentPropNum;
                $alldata['codeProperty'] = $leaseData->CodeProperty;
                $alldata['propType'] = $leaseData->PropType;

                $responsedata[] = $alldata;
            }

            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get Leases',
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
