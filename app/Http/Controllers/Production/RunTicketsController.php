<?php

namespace App\Http\Controllers\Production;
use DB;

use App\Http\Controllers\Controller;
use App\Services\TokenService;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\RunTickets;

class RunTicketsController extends Controller
{
    /**
     * Get RunTickets from RunTickets table.
     *
     * @return json response.
     */
    public function getRunTickets(Request $request)
    {
        $data = array();
        $responsedata = array();
        $status = 200;

        $userid = $request->userid;
        
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
           
            $sqlTickets = "SELECT * FROM RunTickets WHERE TicketTime >= DATEADD(day, -" . $daysToSync . ", GETDATE())";

            $sqlLastTickets = "SELECT * FROM RunTickets n WHERE TicketTime = (SELECT MAX(TicketTime) as TicketTime FROM RunTickets GROUP BY Lease HAVING Lease=n.Lease)";

            $sql = $sqlTickets . " UNION " . $sqlLastTickets . " ORDER BY TicketTime DESC";

            $arrTickets = DB::select($sql);

            foreach($arrTickets as $ticket){
                $alldata = array();
                
                $alldata['internalTicketID'] = $ticket->InternalTicketID;
                $alldata['deviceID'] = $ticket->DeviceID;
                $alldata['entryTime'] = $ticket->EntryTime == null ? null : Carbon::parse($ticket->EntryTime)->format('m/d/Y H:i:s');
                $alldata['ticketTime'] = $ticket->TicketTime == null ? null : Carbon::parse($ticket->TicketTime)->format('m/d/Y H:i:s');
                $alldata['lease'] = $ticket->Lease;
                $alldata['tankNumber'] = $ticket->TankNumber == null ? 0 : $ticket->TankNumber;
                $alldata['ticketNumber'] = $ticket->TicketNumber;
                $alldata['temp1'] = $ticket->Temp1;
                $alldata['oilFeet1'] = $ticket->OilFeet1;
                $alldata['oilInch1'] = $ticket->OilInch1;
                $alldata['oilFraction1'] = $ticket->OilFraction1;
                $alldata['bottomsFeet1'] = $ticket->BottomsFeet1;
                $alldata['bottomsInch1'] = $ticket->BottomsInch1;
                $alldata['temp2'] = $ticket->Temp2;
                $alldata['oilFeet2'] = $ticket->OilFeet2;
                $alldata['oilInch2'] = $ticket->OilInch2;
                $alldata['oilFraction2'] = $ticket->OilFraction2;
                $alldata['bottomsFeet2'] = $ticket->BottomsFeet2;
                $alldata['bottomsInch2'] = $ticket->BottomsInch2;
                $alldata['obsGrav'] = $ticket->ObsGrav;
                $alldata['obsTemp'] = $ticket->ObsTemp;
                $alldata['bsw'] = $ticket->BSW;
                $alldata['grossVol'] = $ticket->GrossVol;
                $alldata['netVol'] = $ticket->NetVol;
                $alldata['timeOn'] = $ticket->TimeOn == null ? null : Carbon::parse($ticket->TimeOn)->format('m/d/Y H:i:s');
                $alldata['timeOff'] = $ticket->TimeOff == null ? null : Carbon::parse($ticket->TimeOff)->format('m/d/Y H:i:s');
                $alldata['carrier'] = $ticket->Carrier;
                $alldata['driver'] = $ticket->Driver;
                $alldata['comments'] = $ticket->Comments;
                $alldata['glMonth'] = $ticket->GLMonth;
                $alldata['glYear'] = $ticket->GLYear;
                $alldata['ticketOption'] = $ticket->BSWPull;
                $alldata['calcGrossVol'] = $ticket->CalcGrossVol;
                $alldata['calcNetVol'] = $ticket->CalcNetVol;
                if ($isNeededUrlForImage && $ticket->TicketImageName !== null) {
                    $alldata['ticketImage'] = $ticket->TicketImageName;
                } else {
                    $alldata['ticketImage'] = $ticket->TicketImage;    
                }
                $responsedata[] = $alldata;
            }

            
            $data = array(
                'status' => 200,
                'success' => true,
                'message' => 'Get all RunTickets data',
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

    /**
     * Upload RunTickets to RunTickets table.
     *
     * @return json response.
     */
    public function uploadRunTickets(Request $request)
    {
        $data = array();
        $responsedata = array();
        $status = 200;

        $arrTickets = $request->tickets;
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

            for ($i = 0; $i < count($arrTickets); $i++) {
                $dicRunTicket = $arrTickets[$i];
                
                $internalTicketID = $dicRunTicket['internalTicketID'];
                
                $entryTime = Carbon::parse($dicRunTicket['entryTime']);
                $ticketTime = Carbon::parse($dicRunTicket['ticketTime']);
                $lease = $dicRunTicket['lease'];
                $deviceID = $dicRunTicket['deviceID'];

                $tankNumber = $dicRunTicket['tankNumber'];
                $ticketNumber = $dicRunTicket['ticketNumber'];

                $temp1 = $dicRunTicket['temp1'];
                $oilFeet1 = $dicRunTicket['oilFeet1'];
                $oilInch1 = $dicRunTicket['oilInch1'];
                $oilFraction1 = $dicRunTicket['oilFraction1'];
                $bottomsFeet1 = $dicRunTicket['bottomsFeet1'];
                $bottomsInch1 = $dicRunTicket['bottomsInch1'];
                $temp2 = $dicRunTicket['temp2'];
                $oilFeet2 = $dicRunTicket['oilFeet2'];
                $oilInch2 = $dicRunTicket['oilInch2'];
                $oilFraction2 = $dicRunTicket['oilFraction2'];
                $bottomsFeet2 = $dicRunTicket['bottomsFeet2'];
                $bottomsInch2 = $dicRunTicket['bottomsInch2'];
                $obsGrav = $dicRunTicket['obsGrav'];
                $obsTemp = $dicRunTicket['obsTemp'];
                $bsw = $dicRunTicket['bsw'];
                $grossVol = isset($dicRunTicket['grossVol']) ? $dicRunTicket['grossVol'] : null;
                $netVol = isset($dicRunTicket['netVol']) ? $dicRunTicket['netVol'] : null;
                $timeOn = Carbon::parse($dicRunTicket['timeOn']);
                $timeOff = Carbon::parse($dicRunTicket['timeOff']);
                $carrier = isset($dicRunTicket['carrier']) ? $dicRunTicket['carrier'] : null;
                $driver = isset($dicRunTicket['driver']) ? $dicRunTicket['driver'] : null;
                $comments = isset($dicRunTicket['comments']) ? $dicRunTicket['comments'] : null;
                $ticketOption = $dicRunTicket['ticketOption'];
                $ticketImage = isset($dicRunTicket['ticketImage']) ? $dicRunTicket['ticketImage'] : null;
                
                $userid = $dicRunTicket['userid'];

                $deleted = isset($dicRunTicket['deleted']) ? $dicRunTicket['deleted'] : 0;
                                
                if ($deleted == 1) {
                    // RunTickets::where('Lease', $lease)
                    //     ->where('TankNumber', $tankNumber)
                    //     ->where('TicketNumber', $ticketNumber)
                    //     ->where('InternalTicketID', $internalTicketID)
                    //     ->delete();
                    RunTickets::where('InternalTicketID', $internalTicketID)->delete();
                    continue;
                }

                // $arrRunTicketsList = RunTickets::where('Lease', $lease)
                //     ->where('TankNumber', $tankNumber)
                //     ->where('TicketNumber', $ticketNumber)
                //     ->where('TicketTime', $ticketTime)
                //     ->where('UserID', $userid)
                //     ->get();
                $arrRunTicketsList = RunTickets::where('InternalTicketID', $internalTicketID)
                    ->get();

                if (!($arrRunTicketsList->isEmpty())) {
                    $oldRunTicket = $arrRunTicketsList->first();
                    
                    $oldRunTicket['DeviceID'] = $deviceID;
                    $oldRunTicket['EntryTime'] = $entryTime;
                    $oldRunTicket['TicketTime'] = $ticketTime;
                    $oldRunTicket['Lease'] = $lease;
                    $oldRunTicket['TankNumber'] = $tankNumber;                    
                    $oldRunTicket['TicketNumber'] = $ticketNumber;

                    $oldRunTicket['Temp1'] = $temp1;
                    $oldRunTicket['OilFeet1'] = $oilFeet1;
                    $oldRunTicket['OilInch1'] = $oilInch1;
                    $oldRunTicket['OilFraction1'] = $oilFraction1;
                    $oldRunTicket['BottomsFeet1'] = $bottomsFeet1;
                    $oldRunTicket['BottomsInch1'] = $bottomsInch1;
                    
                    $oldRunTicket['Temp2'] = $temp2;
                    $oldRunTicket['OilFeet2'] = $oilFeet2;
                    $oldRunTicket['OilInch2'] = $oilInch2;
                    $oldRunTicket['OilFraction2'] = $oilFraction2;
                    $oldRunTicket['BottomsFeet2'] = $bottomsFeet2;
                    $oldRunTicket['BottomsInch2'] = $bottomsInch2;

                    $oldRunTicket['ObsGrav'] = $obsGrav;
                    $oldRunTicket['ObsTemp'] = $obsTemp;
                    $oldRunTicket['BSW'] = $bsw;
                    $oldRunTicket['GrossVol'] = $grossVol;
                    $oldRunTicket['NetVol'] = $netVol;
                    $oldRunTicket['TimeOn'] = $timeOn;
                    $oldRunTicket['TimeOff'] = $timeOff;
                    $oldRunTicket['Carrier'] = $carrier;
                    $newRunTicket['Driver'] = $driver;
                    $oldRunTicket['Comments'] = $comments;
                    $oldRunTicket['BSWPull'] = $ticketOption;
                    // $oldRunTicket['TicketImage'] = $ticketImage;
                    $image = str_replace('data:image/png;base64,', '', $ticketImage);
                    $image = str_replace(' ', '+', $image);
                    $imageName = 'runticketimageurl' . str_random(50).'.'.'png';
                    \File::put(public_path(). '/' . 'assets/' . $imageName, base64_decode($image));
                    $newRunTicket['TicketImageName'] = $imageName;
                    $newRunTicket['TicketImage'] = $image;
                    $oldRunTicket['UserID'] = $userid;


                    if ($oldRunTicket->save())
                    {
                        $data = array(
                            'status' => 200,
                            'success' => true,
                            'message' => 'Added a RunTicket successfully.',
                        ); 
                    } 
                    else 
                    {
                        $status = 400;
                        $data = array(
                            'status' => 400,
                            'success' => true,
                            'message' => 'Failed adding a new TunTicket.',
                        ); 
                        return response(json_encode($data), $status);
                    }

                    
                } else {
                    $newRunTicket = new RunTickets;                    

                    $newRunTicket['DeviceID'] = $deviceID;
                    $newRunTicket['EntryTime'] = $entryTime;
                    $newRunTicket['TicketTime'] = $ticketTime;
                    $newRunTicket['Lease'] = $lease;
                    $newRunTicket['TankNumber'] = $tankNumber;                    
                    $newRunTicket['TicketNumber'] = $ticketNumber;

                    $newRunTicket['Temp1'] = $temp1;
                    $newRunTicket['OilFeet1'] = $oilFeet1;
                    $newRunTicket['OilInch1'] = $oilInch1;
                    $newRunTicket['OilFraction1'] = $oilFraction1;
                    $newRunTicket['BottomsFeet1'] = $bottomsFeet1;
                    $newRunTicket['BottomsInch1'] = $bottomsInch1;
                    
                    $newRunTicket['Temp2'] = $temp2;
                    $newRunTicket['OilFeet2'] = $oilFeet2;
                    $newRunTicket['OilInch2'] = $oilInch2;
                    $newRunTicket['OilFraction2'] = $oilFraction2;
                    $newRunTicket['BottomsFeet2'] = $bottomsFeet2;
                    $newRunTicket['BottomsInch2'] = $bottomsInch2;
                    
                    $newRunTicket['ObsGrav'] = $obsGrav;
                    $newRunTicket['ObsTemp'] = $obsTemp;
                    $newRunTicket['BSW'] = $bsw;
                    $newRunTicket['GrossVol'] = $grossVol;
                    $newRunTicket['NetVol'] = $netVol;
                    $newRunTicket['TimeOn'] = $timeOn;
                    $newRunTicket['TimeOff'] = $timeOff;
                    $newRunTicket['Carrier'] = $carrier;
                    $newRunTicket['Driver'] = $driver;
                    $newRunTicket['Comments'] = $comments;
                    $newRunTicket['BSWPull'] = $ticketOption;
                    // $newRunTicket['TicketImage'] = $ticketImage;

                    $image = str_replace('data:image/png;base64,', '', $ticketImage);
                    $image = str_replace(' ', '+', $image);
                    $imageName = 'runticketimageurl' . str_random(50).'.'.'png';
                    \File::put(public_path(). '/' . 'assets/' . $imageName, base64_decode($image));
                    $newRunTicket['TicketImageName'] = $imageName;
                    $newRunTicket['TicketImage'] = $image;

                    $newRunTicket['UserID'] = $userid;
                    
                    if ($newRunTicket->save())
                    {
                        $data = array(
                            'status' => 200,
                            'success' => true,
                            'message' => 'Added a new RunTicket successfully.',
                        ); 

                    } 
                    else 
                    {
                        $status = 400;
                        $data = array(
                            'status' => 400,
                            'success' => true,
                            'message' => 'Failed adding a new RunTicket.',
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
