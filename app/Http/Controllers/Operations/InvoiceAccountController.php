<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Services\TokenService;

use App\Models\InvoiceAccount;

class InvoiceAccountController extends Controller
{

    /**
     * Get Invoice Accounts from tblInvoiceAccount table.
     *
     * @return json response.
     */
    public function getInvoiceAccounts(Request $request)
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
            $arrInvoiceAccounts = InvoiceAccount::join('tblInvoiceSubAccount', 'tblInvoiceAccount.AcctID', '=', 'tblInvoiceSubAccount.AcctID')
                                ->get();

            foreach($arrInvoiceAccounts as $invoiceAccount){
                
                $alldata = array();

                $alldata['acctID'] = $invoiceAccount->AcctID;
                $alldata['subAcctID'] = $invoiceAccount->SubAcctID;
                $alldata['account'] = $invoiceAccount->AcctDescription;
                $alldata['subaccount'] = $invoiceAccount->SubAcctDescription;
                $alldata['subacctTimeUnits'] = $invoiceAccount->SubAcctTimeUnits;

                $alldata['WPExpJournal'] = $invoiceAccount->WPExpJournal;
                $alldata['WPExpReference'] = $invoiceAccount->WPExpReference;
                $alldata['WPExpAcct'] = $invoiceAccount->WPExpAcct;
                $alldata['WPIncJournal'] = $invoiceAccount->WPIncJournal;
                $alldata['WPIncReference'] = $invoiceAccount->WPIncReference;
                $alldata['WPIncAcct'] = $invoiceAccount->WPIncAcct;
                $alldata['WPIncSubAcct'] = $invoiceAccount->WPIncSubAcct;
                
                $alldata['UnitCost'] = $invoiceAccount->UnitCost;
                $alldata['UnitCostOutCharge'] = $invoiceAccount->UnitCostOutCharge;
                $alldata['OutbillLookup'] = $invoiceAccount->OutbillLookup;
                                
                $responsedata[] = $alldata;
            }

            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get all accounts',
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
