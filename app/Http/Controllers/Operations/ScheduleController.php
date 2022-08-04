<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

use App\Models\OpsSchedule;

use App\Services\TokenService;

class ScheduleController extends Controller
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
     * Get all Schedules from tblOpsSchedule table.
     *
     * @return json response.
     */
    public function getAllSchedules(Request $request)
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
            $arrSchedulesList = array();

            if ($isemptypermission == "true") {
                $arrSchedulesList = OpsSchedule::orderby('Lease', 'ASC')
                    ->get();

            } else {
                $arrSchedulesList = OpsSchedule::join('ListAssetLocations', 'tblOpsSchedule.Lease', '=', 'ListAssetLocations.GrandparentPropNum')
                    ->where(function($query) use($routes, $operatings, $owns){
                        $query->whereIn('ListAssetLocations.Route', $routes)
                            ->whereIn('ListAssetLocations.OpCompany', $operatings)           
                            ->whereIn('ListAssetLocations.OwnCompany', $owns);
                    })
                    ->orderby('Lease', 'ASC')
                    ->select('tblOpsSchedule.*')
                    ->get();
            }
            
            foreach($arrSchedulesList as $schedule){

                $alldata = array();

                $alldata['scheduleID'] = $schedule->ScheduleID;
                $alldata['lease'] = $schedule->Lease;
                $alldata['wellNumber'] = $schedule->WellNumber;
                $alldata['scheduleType'] = $schedule->ScheduleType;
                $alldata['initPlanStartDt'] = $schedule->InitPlanStartDt == null ? null : Carbon::parse($schedule->InitPlanStartDt)->format('m/d/Y H:i:s');
                $alldata['updatedPlanStartDt'] = $schedule->UpdatedPlanStartDt == null ? null : Carbon::parse($schedule->UpdatedPlanStartDt)->format('m/d/Y H:i:s');
                $alldata['entryUserID'] = $schedule->EntryUserID;
                $alldata['updatedPlanUserID'] = $schedule->UpdatedPlanUserID;

                if ($schedule->UpdatedPlanStartDt == null) {
                    $alldata['planStartDt'] = Carbon::parse($schedule->InitPlanStartDt)->format('m/d/Y H:i:s');
                } else {
                    $alldata['planStartDt'] = $schedule->UpdatedPlanStartDt == null ? null : Carbon::parse($schedule->UpdatedPlanStartDt)->format('m/d/Y H:i:s');
                }                
                
                $alldata['actualStartDt'] = $schedule->ActualStartDt == null ? null : Carbon::parse($schedule->ActualStartDt)->format('m/d/Y H:i:s');
                $alldata['actualEndDt'] = $schedule->ActualEndDt == null ? null : Carbon::parse($schedule->ActualEndDt)->format('m/d/Y H:i:s');

                $alldata['actStartUserID'] = $schedule->ActStartUserID;
                $alldata['actEndUserID'] = $schedule->ActEndUserID;

                $alldata['engrComments'] = $schedule->EngrComments;
                $alldata['acctComments'] = $schedule->AcctComments;
                $alldata['fieldComments'] = $schedule->FieldComments;
                $alldata['criticalEndDt'] = $schedule->CriticalEndDt == null ? null : Carbon::parse($schedule->CriticalEndDt)->format('m/d/Y H:i:s');

                if ($schedule->ActualEndDt != null)
                {
                    $alldata['status'] = "Completed";
                    $alldata['date'] = $alldata['actualEndDt'];
                } 
                if ($schedule->ActualEndDt == null && $schedule->ActualStartDt != null) {
                    $alldata['status'] = "Started";
                    $alldata['date'] = $alldata['actualStartDt'];
                }
                if ($schedule->ActualStartDt == null && $schedule->ActualEndDt == null && ($schedule->InitPlanStartDt != null || $schedule->UpdatedPlanStartDt != null)) {
                    $alldata['status'] = "Scheduled";
                    $alldata['date'] = $alldata['planStartDt'];
                }

                $responsedata[] = $alldata;
            }

            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get all Schedules',
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
     * Upload Schedules to tblOpsSchedule table.
     *
     * @return json response.
     */
    public function uploadSchedules(Request $request)
    {
        $data = array();
        $responsedata = array();
        $status = 200;

        $userid = $request->userid;
        $arrSchedules = $request->schedules;
        
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
            
            for ($i = 0; $i < count($arrSchedules); $i++) {
                $dicSchedule = $arrSchedules[$i];

                $lease = $dicSchedule['lease'];
                $wellNumber = isset($dicSchedule['wellNumber']) ? $dicSchedule['wellNumber'] : null;
                $scheduleType = $dicSchedule['scheduleType'];

                $arrSchedulesList = OpsSchedule::where('Lease', $lease)
                    ->where('WellNumber', $wellNumber)
                    ->where('ScheduleType', $scheduleType)
                    ->get();

                if (!$arrSchedulesList->isEmpty()) {
                    $oldSchedule = $arrSchedulesList->first();
                    

                    $oldSchedule['InitPlanStartDt'] = isset($dicSchedule['initPlanStartDt']) ? Carbon::parse($dicSchedule['initPlanStartDt']) : null;
                    $oldSchedule['UpdatedPlanStartDt'] = isset($dicSchedule['updatedPlanStartDt']) ? Carbon::parse($dicSchedule['updatedPlanStartDt']) : null;
                    $oldSchedule['EntryUserID'] = $dicSchedule['entryUserID'];
                    $oldSchedule['UpdatedPlanUserID'] = isset($dicSchedule['updatedPlanUserID']) ? $dicSchedule['updatedPlanUserID'] : null; 
                    $oldSchedule['ActualStartDt'] = isset($dicSchedule['actualStartDt']) ? Carbon::parse($dicSchedule['actualStartDt']) : null;
                    $oldSchedule['ActualEndDt'] = isset($dicSchedule['actualEndDt']) ? Carbon::parse($dicSchedule['actualEndDt']) : null;
                    $oldSchedule['ActStartUserID'] = isset($dicSchedule['actStartUserID']) ? $dicSchedule['actStartUserID'] : null; 
                    $oldSchedule['ActEndUserID'] = isset($dicSchedule['actEndUserID']) ? $dicSchedule['actEndUserID'] : null;
                    $oldSchedule['EngrComments'] = isset($dicSchedule['engrComments']) ? $dicSchedule['engrComments'] : null;
                    $oldSchedule['AcctComments'] = isset($dicSchedule['acctComments']) ? $dicSchedule['acctComments'] : null;
                    $oldSchedule['FieldComments'] = isset($dicSchedule['fieldComments']) ? $dicSchedule['fieldComments'] : null;


                    if ($oldSchedule->save())
                    {
                        $data = array(
                            'status' => 200,
                            'success' => true,
                            'message' => 'Added a new Schedule successfully.',
                        ); 
                    } 
                    else 
                    {
                        $status = 400;
                        $data = array(
                            'status' => 400,
                            'success' => true,
                            'message' => 'Failed adding a new Schedule.',
                        ); 
                        return response(json_encode($data), $status);
                    }

                    
                } else {
                    $newSchedule = new OpsSchedule;                    

                    $newSchedule['Lease'] = $dicSchedule['lease'];
                    $newSchedule['WellNumber'] = $wellNumber;
                    $newSchedule['ScheduleType'] = $dicSchedule['scheduleType'];
                    $newSchedule['InitPlanStartDt'] = isset($dicSchedule['initPlanStartDt']) ? Carbon::parse($dicSchedule['initPlanStartDt']) : null;
                    $newSchedule['UpdatedPlanStartDt'] = isset($dicSchedule['updatedPlanStartDt']) ? Carbon::parse($dicSchedule['updatedPlanStartDt']) : null;
                    $newSchedule['EntryUserID'] = $dicSchedule['entryUserID'];
                    $newSchedule['UpdatedPlanUserID'] = isset($dicSchedule['updatedPlanUserID']) ? $dicSchedule['updatedPlanUserID'] : null; 
                    $newSchedule['ActualStartDt'] = isset($dicSchedule['actualStartDt']) ? Carbon::parse($dicSchedule['actualStartDt']) : null;
                    $newSchedule['ActualEndDt'] = isset($dicSchedule['actualEndDt']) ? Carbon::parse($dicSchedule['actualEndDt']) : null;
                    $newSchedule['ActStartUserID'] = isset($dicSchedule['actStartUserID']) ? $dicSchedule['actStartUserID'] : null; 
                    $newSchedule['ActEndUserID'] = isset($dicSchedule['actEndUserID']) ? $dicSchedule['actEndUserID'] : null;
                    $newSchedule['EngrComments'] = isset($dicSchedule['engrComments']) ? $dicSchedule['engrComments'] : null;
                    $newSchedule['AcctComments'] = isset($dicSchedule['acctComments']) ? $dicSchedule['acctComments'] : null;
                    $newSchedule['FieldComments'] = isset($dicSchedule['fieldComments']) ? $dicSchedule['fieldComments'] : null;

                    if ($newSchedule->save())
                    {
                        $data = array(
                            'status' => 200,
                            'success' => true,
                            'message' => 'Added a new Schedule successfully.',
                        ); 
                    } 
                    else 
                    {
                        $status = 400;
                        $data = array(
                            'status' => 400,
                            'success' => true,
                            'message' => 'Failed adding a new Schedule.',
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
