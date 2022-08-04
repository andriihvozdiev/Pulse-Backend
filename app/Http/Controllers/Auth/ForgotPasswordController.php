<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Mail;

use App\Models\Personnel;


class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }


    /**
     * Get a validator for an incoming request.
     *
     * @param  Request  $request
     * @return Illuminate\Support\Facades\Validator
     */
    protected function validator(Request $request)
    {
        return Validator::make($request->all(), [
            'email' => 'required|max:255|email'
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $status = 200;
        $data = array();
        
        $email_validator = $this->validator($request);
        if ($email_validator->fails()) {
            $status = 400;
            $data = array(
                'status' => 400,
                'success' => false,
                'message' => 'Wrong params',
                'data' => $email_validator->errors()
            );
            return response(json_encode($data), $status);
        }

        $email = $request->input('email');
        try {
            $user = Personnel::where('Email', '=', $email)
                    ->firstOrFail();
            if ($user) {
                $userid = $user->userid;

                Mail::send(
                    'Auth/forgotpassword',
                    [
                        'userid' => $userid
                    ],
                    function ($message) use ($user) {
                        $message->from(config('mail.username'));
                        $message->to($user->Email)->subject('PULSE - Reset Password');
                    }
                );

                $data = array(
                    'status' => 200,
                    'success' => true,
                    'message' => 'Please check your email to reset password.'
                );

            } else {
                $status = 400;
                $data = array(
                    'status' => 400,
                    'success' => false,
                    'message' => 'Your email is not registered.'
                );
            }
            
        } catch (ModelNotFoundException $e) {
            $status = 400;
            $data = array(
                'status' => 400,
                'success' => false,
                'message' => 'Invalid email.'
            );
        }

        return response(json_encode($data), $status);
    }


}
