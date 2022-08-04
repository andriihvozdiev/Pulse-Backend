<?php

namespace App\Http\Controllers\Production;

use DB;

use App\Http\Controllers\Controller;
use App\Services\TokenService;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

use App\Models\TankGaugeEntry;
use App\Models\Tanks;
use App\Models\TankStrappings;

class TankDataController extends Controller
{

    protected function validator(Request $request)
    {
        return Validator::make($request->all(), [
            'userid' => 'required',
            'routes' => 'required',
            'operatings' => 'required',
            'isemptypermission' => 'required'
        ]);
    }


    /**
     * Get TankGaugeEntry from TankGaugeEntry table.
     *
     * @return json response.
     */
    public function getTankGaugeEntry(Request $request)
    {

        $data = array();
        $responsedata = array();
        $status = 200;

        $sort_validator = $this->validator($request);
        if ($sort_validator->fails()) {
            $status = 400;
            $data = array(
                'status' => 400,
                'success' => false,
                'message' => 'Wrong params',
                'data' => $sort_validator->errors()
            );
            return response(json_encode($data), $status);
        }

        $userid = $request->userid;
        $routes = $request->routes;
        $operatings = $request->operatings;
        $owns = $request->owns;
        $isemptypermission = $request->isemptypermission;
        
        $token = '';
        $daysToSync = 7;
        $headers = apache_request_headers();
        foreach ($headers as $header => $value) {
            if (strcasecmp($header, 'accesstoken') == 0) {
                $token = $value;                
            }
            if (strcasecmp($header, 'DaysToSync') == 0) {
                $daysToSync = $value;
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
            
            $sqlTGE = "SELECT TankGaugeEntry.* FROM TankGaugeEntry 
                INNER JOIN ListAssetLocations ON TankGaugeEntry.Lease = ListAssetLocations.GrandparentPropNum 
                WHERE ListAssetLocations.Route IN (" . implode(",", $routes) . ") 
                    AND ListAssetLocations.OpCompany IN ('" . implode("','", $operatings) . "') 
                    AND ListAssetLocations.OwnCompany IN ('" . implode("','", $owns) . "') 
                    AND GaugeTime >= DATEADD(day, -" . $daysToSync . ", GETDATE())";

            $sqlLastTGE = "SELECT * FROM TankGaugeEntry n WHERE GaugeTime = (SELECT MAX(GaugeTime) as GaugeTime FROM TankGaugeEntry GROUP BY Lease HAVING Lease=n.Lease)";

            $sql = $sqlTGE . " UNION " . $sqlLastTGE . " ORDER BY GaugeTime DESC";

            $tankGaugeEntries = DB::select($sql);
            
            $totalcount = count($tankGaugeEntries);
            error_log($totalcount);
            foreach($tankGaugeEntries as $tankGaugeEntry)
            {
                $alldata = array();
                $tankGaugeEntry = get_object_vars($tankGaugeEntry);

                $alldata['tankGaugeID'] =  $tankGaugeEntry['TankGaugeID'];
                $alldata['gaugeTime'] = $tankGaugeEntry['GaugeTime'] == null ? null : Carbon::parse($tankGaugeEntry['GaugeTime'])->format('m/d/Y H:i:s');
                $alldata['entryTime'] = $tankGaugeEntry['EntryTime'] == null ? null : Carbon::parse($tankGaugeEntry['EntryTime'])->format('m/d/Y H:i:s');
                $alldata['deviceID'] = $tankGaugeEntry['DeviceID'];
                $alldata['lease'] = $tankGaugeEntry['Lease'];
                $alldata['comments'] = $tankGaugeEntry['Comments'];
                $alldata['tankID1'] = $tankGaugeEntry['TankID1'];
                $alldata['tankID2'] = $tankGaugeEntry['TankID2'];
                $alldata['tankID3'] = $tankGaugeEntry['TankID3'];
                $alldata['tankID4'] = $tankGaugeEntry['TankID4'];
                $alldata['tankID5'] = $tankGaugeEntry['TankID5'];
                $alldata['tankID6'] = $tankGaugeEntry['TankID6'];
                $alldata['tankID7'] = $tankGaugeEntry['TankID7'];
                $alldata['tankID8'] = $tankGaugeEntry['TankID8'];
                $alldata['tankID9'] = $tankGaugeEntry['TankID9'];
                $alldata['tankID10'] = $tankGaugeEntry['TankID10'];

                $alldata['oilFeet1'] = $tankGaugeEntry['OilFeet1'];
                $alldata['oilFeet2'] = $tankGaugeEntry['OilFeet2'];
                $alldata['oilFeet3'] = $tankGaugeEntry['OilFeet3'];
                $alldata['oilFeet4'] = $tankGaugeEntry['OilFeet4'];
                $alldata['oilFeet5'] = $tankGaugeEntry['OilFeet5'];
                $alldata['oilFeet6'] = $tankGaugeEntry['OilFeet6'];
                $alldata['oilFeet7'] = $tankGaugeEntry['OilFeet7'];
                $alldata['oilFeet8'] = $tankGaugeEntry['OilFeet8'];
                $alldata['oilFeet9'] = $tankGaugeEntry['OilFeet9'];
                $alldata['oilFeet10'] = $tankGaugeEntry['OilFeet10'];                
                               
                
                $alldata['lastGgDt'] = $tankGaugeEntry['LastGgDt'] == null ? null : Carbon::parse($tankGaugeEntry['LastGgDt'])->format('m/d/Y H:i:s');
                $alldata['24HrGgDt'] = $tankGaugeEntry['24HrGgDt'] == null ? null : Carbon::parse($tankGaugeEntry['24HrGgDt'])->format('m/d/Y H:i:s');

                $alldata['calcBbls'] = $tankGaugeEntry['CalcBbls'];
                $alldata['24CalcBbls'] = $tankGaugeEntry['24CalcBbls'];
                $alldata['runTicketVol'] = $tankGaugeEntry['RunTicketVol'];
                $alldata['runTicketVol24'] = $tankGaugeEntry['RunTicketVol24'];
                $alldata['bswVol'] = $tankGaugeEntry['BSWVol'];
                $alldata['bswVol24'] = $tankGaugeEntry['BSWVol24'];
                
                $alldata['onHandOil'] = $tankGaugeEntry['OnHandOil'];
                $alldata['negProdQC'] = $tankGaugeEntry['NegProdQC'];
                $alldata['userid'] = $tankGaugeEntry['UserID'];

                $responsedata[] = $alldata;
            }
            
            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get all Tank Gauge Entry',
                'totalcount' => $totalcount,
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


    /**
     * Get Tanks from Tanks table.
     *
     * @return json response.
     */
    public function getTanks(Request $request)
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
            $tanks = Tanks::get();

            foreach($tanks as $tank)
            {
                $alldata = array();
                $alldata['tankID'] = $tank->TankID;
                $alldata['RRC'] = $tank->RRC;
                $alldata['lease'] = $tank->Lease;
                $alldata['tankType'] = $tank->TankType;
                $alldata['current'] = $tank->Current;

                $responsedata[] = $alldata;
            }
            
            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get all Tanks',
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

    /**
     * Get TankStrappings from TankStrappings table.
     *
     * @return json response.
     */
    public function getTankStrappings(Request $request)
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
            $tankStrappings = TankStrappings::get();

            foreach($tankStrappings as $tankStrapping)
            {
                $alldata = array();
                $alldata['rrc'] = $tankStrapping->RRC;
                $alldata['inc1'] = $tankStrapping->Inc1;
                $alldata['inc2'] = $tankStrapping->Inc2;
                $alldata['inc3'] = $tankStrapping->Inc3;
                $alldata['inc4'] = $tankStrapping->Inc4;
                $alldata['inc5'] = $tankStrapping->Inc5;
                $alldata['inc6'] = $tankStrapping->Inc6;
                $alldata['inc7'] = $tankStrapping->Inc7;
                $alldata['inc8'] = $tankStrapping->Inc8;
                $alldata['inc9'] = $tankStrapping->Inc9;
                $alldata['inc10'] = $tankStrapping->Inc10;

                $responsedata[] = $alldata;
            }

            
            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get all Tank Strappings',
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


    /**
     * Upload TankGaugeEntries to TankGaugeEntry table.
     *
     * @return json response.
     */
    public function uploadTankGaugeEntries(Request $request)
    {

        $data = array();
        $responsedata = array();
        $status = 200;

        $arrEntries = $request->entries;
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

            for ($i = 0; $i < count($arrEntries); $i++) {
                $dicTankGaugeEntry = $arrEntries[$i];

                $tankGaugeID = $dicTankGaugeEntry['tankGaugeID'];
                $entryTime = Carbon::parse($dicTankGaugeEntry['entryTime']);
                $gaugeTime = Carbon::parse($dicTankGaugeEntry['gaugeTime']);
                $lease = $dicTankGaugeEntry['lease'];
                $deviceID = $dicTankGaugeEntry['deviceID'];

                $comment = isset($dicTankGaugeEntry['comment']) ? $dicTankGaugeEntry['comment'] : null;
                $userid = $dicTankGaugeEntry['userid'];

                $tankID1 = isset($dicTankGaugeEntry['tankID1']) ? $dicTankGaugeEntry['tankID1'] : 0;
                $tankID2 = isset($dicTankGaugeEntry['tankID2']) ? $dicTankGaugeEntry['tankID2'] : 0;
                $tankID3 = isset($dicTankGaugeEntry['tankID3']) ? $dicTankGaugeEntry['tankID3'] : 0;
                $tankID4 = isset($dicTankGaugeEntry['tankID4']) ? $dicTankGaugeEntry['tankID4'] : 0;
                $tankID5 = isset($dicTankGaugeEntry['tankID5']) ? $dicTankGaugeEntry['tankID5'] : 0;
                $tankID6 = isset($dicTankGaugeEntry['tankID6']) ? $dicTankGaugeEntry['tankID6'] : 0;
                $tankID7 = isset($dicTankGaugeEntry['tankID7']) ? $dicTankGaugeEntry['tankID7'] : 0;
                $tankID8 = isset($dicTankGaugeEntry['tankID8']) ? $dicTankGaugeEntry['tankID8'] : 0;
                $tankID9 = isset($dicTankGaugeEntry['tankID9']) ? $dicTankGaugeEntry['tankID9'] : 0;
                $tankID10 = isset($dicTankGaugeEntry['tankID10']) ? $dicTankGaugeEntry['tankID10'] : 0;

                $oilFeet1 = isset($dicTankGaugeEntry['oilFeet1']) ? $dicTankGaugeEntry['oilFeet1'] : 0;
                $oilFeet2 = isset($dicTankGaugeEntry['oilFeet2']) ? $dicTankGaugeEntry['oilFeet2'] : 0;
                $oilFeet3 = isset($dicTankGaugeEntry['oilFeet3']) ? $dicTankGaugeEntry['oilFeet3'] : 0;
                $oilFeet4 = isset($dicTankGaugeEntry['oilFeet4']) ? $dicTankGaugeEntry['oilFeet4'] : 0;
                $oilFeet5 = isset($dicTankGaugeEntry['oilFeet5']) ? $dicTankGaugeEntry['oilFeet5'] : 0;
                $oilFeet6 = isset($dicTankGaugeEntry['oilFeet6']) ? $dicTankGaugeEntry['oilFeet6'] : 0;
                $oilFeet7 = isset($dicTankGaugeEntry['oilFeet7']) ? $dicTankGaugeEntry['oilFeet7'] : 0;
                $oilFeet8 = isset($dicTankGaugeEntry['oilFeet8']) ? $dicTankGaugeEntry['oilFeet8'] : 0;
                $oilFeet9 = isset($dicTankGaugeEntry['oilFeet9']) ? $dicTankGaugeEntry['oilFeet9'] : 0;
                $oilFeet10 = isset($dicTankGaugeEntry['oilFeet10']) ? $dicTankGaugeEntry['oilFeet10'] : 0;

                
                $arrTankGaugeEntryList = TankGaugeEntry::where('Lease', $lease)
                    ->where('TankGaugeID', $tankGaugeID)
                    ->get();

                if (!$arrTankGaugeEntryList->isEmpty()) {
                    $oldTankGaugeEntry = $arrTankGaugeEntryList->first();
                    
                    $oldTankGaugeEntry['GaugeTime'] = $gaugeTime;
                    $oldTankGaugeEntry['Lease'] = $lease;
                    $oldTankGaugeEntry['DeviceID'] = $deviceID;
                    $oldTankGaugeEntry['Comments'] = $comment;
                    $oldTankGaugeEntry['UserID'] = $userid;
                    
                    $oldTankGaugeEntry['TankID1'] = $tankID1;
                    $oldTankGaugeEntry['TankID2'] = $tankID2;
                    $oldTankGaugeEntry['TankID3'] = $tankID3;
                    $oldTankGaugeEntry['TankID4'] = $tankID4;
                    $oldTankGaugeEntry['TankID5'] = $tankID5;
                    $oldTankGaugeEntry['TankID6'] = $tankID6;
                    $oldTankGaugeEntry['TankID7'] = $tankID7;
                    $oldTankGaugeEntry['TankID8'] = $tankID8;
                    $oldTankGaugeEntry['TankID9'] = $tankID9;
                    $oldTankGaugeEntry['TankID10'] = $tankID10;

                    $oldTankGaugeEntry['OilFeet1'] = $oilFeet1;
                    $oldTankGaugeEntry['OilFeet2'] = $oilFeet2;
                    $oldTankGaugeEntry['OilFeet3'] = $oilFeet3;
                    $oldTankGaugeEntry['OilFeet4'] = $oilFeet4;
                    $oldTankGaugeEntry['OilFeet5'] = $oilFeet5;
                    $oldTankGaugeEntry['OilFeet6'] = $oilFeet6;
                    $oldTankGaugeEntry['OilFeet7'] = $oilFeet7;
                    $oldTankGaugeEntry['OilFeet8'] = $oilFeet8;
                    $oldTankGaugeEntry['OilFeet9'] = $oilFeet9;
                    $oldTankGaugeEntry['OilFeet10'] = $oilFeet10;

                    $oldTankGaugeEntry['LastGgDt'] = null;
                    $oldTankGaugeEntry['24HrGgDt'] = null;
                    $oldTankGaugeEntry['CalcBbls'] = 0;
                    $oldTankGaugeEntry['24CalcBbls'] = 0;


                    if ($oldTankGaugeEntry->save())
                    {
                        $data = array(
                            'status' => 200,
                            'success' => true,
                            'message' => 'Added a TankGaugeEntry successfully.',
                        ); 
                    } 
                    else 
                    {
                        $status = 400;
                        $data = array(
                            'status' => 400,
                            'success' => true,
                            'message' => 'Failed adding a new TankGaugeEntry.',
                        ); 
                        return response(json_encode($data), $status);
                    }

                    
                } else {
                    $newTankGaugeEntry = new TankGaugeEntry;                    

                    $newTankGaugeEntry['EntryTime'] = $entryTime;
                    $newTankGaugeEntry['GaugeTime'] = $gaugeTime;
                    $newTankGaugeEntry['Lease'] = $lease;
                    $newTankGaugeEntry['DeviceID'] = $deviceID;
                    $newTankGaugeEntry['Comments'] = $comment;
                    $newTankGaugeEntry['UserID'] = $userid;
                    
                    $newTankGaugeEntry['TankID1'] = $tankID1;
                    $newTankGaugeEntry['TankID2'] = $tankID2;
                    $newTankGaugeEntry['TankID3'] = $tankID3;
                    $newTankGaugeEntry['TankID4'] = $tankID4;
                    $newTankGaugeEntry['TankID5'] = $tankID5;
                    $newTankGaugeEntry['TankID6'] = $tankID6;
                    $newTankGaugeEntry['TankID7'] = $tankID7;
                    $newTankGaugeEntry['TankID8'] = $tankID8;
                    $newTankGaugeEntry['TankID9'] = $tankID9;
                    $newTankGaugeEntry['TankID10'] = $tankID10;

                    $newTankGaugeEntry['OilFeet1'] = $oilFeet1;
                    $newTankGaugeEntry['OilFeet2'] = $oilFeet2;
                    $newTankGaugeEntry['OilFeet3'] = $oilFeet3;
                    $newTankGaugeEntry['OilFeet4'] = $oilFeet4;
                    $newTankGaugeEntry['OilFeet5'] = $oilFeet5;
                    $newTankGaugeEntry['OilFeet6'] = $oilFeet6;
                    $newTankGaugeEntry['OilFeet7'] = $oilFeet7;
                    $newTankGaugeEntry['OilFeet8'] = $oilFeet8;
                    $newTankGaugeEntry['OilFeet9'] = $oilFeet9;
                    $newTankGaugeEntry['OilFeet10'] = $oilFeet10;

                    $oldTankGaugeEntry['LastGgDt'] = null;
                    $oldTankGaugeEntry['24HrGgDt'] = null;
                    $oldTankGaugeEntry['CalcBbls'] = 0;
                    $oldTankGaugeEntry['24CalcBbls'] = 0;
                    
                    if ($newTankGaugeEntry->save())
                    {
                        $data = array(
                            'status' => 200,
                            'success' => true,
                            'message' => 'Added a new TankGaugeEntry successfully.',
                        ); 

                    } 
                    else 
                    {
                        $status = 400;
                        $data = array(
                            'status' => 400,
                            'success' => true,
                            'message' => 'Failed adding a new TankGaugeEntry.',
                        ); 
                        return response(json_encode($data), $status);
                    }
                }

            }

            
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
