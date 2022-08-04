<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

use App\Models\ListAssetLocations;
use App\Models\Invoices;
use App\Models\InvoicesDetail;
use App\Models\InvoiceAccount;
use App\Models\InvoicePersonnel;
use App\Models\InvoiceImage;

use App\Services\TokenService;

use DB;

class InvoicesController extends Controller
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
     * Get all invoices data from multiple tables(Invoices, InvoiceDetail, tblInvoicePersonnel, ListAssetLocations).
     *
     * @return json response(uploading result).
     */
    public function getAllInvoices(Request $request)
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
        $isNeededUrlForImage = isset($request['isNeededUrlForImage']) ? true : false;
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
            $arrInvoicesList = array();

            if ($isemptypermission == "true") {
                $arrInvoicesAtBeforeDaysToSync = InvoicesDetail::join('Invoices', 'InvoicesDetail.InvoiceID', '=', 'Invoices.InvoiceID')
                    ->join('tblInvoicePersonnel', 'InvoicesDetail.InvoiceID', '=', 'tblInvoicePersonnel.InvoiceID')
                    ->join('ListAssetLocations', 'Invoices.Lease', '=', 'ListAssetLocations.PropNum')
                    ->where('InvoiceDate', '<', Carbon::now()->subDays($daysToSync))
                    ->where('Invoices.NoBill', '=', '0')
                    ->where(function($query) {
                        $query->where('Invoices.Approval1', '=', '0')
                            ->orWhere('Invoices.Approval2', '=', '0')
                            ->orWhere('Invoices.Export', '=', '0');
                        })
                    ->orderby('InvoiceDate', 'DESC')
                    ->select('Invoices.*', 'ListAssetLocations.*', 'InvoicesDetail.*', 'tblInvoicePersonnel.UserID as personnel_id', 'Invoices.Approval1 as primaryApproval', 'Invoices.Approval2 as secondaryApproval', 'InvoicesDetail.ID as invoiceDetailID', 'tblInvoicePersonnel.ID as invoicePersonnelID')
                    ->get()->all();

                $arrInvoicesAtAfterDaysToSync = InvoicesDetail::join('Invoices', 'InvoicesDetail.InvoiceID', '=', 'Invoices.InvoiceID')
                    ->join('tblInvoicePersonnel', 'InvoicesDetail.InvoiceID', '=', 'tblInvoicePersonnel.InvoiceID')
                    ->join('ListAssetLocations', 'Invoices.Lease', '=', 'ListAssetLocations.PropNum')
                    ->whereBetween('InvoiceDate', [Carbon::now()->subDays($daysToSync), Carbon::now()])
                    ->orderby('InvoiceDate', 'DESC')
                    ->select('Invoices.*', 'ListAssetLocations.*', 'InvoicesDetail.*', 'tblInvoicePersonnel.UserID as personnel_id', 'Invoices.Approval1 as primaryApproval', 'Invoices.Approval2 as secondaryApproval', 'InvoicesDetail.ID as invoiceDetailID', 'tblInvoicePersonnel.ID as invoicePersonnelID')
                    ->get()->all();
                
                $arrInvoicesList = array_merge($arrInvoicesAtAfterDaysToSync, $arrInvoicesAtBeforeDaysToSync);
                // $arrInvoicesList = $arrInvoicesAfterBeforeDaysToSync;
                // if (count($arrInvoicesList) == 0) {
                //     $arrInvoicesList = InvoicesDetail::join('Invoices', 'InvoicesDetail.InvoiceID', '=', 'Invoices.InvoiceID')
                //         ->join('tblInvoicePersonnel', 'InvoicesDetail.InvoiceID', '=', 'tblInvoicePersonnel.InvoiceID')
                //         ->join('ListAssetLocations', 'Invoices.Lease', '=', 'ListAssetLocations.PropNum')
                //         ->whereBetween('InvoiceDate', [Carbon::now()->subDays(365), Carbon::now()])
                //         ->orderby('InvoiceDate', 'DESC')
                //         ->select('Invoices.*', 'ListAssetLocations.*', 'InvoicesDetail.*', 'tblInvoicePersonnel.UserID as personnel_id', 'Invoices.Approval1 as primaryApproval', 'Invoices.Approval2 as secondaryApproval', 'InvoicesDetail.ID as invoiceDetailID', 'tblInvoicePersonnel.ID as invoicePersonnelID')
                //         ->take(1)
                //         ->get();
                // }
            } else {

                $arrInvoicesAtBeforeDaysToSync = InvoicesDetail::join('Invoices', 'InvoicesDetail.InvoiceID', '=', 'Invoices.InvoiceID')
                    ->join('tblInvoicePersonnel', 'InvoicesDetail.InvoiceID', '=', 'tblInvoicePersonnel.InvoiceID')
                    ->join('ListAssetLocations', 'Invoices.Lease', '=', 'ListAssetLocations.PropNum')
                    ->where(function($query) use($routes, $operatings, $owns){
                        $query->whereIn('ListAssetLocations.Route', $routes)
                            ->whereIn('ListAssetLocations.OpCompany', $operatings)           
                            ->whereIn('ListAssetLocations.OwnCompany', $owns);
                        })
                    ->where('InvoiceDate', '<', Carbon::now()->subDays($daysToSync))
                    ->where('Invoices.NoBill', '=', '0')
                    ->where(function($query) {
                        $query->where('Invoices.Approval1', '=', '0')
                            ->orWhere('Invoices.Approval2', '=', '0')
                            ->orWhere('Invoices.Export', '=', '0');
                        })
                    ->orderby('InvoiceDate', 'DESC')
                    ->select('Invoices.*', 'ListAssetLocations.*', 'InvoicesDetail.*', 'tblInvoicePersonnel.UserID as personnel_id', 'Invoices.Approval1 as primaryApproval', 'Invoices.Approval2 as secondaryApproval', 'InvoicesDetail.ID as invoiceDetailID', 'tblInvoicePersonnel.ID as invoicePersonnelID')
                    ->get()->all();

                $arrInvoicesAtAfterDaysToSync = InvoicesDetail::join('Invoices', 'InvoicesDetail.InvoiceID', '=', 'Invoices.InvoiceID')
                    ->join('tblInvoicePersonnel', 'InvoicesDetail.InvoiceID', '=', 'tblInvoicePersonnel.InvoiceID')
                    ->join('ListAssetLocations', 'Invoices.Lease', '=', 'ListAssetLocations.PropNum')
                    ->where(function($query) use($routes, $operatings, $owns){
                        $query->whereIn('ListAssetLocations.Route', $routes)
                            ->whereIn('ListAssetLocations.OpCompany', $operatings)           
                            ->whereIn('ListAssetLocations.OwnCompany', $owns);
                        })
                    ->whereBetween('InvoiceDate', [Carbon::now()->subDays($daysToSync), Carbon::now()])
                    ->orderby('InvoiceDate', 'DESC')
                    ->select('Invoices.*', 'ListAssetLocations.*', 'InvoicesDetail.*', 'tblInvoicePersonnel.UserID as personnel_id', 'Invoices.Approval1 as primaryApproval', 'Invoices.Approval2 as secondaryApproval', 'InvoicesDetail.ID as invoiceDetailID', 'tblInvoicePersonnel.ID as invoicePersonnelID')
                    ->get()->all();
                
                $arrInvoicesList = array_merge($arrInvoicesAtAfterDaysToSync, $arrInvoicesAtBeforeDaysToSync);
                
                // if (count($arrInvoicesList) == 0) {
                //     $arrInvoicesList = InvoicesDetail::join('Invoices', 'InvoicesDetail.InvoiceID', '=', 'Invoices.InvoiceID')
                //         ->join('tblInvoicePersonnel', 'InvoicesDetail.InvoiceID', '=', 'tblInvoicePersonnel.InvoiceID')
                //         ->join('ListAssetLocations', 'Invoices.Lease', '=', 'ListAssetLocations.PropNum')
                //         ->where(function($query) use($routes, $operatings, $owns){
                //             $query->whereIn('ListAssetLocations.Route', $routes)
                //                 ->whereIn('ListAssetLocations.OpCompany', $operatings)           
                //                 ->whereIn('ListAssetLocations.OwnCompany', $owns);
                //         })
                //         ->whereBetween('InvoiceDate', [Carbon::now()->subDays(365), Carbon::now()])
                //         ->orderby('InvoiceDate', 'DESC')
                //         ->select('Invoices.*', 'ListAssetLocations.*', 'InvoicesDetail.*', 'tblInvoicePersonnel.UserID as personnel_id', 'Invoices.Approval1 as primaryApproval', 'Invoices.Approval2 as secondaryApproval', 'InvoicesDetail.ID as invoiceDetailID', 'tblInvoicePersonnel.ID as invoicePersonnelID')
                //         ->take(1)
                //         ->get();
                // }
            }

            if (empty($arrInvoicesList))
            {
                $status = 400;
                $data = array(
                    'status' => 400,
                    'success' => false,
                    'message' => 'Empty data.'
                );

                return response(json_encode($data), $status);
            }


            foreach($arrInvoicesList as $invoice){

                $alldata = array();

                $alldata['invoiceID'] = $invoice->InvoiceID;
                $alldata['invoiceAppID'] = $invoice->InvoiceAppID;
                $alldata['lease'] = $invoice->Lease;
                $alldata['dailyCost'] = $invoice->DailyCost;
                $alldata['totalCost'] = $invoice->TotalCost;
                
                $alldata['invoiceDate'] = Carbon::parse($invoice->InvoiceDate)->format('m/d/Y H:i:s');
                
                $alldata['wellNumber'] = $invoice->WellNumber;
                $alldata['route'] = $invoice->Route;
                $alldata['opCompany'] = $invoice->OpCompany;
                $alldata['ownCompany'] = $invoice->OwnCompany;

                $alldata['export'] = $invoice->Export;

                $alldata['approval0'] = $invoice->Approval0;
                $alldata['approval1'] = $invoice->primaryApproval;
                $alldata['approval2'] = $invoice->secondaryApproval;
                $alldata['outsideBill'] = $invoice->OutsideBill;
                $alldata['noBill'] = $invoice->NoBill;

                $alldata['approvalDt0'] = $invoice->ApprovalDt0 == null ? null : Carbon::parse($invoice->ApprovalDt0)->format('m/d/Y H:i:s');
                $alldata['approvalDt1'] = $invoice->ApprovalDt1 == null ? null : Carbon::parse($invoice->ApprovalDt1)->format('m/d/Y H:i:s');
                $alldata['approvalDt2'] = $invoice->ApprovalDt2 == null ? null : Carbon::parse($invoice->ApprovalDt2)->format('m/d/Y H:i:s');
                $alldata['outsideBillDt'] = $invoice->OutsideBillDt == null ? null : Carbon::parse($invoice->OutsideBillDt)->format('m/d/Y H:i:s');
                $alldata['noBillDt'] = $invoice->NoBillDt == null ? null : Carbon::parse($invoice->NoBillDt)->format('m/d/Y H:i:s');

                $alldata['app0Emp'] = $invoice->App0Emp;
                $alldata['app1Emp'] = $invoice->App1Emp;
                $alldata['app2Emp'] = $invoice->App2Emp;
                $alldata['outsideBillEmp'] = $invoice->OutsideBillEmp;
                $alldata['noBillEmp'] = $invoice->NoBillEmp;

                $alldata['deviceID'] = $invoice->DeviceID;
                $alldata['comments'] = $invoice->Comments;
                $alldata['userid'] = $invoice->UserID;

                $alldata['account'] = $invoice->Account;
                $alldata['accountSub'] = $invoice->AccountSub;
                $alldata['accountTime'] = $invoice->AccountTime;
                $alldata['accountUnit'] = $invoice->AccountUnit;

                $alldata['peopleid'] = $invoice->personnel_id;
                $alldata['invoiceDetailID'] = $invoice->invoiceDetailID;
                $alldata['invoicePersonnelID'] = $invoice->invoicePersonnelID;

                $alldata['tubingComments'] = $invoice->TubingComments;
                $alldata['rodComments'] = $invoice->RodComments;
                $alldata['company'] = $invoice->Company;
                $tmpImageData = array();
                foreach($invoice->invoiceImages as $imageData) {
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
                $alldata['invoiceImages'] = $tmpImageData;
                $responsedata[] = $alldata;
            }
            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get all Invoices',
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

    /**
     * Get invoices data from multiple tables(Invoices, InvoiceDetail, tblInvoicePersonnel, ListAssetLocations)
     * within conditions (Approval1=0 (False) or Approval2=0 (False) or Export=0 (False)) AND (NoBill=0).
     * @return json response(uploading result).
     */
    // public function getSpecificInvoices(Request $request) {
    //     $data = array();
    //     $responsedata = array();
    //     $status = 200;

    //     $sort_validator = $this->validator($request);
    //     if ($sort_validator->fails()) {
    //         $status = 400;
    //         $data = array(
    //             'status' => 400,
    //             'success' => false,
    //             'message' => 'Wrong params',
    //             'data' => $sort_validator->errors()
    //         );
    //         return response(json_encode($data), $status);
    //     }
        
    //     $userid = $request->userid;
    //     $routes = $request->routes;
    //     $operatings = $request->operatings;
    //     $owns = $request->owns;
    //     $isemptypermission = $request->isemptypermission;

    //     $token = '';
    //     $headers = apache_request_headers();
    //     foreach ($headers as $header => $value) {
    //         if (strcasecmp($header, 'accesstoken') == 0) {
    //             $token = $value;                
    //         }
    //     }

    //     if (!(TokenService::validateToken($token, $userid))) {
    //         $status = 401;
    //         $data = array(
    //             'status' => 401,
    //             'success' => false,
    //             'message' => 'Unathorized'
    //         );
    //         return response(json_encode($data), $status);
    //     }

    //     try {
    //         $arrInvoicesList = array();

    //         if ($isemptypermission == "true") {
    //             $arrInvoicesList = InvoicesDetail::join('Invoices', 'InvoicesDetail.InvoiceID', '=', 'Invoices.InvoiceID')
    //                 ->join('tblInvoicePersonnel', 'InvoicesDetail.InvoiceID', '=', 'tblInvoicePersonnel.InvoiceID')
    //                 ->join('ListAssetLocations', 'Invoices.Lease', '=', 'ListAssetLocations.PropNum')
    //                 ->orderby('InvoiceDate', 'DESC')
    //                 ->select('Invoices.*', 'ListAssetLocations.*', 'InvoicesDetail.*', 'tblInvoicePersonnel.UserID as personnel_id', 'Invoices.Approval1 as primaryApproval', 'Invoices.Approval2 as secondaryApproval', 'InvoicesDetail.ID as invoiceDetailID', 'tblInvoicePersonnel.ID as invoicePersonnelID')
    //                 ->where('Invoices.NoBill', '=', '0')
    //                 ->where(function($query) {
    //                     $query->where('Invoices.Approval1', '=', '0')
    //                         ->orWhere('Invoices.Approval2', '=', '0')
    //                         ->orWhere('Invoices.Export', '=', '0');
    //                 })
    //                 ->get();
    //         } else {
    //             $arrInvoicesList = InvoicesDetail::join('Invoices', 'InvoicesDetail.InvoiceID', '=', 'Invoices.InvoiceID')
    //                 ->join('tblInvoicePersonnel', 'InvoicesDetail.InvoiceID', '=', 'tblInvoicePersonnel.InvoiceID')
    //                 ->join('ListAssetLocations', 'Invoices.Lease', '=', 'ListAssetLocations.PropNum')
    //                 ->where(function($query) use($routes, $operatings, $owns){
    //                     $query->whereIn('ListAssetLocations.Route', $routes)
    //                         ->whereIn('ListAssetLocations.OpCompany', $operatings)           
    //                         ->whereIn('ListAssetLocations.OwnCompany', $owns);
    //                 })
    //                 ->where('Invoices.NoBill', '=', '0')
    //                 ->where(function($query) {
    //                     $query->where('Invoices.Approval1', '=', '0')
    //                         ->orWhere('Invoices.Approval2', '=', '0')
    //                         ->orWhere('Invoices.Export', '=', '0');
    //                 })
    //                 ->orderby('InvoiceDate', 'DESC')
    //                 ->select('Invoices.*', 'ListAssetLocations.*', 'InvoicesDetail.*', 'tblInvoicePersonnel.UserID as personnel_id', 'Invoices.Approval1 as primaryApproval', 'Invoices.Approval2 as secondaryApproval', 'InvoicesDetail.ID as invoiceDetailID', 'tblInvoicePersonnel.ID as invoicePersonnelID')
    //                 ->get();
    //         }

    //         if (empty($arrInvoicesList))
    //         {
    //             $status = 400;
    //             $data = array(
    //                 'status' => 400,
    //                 'success' => false,
    //                 'message' => 'Empty data.'
    //             );

    //             return response(json_encode($data), $status);
    //         }


    //         foreach($arrInvoicesList as $invoice){

    //             $alldata = array();

    //             $alldata['invoiceID'] = $invoice->InvoiceID;
    //             $alldata['invoiceAppID'] = $invoice->InvoiceAppID;
    //             $alldata['lease'] = $invoice->Lease;
                
    //             $alldata['invoiceDate'] = Carbon::parse($invoice->InvoiceDate)->format('m/d/Y H:i:s');
                
    //             $alldata['wellNumber'] = $invoice->WellNumber;
    //             $alldata['route'] = $invoice->Route;
    //             $alldata['opCompany'] = $invoice->OpCompany;
    //             $alldata['ownCompany'] = $invoice->OwnCompany;

    //             $alldata['export'] = $invoice->Export;

    //             $alldata['approval0'] = $invoice->Approval0;
    //             $alldata['approval1'] = $invoice->primaryApproval;
    //             $alldata['approval2'] = $invoice->secondaryApproval;
    //             $alldata['outsideBill'] = $invoice->OutsideBill;
    //             $alldata['noBill'] = $invoice->NoBill;

    //             $alldata['approvalDt0'] = $invoice->ApprovalDt0 == null ? null : Carbon::parse($invoice->ApprovalDt0)->format('m/d/Y H:i:s');
    //             $alldata['approvalDt1'] = $invoice->ApprovalDt1 == null ? null : Carbon::parse($invoice->ApprovalDt1)->format('m/d/Y H:i:s');
    //             $alldata['approvalDt2'] = $invoice->ApprovalDt2 == null ? null : Carbon::parse($invoice->ApprovalDt2)->format('m/d/Y H:i:s');
    //             $alldata['outsideBillDt'] = $invoice->OutsideBillDt == null ? null : Carbon::parse($invoice->OutsideBillDt)->format('m/d/Y H:i:s');
    //             $alldata['noBillDt'] = $invoice->NoBillDt == null ? null : Carbon::parse($invoice->NoBillDt)->format('m/d/Y H:i:s');

    //             $alldata['app0Emp'] = $invoice->App0Emp;
    //             $alldata['app1Emp'] = $invoice->App1Emp;
    //             $alldata['app2Emp'] = $invoice->App2Emp;
    //             $alldata['outsideBillEmp'] = $invoice->OutsideBillEmp;
    //             $alldata['noBillEmp'] = $invoice->NoBillEmp;

    //             $alldata['deviceID'] = $invoice->DeviceID;
    //             $alldata['comments'] = $invoice->Comments;
    //             $alldata['userid'] = $invoice->UserID;

    //             $alldata['account'] = $invoice->Account;
    //             $alldata['accountSub'] = $invoice->AccountSub;
    //             $alldata['accountTime'] = $invoice->AccountTime;
    //             $alldata['accountUnit'] = $invoice->AccountUnit;

    //             $alldata['peopleid'] = $invoice->personnel_id;
    //             $alldata['invoiceDetailID'] = $invoice->invoiceDetailID;
    //             $alldata['invoicePersonnelID'] = $invoice->invoicePersonnelID;

    //             $alldata['tubingComments'] = $invoice->TubingComments;
    //             $alldata['rodComments'] = $invoice->RodComments;
    //             $alldata['company'] = $invoice->Company;
                
    //             $responsedata[] = $alldata;
    //         }

            
    //         $data = array(
    //             'status' => 200,
    //             'success' => true,
    //             'message' => 'Get all Invoices',
    //             'data' => $responsedata,
    //         ); 
            
    //     } catch (Illuminate\Database\QueryException $e) {
    //         $status = 400;
    //         $data = array(
    //             'status' => 400,
    //             'success' => false,
    //             'message' => 'Invalid Arguments.'
    //         );
    //     }

    //     return response(json_encode($data), $status);
    // }


    /**
     * Upload Invoices table.
     *
     * @return json response(uploading result).
     */
    public function uploadInvoices(Request $request)
    {
        $data = array();
        $status = 200;

        $arrInvoices = $request->invoices;
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
            
            for ($i = 0; $i < count($arrInvoices); $i++) {
                $dicInvoice = $arrInvoices[$i];

                $invoiceID = $dicInvoice['invoiceID'];
                $invoiceAppID = $dicInvoice['invoiceAppID'];
                $lease = $dicInvoice['lease'];
                $wellNumber = isset($dicInvoice['wellNumber']) ? $dicInvoice['wellNumber'] : null;
                $invoiceDate = $dicInvoice['invoiceDate'];
                $deviceID = $dicInvoice['deviceID'];
                $dailyCost = $dicInvoice['dailyCost'];
                $totalCost = $dicInvoice['totalCost'];
                $comments = isset($dicInvoice['comments']) ? $dicInvoice['comments'] : null;
                $userid = $dicInvoice['userid'];
                $invoiceImages = isset($dicInvoice['invoiceImages']) ? $dicInvoice['invoiceImages'] : null;
                $isSaveDemand = isset($dicInvoice['isSaveDemand']) ? true : false;
                $approval0 = $dicInvoice['approval0'];
                $approval1 = $dicInvoice['approval1'];
                $approval2 = $dicInvoice['approval2'];
                $outsideBill = $dicInvoice['outsideBill'];
                $noBill = $dicInvoice['noBill'];

                $approvalDt0 = isset($dicInvoice['approvalDt0']) ? Carbon::parse($dicInvoice['approvalDt0']) : null;
                $approvalDt1 = isset($dicInvoice['approvalDt1']) ? Carbon::parse($dicInvoice['approvalDt1']) : null;
                $approvalDt2 = isset($dicInvoice['approvalDt2']) ? Carbon::parse($dicInvoice['approvalDt2']) : null;
                $outsideBillDt = isset($dicInvoice['outsideBillDt']) ? Carbon::parse($dicInvoice['outsideBillDt']) : null;
                $noBillDt = isset($dicInvoice['noBillDt']) ? Carbon::parse($dicInvoice['noBillDt']) : null;

                $app0Emp = isset($dicInvoice['app0Emp']) ? $dicInvoice['app0Emp'] : null;
                $app1Emp = isset($dicInvoice['app1Emp']) ? $dicInvoice['app1Emp'] : null;
                $app2Emp = isset($dicInvoice['app2Emp']) ? $dicInvoice['app2Emp'] : null;
                $outsideBillEmp = isset($dicInvoice['outsideBillEmp']) ? $dicInvoice['outsideBillEmp'] : null;
                $noBillEmp = isset($dicInvoice['noBillEmp']) ? $dicInvoice['noBillEmp'] : null;

                $tubingComments = isset($dicInvoice['tubingComments']) ? $dicInvoice['tubingComments'] : null;
                $rodComments = isset($dicInvoice['rodComments']) ? $dicInvoice['rodComments'] : null;
                $company = isset($dicInvoice['company']) ? $dicInvoice['company'] : null;

                $deleted = isset($dicInvoice['deleted']) ? $dicInvoice['deleted'] : 0;

                if ($deleted == 1) {
                    Invoices::where('InvoiceID', $invoiceID)->delete();
                    continue;
                }

                $existingInvoices = array();
                // if ($invoiceID == 0)
                // {
                //     $existingInvoices = Invoices::where('Lease', $lease)
                //             ->where('WellNumber', $wellNumber)
                //             ->where('Comments', $comments)
                //             ->where('DeviceID', $deviceID)
                //             ->get();
                // } else {
                //     $existingInvoices = Invoices::where('InvoiceID', $invoiceID)
                //             ->get();
                // }
                if ($invoiceID !== 0)
                {
                    $existingInvoices = Invoices::where('InvoiceID', $invoiceID)
                            ->get();
                }

                if (!$existingInvoices->isEmpty()) {
                    $invoice = $existingInvoices->first();                    
                    
                    $invoice->Lease = $lease;
                    $invoice->WellNumber = $wellNumber;
                    $invoice->Comments = $comments;
                    $invoice->DeviceID = $deviceID;
                    $invoice->InvoiceDate = Carbon::parse($invoiceDate);
                    $invoice->InvoiceAppID = $invoiceAppID;
                    $invoice->dailyCost = $dailyCost;
                    $invoice->totalCost = $totalCost;
                    
                    $invoice->Approval0 = $approval0;
                    $invoice->Approval1 = $approval1;
                    $invoice->Approval2 = $approval2;
                    $invoice->OutsideBill = $outsideBill;
                    $invoice->NoBill = $noBill;

                    $invoice->ApprovalDt0 = $approvalDt0;
                    $invoice->ApprovalDt1 = $approvalDt1;
                    $invoice->ApprovalDt2 = $approvalDt2;
                    $invoice->OutsideBillDt = $outsideBillDt;
                    $invoice->NoBillDt = $noBillDt;

                    $invoice->App0Emp = $app0Emp;
                    $invoice->App1Emp = $app1Emp;
                    $invoice->App2Emp = $app2Emp;
                    $invoice->OutsideBillEmp = $outsideBillEmp;
                    $invoice->NoBillEmp = $noBillEmp;

                    $invoice->TubingComments = $tubingComments;
                    $invoice->RodComments = $rodComments;
                    $invoice->Company = $company;
                    if (!($invoice->save()))
                    {
                        $status = 400;
                        $data = array(
                            'status' => 400,
                            'success' => true,
                            'message' => 'Failed updating a Invoice.',
                        ); 
                        return response(json_encode($data), $status);
                    } else {
                        if ($invoiceImages !== null && count($invoiceImages)) {

                            foreach ($invoiceImages as $image) {

                                if (strpos($image['Image'], 'invoiceimageurl') !== false) {
                                    // $dataSets = array(
                                    //     'InvoiceID' => $invoice->InvoiceID,
                                    //     'UserID' => $userid,
                                    //     'UploadDate' => Carbon::now(),
                                    //     'Image' => $image
                                    // );    
                                    // InvoiceImage::insert($dataSets);
                                } else {
                                    // remove old invoice images
                                    $res = InvoiceImage::where('ImageID', $image['ImageID'])
                                        ->delete();
                                    
                                    $imageToSave = str_replace('data:image/png;base64,', '', $image['Image']);
                                    $imageToSave = str_replace(' ', '+', $imageToSave);
                                    $imageName = 'invoiceimageurl' . str_random(50).'.'.'png';
                                    \File::put(public_path(). '/' . 'assets/' . $imageName, base64_decode($imageToSave));
                                    $dataSets = array(
                                        'InvoiceID' => $invoice->InvoiceID,
                                        'UserID' => $userid,
                                        'UploadDate' => Carbon::now(),
                                        'ImageName' => $imageName,
                                        'Image' => $image['Image']
                                    );
                                    InvoiceImage::insert($dataSets);
                                }
                            }
                        }
                    }
                    

                } else {
                    // create new invoice
                    $invoice = new Invoices;

                    if ($userid != 0) {
                        $invoice->UserID = $userid; 
                    }
                    
                    $invoice->DeviceID = $deviceID;
                    $invoice->InvoiceDate = Carbon::parse($invoiceDate);
                    $invoice->EntryDate = Carbon::now();
                    $invoice->Lease = $lease;
                    $invoice->WellNumber = $wellNumber;
                    $invoice->Comments = $comments;
                    $invoice->InvoiceAppID = $invoiceAppID;
                    $invoice->DailyCost = $dailyCost;
                    $invoice->TotalCost = $totalCost;

                    $invoice->Approval0 = $approval0;
                    $invoice->Approval1 = $approval1;
                    $invoice->Approval2 = $approval2;
                    $invoice->OutsideBill = $outsideBill;
                    $invoice->NoBill = $noBill;

                    $invoice->ApprovalDt0 = $approvalDt0;
                    $invoice->ApprovalDt1 = $approvalDt1;
                    $invoice->ApprovalDt2 = $approvalDt2;
                    $invoice->OutsideBillDt = $outsideBillDt;
                    $invoice->NoBillDt = $noBillDt;

                    $invoice->App0Emp = $app0Emp;
                    $invoice->App1Emp = $app1Emp;
                    $invoice->App2Emp = $app2Emp;
                    $invoice->OutsideBillEmp = $outsideBillEmp;
                    $invoice->NoBillEmp = $noBillEmp;

                    $invoice->TubingComments = $tubingComments;
                    $invoice->RodComments = $rodComments;
                    $invoice->Company = $company;
                    if (!($invoice->save()))
                    {
                        $status = 400;
                        $data = array(
                            'status' => 400,
                            'success' => true,
                            'message' => 'Failed adding a new Invoice.',
                        ); 
                        return response(json_encode($data), $status);
                    } else {
                        if ($invoiceImages !== null && count($invoiceImages) > 0) {
                            foreach ($invoiceImages as $image) {
                                $imageDataToSave = str_replace('data:image/png;base64,', '', $image['Image']);
                                $imageDataToSave = str_replace(' ', '+', $imageDataToSave);
                                $imageName = 'invoiceimageurl' . str_random(50).'.'.'png';
                                \File::put(public_path(). '/' . 'assets/' . $imageName, base64_decode($imageDataToSave));
                                $dataSets = array(
                                    'InvoiceID' => $invoice->InvoiceID,
                                    'UserID' => $userid,
                                    'UploadDate' => Carbon::now(),
                                    'Image' => $image['Image'],
                                    'ImageName' => $imageName
                                );
                                InvoiceImage::insert($dataSets);
                            }
                        }
                    }
                }
                
            }

            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Added a new Invoice successfully.'
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
