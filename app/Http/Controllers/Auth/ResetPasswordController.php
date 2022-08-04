<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\TokenService;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    protected function validator(Request $request)
    {
        return Validator::make($request->all(), [
            'userid' => 'required|max:11',
            'password' => 'required'
        ]);
    }


    public function changePassword(Request $request)
    {
        $userid = $request->userid;        
        $password = $request->password;

        $data = array();
        $status = 200;

        $reset_validator = $this->validator($request);
        if ($reset_validator->fails()) {
            $status = 400;
            $data = array(
                'status' => 400,
                'success' => false,
                'message' => 'Wrong params',
                'data' => $reset_validator->errors()
            );
            return response(json_encode($data), $status);
        }

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
            $user = Personnel::where('userid', $userid)
                    ->firstOrFail();

            $user->Password = $password;
            if($user->save()){
                $data = array(
                    'status' => 200,
                    'success' => true,
                    'message' => 'Password Change Successfully',
                    'data' => array(
                        'userid' => $user->userid,
                        'EmployeeID' => $user->EmployeeID,
                        'employeename'  => $user->EmployeeName,
                        'email' => $user->Email,
                        'active' => $user->Active,
                        'admin' => $user->Admin,
                    )
                );    
            } else {
                $status = 400;
                $data = array(
                    'status' => 400,
                    'success' => false,
                    'message' => 'Update Failed.'
                );
            }

            
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

    public function resetPassword(Request $request)
    {
        $data = array();
        $status = 200;

        $reset_validator = $this->validator($request);
        if ($reset_validator->fails()) {
            $status = 400;
            $data = array(
                'status' => 400,
                'success' => false,
                'message' => 'Wrong params',
                'data' => $reset_validator->errors()
            );
            return response(json_encode($data), $status);
        }


        $userid = $request->userid;        
        $password = $request->password;

        try {
            $user = Personnel::where('userid', $userid)
                    ->firstOrFail();

            $user->Password = $password;
            if($user->save()){
                $data = array(
                    'status' => 200,
                    'success' => true,
                    'message' => 'Password Change Successfully',                    
                );    
            } else {
                $status = 400;
                $data = array(
                    'status' => 400,
                    'success' => false,
                    'message' => 'Update Failed.'
                );
            }

            
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
