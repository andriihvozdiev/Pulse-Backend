<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

use App\Models\RigReportsModels\RigReports;
use App\Models\RigReportsModels\RigReportsImage;
use App\Services\TokenService;

class RigReportsController extends Controller
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
     * Get all data from tblRigReports.
     *
     * @return json response.
     */
    public function getRigReports(Request $request)
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
        $isNeededUrlForImage = isset($request['isNeededUrlForImage']) ? true : false;
        $isAll = isset($request['isAll']) ? $request['isAll'] : false;
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
            $arrRigReports = array();

            if ($isemptypermission == "true") {
                if ($isAll) {
                    $arrRigReports = RigReports::orderby('ReportDate', 'DESC')
                    ->select('tblRigReports.*')
                    ->get(); 
                } else {
                    $arrRigReports = RigReports::orderby('ReportDate', 'DESC')
                    ->whereBetween('ReportDate', [Carbon::now()->subDays($daysToSync), Carbon::now()])
                    ->select('tblRigReports.*')
                    ->get(); 
                }
            } else {
                if ($isAll) {
                    $arrRigReports = RigReports::join('ListAssetLocations', 'tblRigReports.Lease', '=', 'ListAssetLocations.GrandparentPropNum')
                    ->where(function($query) use($routes, $operatings, $owns){
                        $query->whereIn('ListAssetLocations.Route', $routes)
                            ->whereIn('ListAssetLocations.OpCompany', $operatings)           
                            ->whereIn('ListAssetLocations.OwnCompany', $owns);
                    })
                    ->orderby('ReportDate', 'DESC')
                    ->select('tblRigReports.*')
                    ->get()
                    ->all();
                } else {
                    $arrRigReports = RigReports::join('ListAssetLocations', 'tblRigReports.Lease', '=', 'ListAssetLocations.GrandparentPropNum')
                    ->where(function($query) use($routes, $operatings, $owns){
                        $query->whereIn('ListAssetLocations.Route', $routes)
                            ->whereIn('ListAssetLocations.OpCompany', $operatings)           
                            ->whereIn('ListAssetLocations.OwnCompany', $owns);
                    })
                    ->whereBetween('ReportDate', [Carbon::now()->subDays($daysToSync), Carbon::now()])
                    ->orderby('ReportDate', 'DESC')
                    ->select('tblRigReports.*')
                    ->get()
                    ->all();
                }
            }
            foreach($arrRigReports as $rigReport){

                $alldata = array();

                $alldata['reportID'] = $rigReport->ReportID;
                $alldata['lease'] = $rigReport->Lease;
                $alldata['wellNum'] = $rigReport->WellNum;
                $alldata['company'] = $rigReport->Company;
                $alldata['reportDate'] = $rigReport->ReportDate == null ? null : Carbon::parse($rigReport->ReportDate)->format('m/d/Y H:i:s');
                $alldata['entryUser'] = $rigReport->EntryUser;
                $alldata['entryDate'] = $rigReport->EntryDate == null ? null : Carbon::parse($rigReport->EntryDate)->format('m/d/Y H:i:s');                
                $alldata['reportAppID'] = $rigReport->ReportAppID;
                $alldata['comments'] = $rigReport->Comments;
                $alldata['tubing'] = $rigReport->Tubing;
                $alldata['rods'] = $rigReport->Rods;
                $alldata['engrApproval'] = $rigReport->EngrApproval;
                $alldata['dailyCost'] = $rigReport->DailyCost;
                $alldata['totalCost'] = $rigReport->TotalCost;
                
                $tmpImageData = array();
                foreach($rigReport->rigImages as $imageData) {
                    if ($isNeededUrlForImage) {
                        if ($imageData['ImageName'] !== null) {
                            $dataToPush = array(
                                'Image' => $imageData['ImageName'],
                                'ImageID' => $imageData['ImageID']
                            );
                        } else {
                            $dataToPush = array(
                                'Image' => $imageData['Image'],
                                'ImageID' => $imageData['ImageID']
                            );
                        }
                        
                        array_push($tmpImageData, $dataToPush);
                    } else {
                        $dataToPush = array(
                            'Image' => $imageData['Image'],
                            'ImageID' => $imageData['ImageID']
                        );
                        array_push($tmpImageData, $dataToPush);
                    }
                }
                $alldata['rigImages'] = $tmpImageData;
                $responsedata[] = $alldata;
            }

            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get all RigReports',
                'data' => $responsedata
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

    public function getRigReportsWithWellList(Request $request)
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
        $lease = $request->lease;
        $wellNumber = $request->wellNumber;
        $routes = $request->routes;
        $operatings = $request->operatings;
        $owns = $request->owns;
        $isemptypermission = $request->isemptypermission;
        
        $token = '';
        $isNeededUrlForImage = isset($request['isNeededUrlForImage']) ? true : false;
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
            $arrRigReports = array();

            if ($isemptypermission == "true") {
                $arrRigReports = RigReports::orderby('ReportDate', 'DESC')
                ->where('Lease', $lease)
                ->where('WellNum', $wellNumber)
                ->select('tblRigReports.*')
                ->get(); 
            } else {
                
                $arrRigReports = RigReports::join('ListAssetLocations', 'tblRigReports.Lease', '=', 'ListAssetLocations.GrandparentPropNum')
                ->where(function($query) use($routes, $operatings, $owns){
                    $query->whereIn('ListAssetLocations.Route', $routes)
                        ->whereIn('ListAssetLocations.OpCompany', $operatings)
                        ->whereIn('ListAssetLocations.OwnCompany', $owns);
                })
                ->where('Lease', $lease)
                ->where('WellNum', $wellNumber)
                ->orderby('ReportDate', 'DESC')
                ->select('tblRigReports.*')
                ->get()
                ->all();
            }
            foreach($arrRigReports as $rigReport){

                $alldata = array();
                $alldata['reportID'] = $rigReport->ReportID;
                $alldata['lease'] = $rigReport->Lease;
                $alldata['wellNum'] = $rigReport->WellNum;
                $alldata['company'] = $rigReport->Company;
                $alldata['reportDate'] = $rigReport->ReportDate == null ? null : Carbon::parse($rigReport->ReportDate)->format('m/d/Y H:i:s');
                $alldata['entryUser'] = $rigReport->EntryUser;
                $alldata['entryDate'] = $rigReport->EntryDate == null ? null : Carbon::parse($rigReport->EntryDate)->format('m/d/Y H:i:s');                
                $alldata['reportAppID'] = $rigReport->ReportAppID;
                $alldata['comments'] = $rigReport->Comments;
                $alldata['tubing'] = $rigReport->Tubing;
                $alldata['rods'] = $rigReport->Rods;
                $alldata['engrApproval'] = $rigReport->EngrApproval;
                $alldata['dailyCost'] = $rigReport->DailyCost;
                $alldata['totalCost'] = $rigReport->TotalCost;
                
                $tmpImageData = array();
                foreach($rigReport->rigImages as $imageData) {
                    if ($isNeededUrlForImage) {
                        if ($imageData['ImageName'] !== null) {
                            $dataToPush = array(
                                'Image' => $imageData['ImageName'],
                                'ImageID' => $imageData['ImageID']
                            );
                        } else {
                            $dataToPush = array(
                                'Image' => $imageData['Image'],
                                'ImageID' => $imageData['ImageID']
                            );
                        }
                        
                        array_push($tmpImageData, $dataToPush);
                    } else {
                        $dataToPush = array(
                            'Image' => $imageData['Image'],
                            'ImageID' => $imageData['ImageID']
                        );
                        array_push($tmpImageData, $dataToPush);
                    }
                }
                $alldata['rigImages'] = $tmpImageData;
                $responsedata[] = $alldata;
            }

            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get all RigReports',
                'data' => $responsedata
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


    /**
     * Upload data to tblRigReports.
     *
     * @return json response.
     */
    public function uploadRigReports(Request $request)
    {
        $data = array();
        $responsedata = array();
        $status = 200;

        $arrRigReports = $request->rigReports;

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

            for ($i = 0; $i < count($arrRigReports); $i++) {
                $dicRigReports = $arrRigReports[$i];

                $lease = $dicRigReports['lease'];
                $wellNumber = $dicRigReports['wellNumber'];
                $company = $dicRigReports['company'];
                $dailyCost = isset($dicRigReports['dailyCost']) ? $dicRigReports['dailyCost'] : null;
                $totalCost = isset($dicRigReports['totalCost']) ? $dicRigReports['totalCost'] : null;
                $reportDate = isset($dicRigReports['reportDate']) ? Carbon::parse($dicRigReports['reportDate']) : null;
                $entryUser = $dicRigReports['entryUser'];
                $entryDate = isset($dicRigReports['entryDate']) ? Carbon::parse($dicRigReports['entryDate']) : null;
                $reportAppID = $dicRigReports['reportAppID'];
                $comments = isset($dicRigReports['comments']) ? $dicRigReports['comments'] : null;
                $tubing = isset($dicRigReports['tubing']) ? $dicRigReports['tubing'] : null;
                $rods = isset($dicRigReports['rods']) ? $dicRigReports['rods'] : null;
                $rigImages = isset($dicRigReports['rigImage']) ? $dicRigReports['rigImage'] : null;
                
                $existingRigReports = RigReports::where('Lease', $lease)
                            ->where('WellNum', $wellNumber)
                            ->where('Company', $company)
                            ->where('ReportAppID', $reportAppID)
                            ->get();

                if (!$existingRigReports->isEmpty()) {
                    $oldRigReports = $existingRigReports->first();
                    $report_id = $oldRigReports['ReportID'];
                    $oldRigReports['ReportDate'] = $reportDate;
                    $oldRigReports['EntryUser'] = $entryUser;
                    $oldRigReports['EntryDate'] = $entryDate;
                    $oldRigReports['Comments'] = $comments;
                    $oldRigReports['Tubing'] = $tubing;
                    $oldRigReports['Rods'] = $rods;
                    $oldRigReports['dailyCost'] = $dailyCost;
                    $oldRigReports['totalCost'] = $totalCost;
                    //remove old rigreports images
                    $res = RigReportsImage::where('ReportID', $report_id)
                        ->delete();
                    if ($rigImages !== null) {
                        foreach ($rigImages as $image) {
                            $imageToSave = str_replace('data:image/png;base64,', '', $image['Image']);
                            $imageToSave = str_replace(' ', '+', $imageToSave);
                            $imageName = 'rigimageurl' . str_random(50).'.'.'png';
                            \File::put(public_path(). '/' . 'assets/' . $imageName, base64_decode($imageToSave));

                            $dataSets = array(
                                'ReportID' => $report_id,
                                'UserID' => $userid,
                                'UploadDate' => Carbon::now(),
                                'Image' => $imageToSave,
                                'ImageName' => $imageToSave
                            );
                            RigReportsImage::insert($dataSets);
                        }
                        
                    }
                    if ($oldRigReports->save())
                    {
                        $data = array(
                            'status' => 200,
                            'success' => true,
                            'message' => 'Added a new RigReports successfully.'
                        ); 
                    } 
                    else 
                    {
                        $status = 400;
                        $data = array(
                            'status' => 400,
                            'success' => true,
                            'message' => 'Failed adding a new RigReports.',
                        ); 
                        return response(json_encode($data), $status);
                    }

                    
                } else {
                    $newRigReports = new RigReports;                    

                    $newRigReports['Lease'] = $lease;
                    $newRigReports['WellNum'] = $wellNumber;
                    $newRigReports['Company'] = $company;
                    $newRigReports['ReportDate'] = $reportDate;
                    $newRigReports['EntryUser'] = $entryUser;
                    $newRigReports['EntryDate'] = $entryDate;
                    $newRigReports['ReportAppID'] = $reportAppID;
                    $newRigReports['Comments'] = $comments;
                    $newRigReports['Tubing'] = $tubing;
                    $newRigReports['Rods'] = $rods;
                    $newRigReports['dailyCost'] = $dailyCost;
                    $newRigReports['totalCost'] = $totalCost;
                    if ($newRigReports->save())
                    {
                        $ret = 0;
                        if ($rigImages !== null) {
                            foreach ($rigImages as $image) {

                                $imageToSave = str_replace('data:image/png;base64,', '', $image['Image']);
                                $imageToSave = str_replace(' ', '+', $imageToSave);
                                $imageName = 'rigimageurl' . str_random(50).'.'.'png';
                                \File::put(public_path(). '/' . 'assets/' . $imageName, base64_decode($imageToSave));
                                
                                $dataSets = array(
                                    'ReportID' => $newRigReports->ReportID,
                                    'UserID' => $userid,
                                    'UploadDate' => Carbon::now(),
                                    'Image' => $imageToSave,
                                    'ImageName' => $imageName
                                );

                                if (RigReportsImage::insert($dataSets)) {
                                    $ret++;
                                }
                            }

                        }
                        $data = array(
                            'status' => 200,
                            'success' => true,
                            'message' => 'Added a new RigReports successfully.',
                            'uploaded_count' => count($rigImages),
                            'count' => $ret
                        ); 
                    } 
                    else 
                    {
                        $status = 400;
                        $data = array(
                            'status' => 400,
                            'success' => true,
                            'message' => 'Failed adding a new RigReports.',
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
