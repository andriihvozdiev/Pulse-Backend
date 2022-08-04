<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(Request $request)
    {
        return Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|min:2',
        ]);
    }

    public function signup(Request $request)
    {
        $data = array();
        $status = 200;

        $v = $this->validator($request);
        if ($v->fails()) {
            $status = 400;
            $data = array(
                'status' => 400,
                'success' => false,
                'message' => 'Wrong params',
                'data' => $v->errors()
            );
            return response(json_encode($data), $status);
        }

        $employeename = $request->name;
        $email = $request->email;
        $password = $request->password;       

        try{
            $counter = Personnel::where('Email', $email)->count();
            if ($counter > 0) 
            {
                $status = 201;
                $data = array(
                    'status' => 201,
                    'success' => false,
                    'message' => 'User Already Registered.'
                );
                return response(json_encode($data), $status);
            }

            $user = new Personnel;
            
            $user->Email = $email;
            $user->Password = $password;            
            $user['EmployeeName'] = $employeename;
            

            if ($user->save()) {

                Mail::send(
                    'Auth/email_verification',
                    [
                        'name' => $user->EmployeeName,
                        'userid' => $user->userid
                    ],
                    function ($message) use ($user) {
                        $message->from(config('mail.username'));
                        $message->to($user->Email)->subject('Verify your PULSE account email address');
                    }
                );

                $data = array(
                    'status' => 200,
                    'success' => true,
                    'message' => 'Profile added successfully. Please check your Inbox to verify your email.',
                    'data' => array(
                        'userid' => $user->userid,
                        'employeename' => $user->EmployeeName,
                        'email' => $user->Email,
                    ),
                );
            } else {
                $status = 401;
                $data = array(
                    'status' => 401,
                    'success' => false,
                    'message' => 'Profile can not update.'
                );
            }
            
        } catch (Exception $e){
            $status = 500;
            $data = array(
                'status' => 500,
                'success' => false,
                'message' => 'Profile can not update.'
            );
        }
        
        return response(json_encode($data), $status);
    }
}
