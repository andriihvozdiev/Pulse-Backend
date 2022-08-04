<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Models\Personnel;
use App\Models\LeaseRoutes;
use App\Models\ListCompany;

use App\Services\TokenService;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    
    /**
     * Get a validator for an incoming login request.
     *
     * @param  Request  $request
     * @return Illuminate\Support\Facades\Validator
     */
    protected function validator(Request $request)
    {
        return Validator::make($request->all(), [
            'email' => 'required|max:255|email',
            'password' => 'required|max:255'
        ]);
    }

    /**
     * Get a response for an incoming login request.
     *
     * @param  Request  $request (including $email, $password)
     * @return json response.
     */
    public function login(Request $request) 
    {
        $data = array();
        $status = 200;
        
        $login_validator = $this->validator($request);
        if ($login_validator->fails()) {
            $status = 400;
            $data = array(
                'status' => 400,
                'success' => false,
                'message' => 'Wrong params',
                'data' => $login_validator->errors()
            );
            return response(json_encode($data), $status);
        }

        //Retrieve the name input field
        $email = $request->input('email');
        
        $password = $request->password;
        
        $nullcount = 0;

        try {
            $user = Personnel::where('Email', '=', $email)
                    ->where('Password', $password)
                    ->firstOrFail();

            if ($user->AdminApp == 0)
            {
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
                    'message' => 'Unverified Email',
                    'data' => array(
                        'userid' => $user->userid                        
                    ),
                );
                return response(json_encode($data), $status);
            }

            $routes = array();
            $operatings = array();
            $owns = array();

            if ($user->Route1 != null) {
                $routes[] = $user->Route1;
            }
            if ($user->Route2 != null) {
                $routes[] = $user->Route2;
            }
            if ($user->Route3 != null) {
                $routes[] = $user->Route3;
            }
            if ($user->Route4 != null) {
                $routes[] = $user->Route4;
            }
            if ($user->Route5 != null) {
                $routes[] = $user->Route5;
            }

            if (count($routes) == 0) {
                $nullcount += 1;
                $leaseRoutes = LeaseRoutes::get();

                foreach ($leaseRoutes as $leaseRoute) {
                    $routes[] = $leaseRoute->ID;    
                }
                
            }

            if ($user->Operating1 != null) {
                $operatings[] = $user->Operating1;
            }
            if ($user->Operating2 != null) {
                $operatings[] = $user->Operating2;
            }
            if ($user->Operating3 != null) {
                $operatings[] = $user->Operating3;
            }
            if ($user->Operating4 != null) {
                $operatings[] = $user->Operating4;
            }
            if ($user->Operating5 != null) {
                $operatings[] = $user->Operating5;
            }

            if (count($operatings) == 0) {
                $nullcount += 1;
                $listcompanies = DB::select('select * from ListCompany');

                foreach ($listcompanies as $listcompany) {
                    $operatings[] = $listcompany->CompanyCode;                     
                }
                
            }

            if ($user->Own1 != null) {
                $owns[] = $user->Own1;
            }
            if ($user->Own2 != null) {
                $owns[] = $user->Own2;
            }
            if ($user->Own3 != null) {
                $owns[] = $user->Own3;
            }
            if ($user->Own4 != null) {
                $owns[] = $user->Own4;
            }
            if ($user->Own5 != null) {
                $owns[] = $user->Own5;
            }

            if (count($owns) == 0) {
                $nullcount += 1;
                $listcompanies = ListCompany::get();

                foreach ($listcompanies as $listcompany) {
                    $owns[] = $listcompany->CompanyCode;                     
                }
                
            }

            $user->LastLogin = Carbon::now();

            if ($user->save()) {
                $emptypermission = "false";
                if ($nullcount == 3) {
                    $emptypermission = "true";
                }

                $payload = [
                    'userid' => $user->userid, 
                    'email' => $user->Email
                ];
                $token = TokenService::createToken($payload);
                header('accessToken:'.$token);

                $data = array(
                    'status' => 200,
                    'success' => true,
                    'message' => 'Login Successfully.',
                    'data' => array(
                        'userid' => $user->userid,
                        'employeename'  => $user->EmployeeName,
                        'email' => $user->Email,
                        'password' => $user->Password,
                        'active' => $user->Active,
                        'admin' => $user->Admin,
                        'routes' => $routes,
                        'operatings' => $operatings,
                        'owns' => $owns,
                        'emptypermission' => $emptypermission,
                        'department' => $user->Department == null ? "null" : $user->Department,
                    ),
                );
            }

            
        } catch (ModelNotFoundException $e) {
            $status = 400;
            $data = array(
                'status' => 400,
                'success' => false,
                'message' => 'Please enter valid username and password.'
            );
        }
        
        return response(json_encode($data), $status);
        
    }


    protected function userid_validator(Request $request)
    {
        return Validator::make($request->all(), [
            'userid' => 'required|numeric'            
        ]);
    }

    /**
     * Get a response for an incoming login request.
     *
     * @param  Request  $request (including $userid)
     * @return json response.
     */
    public function loginWithUserID(Request $request) 
    {
        $data = array();
        $status = 200;
        
        $login_validator = $this->userid_validator($request);
        if ($login_validator->fails()) {
            $status = 400;
            $data = array(
                'status' => 400,
                'success' => false,
                'message' => 'Wrong params',
                'data' => $login_validator->errors()
            );
            return response(json_encode($data), $status);
        }

        $userid = $request->userid;
        
        $nullcount = 0;

        try {
            $user = Personnel::where('userid', '=', $userid)
                    ->firstOrFail();

            $routes = array();
            $operatings = array();
            $owns = array();

            if ($user->Route1 != null) {
                $routes[] = $user->Route1;
            }
            if ($user->Route2 != null) {
                $routes[] = $user->Route2;
            }
            if ($user->Route3 != null) {
                $routes[] = $user->Route3;
            }
            if ($user->Route4 != null) {
                $routes[] = $user->Route4;
            }
            if ($user->Route5 != null) {
                $routes[] = $user->Route5;
            }

            if (count($routes) == 0) {
                $nullcount += 1;
                $leaseRoutes = LeaseRoutes::get();

                foreach ($leaseRoutes as $leaseRoute) {
                    $routes[] = $leaseRoute->ID;    
                }
                
            }

            if ($user->Operating1 != null) {
                $operatings[] = $user->Operating1;
            }
            if ($user->Operating2 != null) {
                $operatings[] = $user->Operating2;
            }
            if ($user->Operating3 != null) {
                $operatings[] = $user->Operating3;
            }
            if ($user->Operating4 != null) {
                $operatings[] = $user->Operating4;
            }
            if ($user->Operating5 != null) {
                $operatings[] = $user->Operating5;
            }

            if (count($operatings) == 0) {
                $nullcount += 1;
                $listcompanies = DB::select('select * from ListCompany');

                foreach ($listcompanies as $listcompany) {
                    $operatings[] = $listcompany->CompanyCode;                     
                }
                
            }

            if ($user->Own1 != null) {
                $owns[] = $user->Own1;
            }
            if ($user->Own2 != null) {
                $owns[] = $user->Own2;
            }
            if ($user->Own3 != null) {
                $owns[] = $user->Own3;
            }
            if ($user->Own4 != null) {
                $owns[] = $user->Own4;
            }
            if ($user->Own5 != null) {
                $owns[] = $user->Own5;
            }

            if (count($owns) == 0) {
                $nullcount += 1;
                $listcompanies = ListCompany::get();

                foreach ($listcompanies as $listcompany) {
                    $owns[] = $listcompany->CompanyCode;                     
                }
                
            }

            $user->LastLogin = Carbon::now();
            $user->AdminApp = 1;

            if ($user->save()) {
                $emptypermission = "false";
                if ($nullcount == 3) {
                    $emptypermission = "true";
                }

                $payload = [
                    'userid' => $user->userid, 
                    'email' => $user->Email
                ];
                $token = TokenService::createToken($payload);
                header('accessToken:'.$token);

                $data = array(
                    'status' => 200,
                    'success' => true,
                    'message' => 'Login Successfully.',
                    'data' => array(
                        'userid' => $user->userid,
                        'employeename'  => $user->EmployeeName,
                        'email' => $user->Email,
                        'password' => $user->Password,
                        'active' => $user->Active,
                        'admin' => $user->Admin,
                        'routes' => $routes,
                        'operatings' => $operatings,
                        'owns' => $owns,
                        'emptypermission' => $emptypermission,
                        'department' => $user->Department == null ? "null" : $user->Department,
                    ),
                );
            }

            
        } catch (ModelNotFoundException $e) {
            $status = 400;
            $data = array(
                'status' => 400,
                'success' => false,
                'message' => 'Please enter valid username and password.'
            );
        }
        
        return response(json_encode($data), $status);
        
    }

}
