<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\ListAssetLocations;
use App\Models\Invoices;
use App\Models\InvoicesDetail;
use App\Models\InvoiceAccount;
use App\Models\InvoicePersonnel;

use App\Services\TokenService;

class InvoicesDetailController extends Controller
{

    public function uploadInvoicesDetail(Request $request)
    {
        $data = array();
        $status = 200;

        $arrInvoicesDetail = $request->invoicesDetail;
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
            for ($i = 0; $i < count($arrInvoicesDetail); $i++) {
                $dicInvoiceDetail = $arrInvoicesDetail[$i];

                $invoiceID = $dicInvoiceDetail['invoiceID'];
                $invoiceAppID = isset($dicInvoiceDetail['invoiceAppID']) ? $dicInvoiceDetail['invoiceAppID'] : null;
               
                $account = $dicInvoiceDetail['account'];
                $accountSub = $dicInvoiceDetail['accountSub'];
                $acctTime = $dicInvoiceDetail['accountTime'];
                $acctUnit = isset($dicInvoiceDetail['accountUnit']) ? $dicInvoiceDetail['accountUnit'] : null;
                
                $deleted = isset($dicInvoiceDetail['deleted']) ? $dicInvoiceDetail['deleted'] : 0;

                if ($invoiceID == 0)
                { 
                    try {
                        $invoice = Invoices::where('InvoiceAppID', $invoiceAppID)->firstOrFail();
                        $invoiceID = $invoice->InvoiceID;    
                    } catch (ModelNotFoundException $ex) {
                        $status = 409;
                        $data = array(
                            'status' => 409,
                            'success' => $invoiceAppID,
                            'message' => 'Invalid Argument - InvoiceAppID'
                        );

                       

                    }
                    
                }

                if ($deleted == 1) {
                    InvoicesDetail::where('InvoiceID', $invoiceID)
                        ->where('Account', $account)
                        ->where('AccountSub', $accountSub)
                        ->delete();
                    continue;
                }

                $existingInvoicesDetail = InvoicesDetail::where('InvoiceID', $invoiceID)
                    ->where('Account', $account)
                    ->where('AccountSub', $accountSub)
                    ->get();

                if (!$existingInvoicesDetail->isEmpty()) {
                    
                    $invoiceDetail = $existingInvoicesDetail->first();
                    
                    $invoiceDetail->InvoiceAppID = $invoiceAppID;
                    $invoiceDetail->AccountTime = $acctTime;

                    if ($invoiceDetail->save()) {
                        $status = 200;
                        $data = array(
                            'status' => 200,
                            'success' => true,
                            'message' => 'Changed InvoiceDetail.',
                        ); 
                    } else {
                        $status = 201;
                        $data = array(
                            'status' => 201,
                            'success' => false,
                            'message' => 'Failed change InvoiceDetail.',
                        ); 
                        return response(json_encode($data), $status);
                    }
                    
                } else  {

                    $accountdata = InvoiceAccount::join('tblInvoiceSubAccount', 'tblInvoiceAccount.AcctID', '=', 'tblInvoiceSubAccount.AcctID')
                            ->where('tblInvoiceAccount.AcctID', $account)
                            ->where('tblInvoiceSubAccount.SubAcctID', $accountSub)
                            ->first();

                    $invoicesdetail = new InvoicesDetail;
                    
                    $invoicesdetail->InvoiceID = $invoiceID;
                    $invoicesdetail->InvoiceAppID = $invoiceAppID;
                    $invoicesdetail->Account = $accountdata['AcctID'];
                    $invoicesdetail->AccountSub = $accountdata['SubAcctID'];
                    $invoicesdetail->AccountTime = $acctTime;
                    $invoicesdetail->AccountUnit = $acctUnit;
                    
                    $invoicesdetail->WPExpReference = $accountdata['WPExpReference'];
                    $invoicesdetail->WPExpAcct = $accountdata['WPExpAcct'];
                    $invoicesdetail->WPExpJournal = $accountdata['WPExpJournal'];
                    $invoicesdetail->WPIncReference = $accountdata['WPIncReference'];
                    $invoicesdetail->WPIncAcct = $accountdata['WPIncAcct'];
                    $invoicesdetail->WPIncSubAcct = $accountdata['WPIncSubAcct'];
                    $invoicesdetail->WPIncJournal = $accountdata['WPIncJournal'];
                    $invoicesdetail->UnitCost = $accountdata['UnitCost'];
                    if ($acctUnit==='Ft(s)') {
                        $invoicesdetail->SubTotal = $accountdata['UnitCost'];
                    } elseif ($acctUnit==='Jt(s)') {
                        $invoicesdetail->SubTotal = $accountdata['UnitCost'];
                    } else {
                        $invoicesdetail->SubTotal = $accountdata['UnitCost'] * $acctTime;
                    }

                    if ($invoicesdetail->save()) {
                        $status = 200;
                        $data = array(
                            'status' => 200,
                            'success' => true,
                            'message' => 'Added new InvoiceDetail.',
                        ); 

                    } else {
                        $status = 401;
                        $data = array(
                            'status' => 401,
                            'success' => false,
                            'message' => 'Failed adding new InvoiceDetail.',
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
