<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TokenService;
use Illuminate\Support\Facades\Validator;

use App\Models\ListCompany;


class ListCompanyController extends Controller
{
    protected function validator(Request $request)
    {
        return Validator::make($request->all(), [
            'userid' => 'required'
        ]);
    }

    /**
     * Get ListCompanies from ListCompany table.
     *
     * @return json response.
     */
    public function getAllListCompanies(Request $request)
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
            
            $listCompanies = ListCompany::get();

            foreach($listCompanies as $listCompany){
                
                $alldata = array();

                $alldata['companyCode'] = $listCompany->CompanyCode;
                $alldata['company'] = $listCompany->Company;
                
                $responsedata[] = $alldata;
            }

            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get all Companies',
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
