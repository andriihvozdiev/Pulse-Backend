<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\TokenService;

use App\Models\Invoices;
use App\Models\InvoicePersonnel;



class InvoicesPersonnelController extends Controller
{

    /**
     * Upload InvoicePersonnel table.
     *
     * @return json response(uploading result).
     */
    public function uploadInvoicesPersonnel(Request $request)
    {
        $data = array();
        $status = 200;

        $arrInvoicesPersonnel = $request->invoicesPersonnel;
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
            for ($i = 0; $i < count($arrInvoicesPersonnel); $i++) {
                $dicInvoicePersonnel = $arrInvoicesPersonnel[$i];

                $invoiceID = $dicInvoicePersonnel['invoiceID'];
                $invoiceAppID = isset($dicInvoicePersonnel['invoiceAppID']) ? $dicInvoicePersonnel['invoiceAppID'] : null;
               
                $peopleID = $dicInvoicePersonnel['peopleID'];
                $deleted = isset($dicInvoicePersonnel['deleted']) ? $dicInvoicePersonnel['deleted'] : 0;

                if ($invoiceID == 0)
                { 
                    try {
                        $invoice = Invoices::where('InvoiceAppID', $invoiceAppID)->firstOrFail();
                        $invoiceID = $invoice->InvoiceID;    
                    } catch (ModelNotFoundException $ex) {
                        $status = 409;
                        $data = array(
                            'status' => 409,
                            'success' => false,
                            'message' => 'Invalid Arguments - InvoiceAppID'
                        );

                                                
                    }
                    
                }

                if ($invoiceID == 0) {
                    $existingPeoples = InvoicePersonnel::where('InvoiceAppID', $invoiceAppID)
                        ->where('UserID', $peopleID)
                        ->get();
                } else {
                    $existingPeoples = InvoicePersonnel::where('InvoiceID', $invoiceID)
                        ->where('UserID', $peopleID)
                        ->get();
                }
                
                if ($deleted == 1) {
                        $invoicePersonnel = $existingPeoples->first();
                        $invoicePersonnel->delete();
                    continue;
                    }
            
                if (!$existingPeoples->isEmpty()) {
                    $invoicePersonnel = $existingPeoples->first();
                    $invoicePersonnel->InvoiceAppID = $invoiceAppID;
                    $invoicePersonnel->UserID = $peopleID;

                    if ($invoicePersonnel->save()) {
                        $status = 200;
                        $data = array(
                            'status' => 200,
                            'success' => true,
                            'message' => 'Changed InvoicePersonnel.',
                        ); 
                    } else {
                        $status = 201;
                        $data = array(
                            'status' => 201,
                            'success' => false,
                            'message' => 'Failed change InvoicePersonnel.',
                        ); 
                        return response(json_encode($data), $status);
                    }

                } else {
                    $invoicepersonnel = new InvoicePersonnel;
                    
                    $invoicepersonnel->InvoiceID = $invoiceID;
                    $invoicepersonnel->InvoiceAppID = $invoiceAppID;
                    $invoicepersonnel->EmployeeID = null;
                    $invoicepersonnel->UserID = $peopleID;  

                    if (!($invoicepersonnel->save())) {
                        $status = 201;
                        $data = array(
                            'status' => 201,
                            'success' => false,
                            'message' => 'Failed adding new InvoicePersonnel.',
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
