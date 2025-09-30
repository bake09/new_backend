<?php

namespace App\Http\Controllers;

use App\Models\Cdr;
use App\Models\SFUser;
use Illuminate\Http\Request;
use App\Http\Resources\CdrResource;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\SFUserResource;
use Carbon\Carbon;
use App\Exports\CdrReportMultipleLoginsExport;
use Maatwebsite\Excel\Facades\Excel;

class CdrController extends Controller
{
    public function index()
    {
        $cdrs = Cdr::paginate();
        return CdrResource::collection($cdrs);
    }
    
    private string $baseUrl = 'https://sf.ahweller.de/rest';
    // private string $baseUrl = 'https://sflb.weller-automobile.de/rest';
    
    public function login(Request $request)
    {
        return "Users already imported!";

        $loginID  = env('STARFACE_USER', 'baok'); 
        $password = env('STARFACE_PASS', 'Rdlcgvp8');

        // $loginID  = env('STARFACE_USER', '182'); 
        // $password = env('STARFACE_PASS', 'Rdlcgvp8');
        
        // $loginID  = env('STARFACE_USER', '277'); 
        // $password = env('STARFACE_PASS', 'Dmkdohs1');

        try {
            // 1️⃣ Nonce abrufen
            $nonceResponse = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Version'    => '2',
                ])
                ->get($this->baseUrl . '/login');

            if (!$nonceResponse->ok()) {
                return response()->json([
                    'error'  => 'Nonce-Request fehlgeschlagen',
                    'status' => $nonceResponse->status(),
                    'body'   => $nonceResponse->body()
                ], 400);
            }

            $nonceData = $nonceResponse->json();
            $nonce     = $nonceData['nonce'] ?? null;
            $loginType = $nonceData['loginType'] ?? 'ActiveDirectory';

            if (!$nonce) {
                return response()->json(['error' => 'Kein Nonce erhalten'], 400);
            }

            // 2️⃣ Secret berechnen (AD-Login → Base64)
            $secret = base64_encode($loginID . $nonce . $password);

            // 3️⃣ Login durchführen
            $loginResponse = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Version'    => '2',
                ])
                ->post($this->baseUrl . '/login', [
                    'loginType' => $loginType,
                    'nonce'     => $nonce,
                    'secret'    => $secret,
                ]);

            if (!$loginResponse->ok()) {
                return response()->json([
                    'error'  => 'Login fehlgeschlagen',
                    'status' => $loginResponse->status(),
                    'body'   => $loginResponse->body()
                ], 400);
            }

            $loginData = $loginResponse->json();
            $token = $loginData['token'] ?? null;

            if (!$token) {
                return response()->json(['error' => 'token fehlt'], 400);
            }

            // 4️⃣ Userliste abrufen
            $usersResponse = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Version'    => '2',
                    'authToken'    => $token,
                ])
                ->get($this->baseUrl . '/users');

            $users = $usersResponse->ok() ? $usersResponse->json() : ['error' => 'Fehler beim Abrufen der User'];
            
            // 5️⃣ Benutzer in DB speichern
            foreach ($users as $user) {
                SFUser::updateOrCreate(
                    ['sf_id' => $user['id']], // eindeutige Bedingung
                    [
                        'sf_login'  => $user['login'] ?? null,
                        'firstname' => $user['firstName'] ?? null,
                        'surname'   => $user['familyName'] ?? null,
                        'email'     => $user['email'] ?? null,
                    ]
                );
            }

            // 5️⃣ Alles zurückgeben
            return response()->json([
                'status'    => 200,
                'loginType' => $loginType,
                'nonce'     => $nonce,
                'secret'    => $secret,
                'token'     => $token,
                'users'     => $users
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Exception aufgetreten',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getSFUsers()
    {
        $sfUsers = SFUserResource::collection(SFUser::all());
        return response()->json($sfUsers, 200, [], JSON_UNESCAPED_UNICODE);
    }
    

    // // TODOS's LB cdrs und user ebenfalls importieren!
    // public function createCDRreports(Request $request)
    // {
    //     $login = $request->users;
        
    //     $jetzt = Carbon::now();
    //     $letzteWoche = $jetzt->copy()->subWeek();
    //     $start = $letzteWoche->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d H:i:s');
    //     $ende  = $letzteWoche->copy()->endOfWeek(Carbon::SUNDAY)->format('Y-m-d H:i:s');

    //     // $login = [318];
    //     // $login = [317];
    //     // $login = [318, 787, 310, 441, 315, 317, 182, 440, 316];
    //     // mögliche weitere logins

    //     $cdrs = Cdr::whereBetween('starttime', [$start, $ende])
    //         ->whereIn('login', $login)
    //         ->get();

    //     $cdrsGrouped = $cdrs->groupBy('login'); // gruppiert nach login

    //     foreach ($cdrsGrouped as $loginId => $cdrCollection) {
    //         $name = SFUser::where('sf_login', $loginId)->value('firstname') . ' ' . SFUser::where('sf_login', $loginId)->value('surname');

    //         // Gesamtanzahl
    //         $gesamt = $cdrCollection->count();

    //         // Eingehend (incoming == true)
    //         $incoming = $cdrCollection->filter(function($cdr) {
    //             return $cdr->incoming == true;
    //         });
    //         $incomingGesamt = $incoming->count();
    //         $incomingAngenommen = $incoming->filter(function($cdr) {
    //             return $cdr->answered == true;
    //         })->count();
    //         $incomingNichtAngenommen = $incomingGesamt - $incomingAngenommen;

    //         // Ausgehend (incoming == false)
    //         $outgoing = $cdrCollection->filter(function($cdr) {
    //             return $cdr->incoming == false;
    //         });
    //         $outgoingGesamt = $outgoing->count();
    //         $outgoingAngenommen = $outgoing->filter(function($cdr) {
    //             return $cdr->answered == true;
    //         })->count();
    //         $outgoingNichtAngenommen = $outgoingGesamt - $outgoingAngenommen;

    //         // Intern (calledaccountid != 0)
    //         $intern = $cdrCollection->filter(function($cdr) {
    //             return $cdr->calledaccountid != 0;
    //         });
    //         $internGesamt = $intern->count();
    //         $internAngenommen = $intern->filter(function($cdr) {
    //             return $cdr->calledaccountid != 0 && $cdr->answered == true;
    //         })->count();
    //         $internNichtAngenommen = $internGesamt - $internAngenommen;

    //         // Extern (calledaccountid == 0)
    //         $extern = $cdrCollection->filter(function($cdr) {
    //             return $cdr->calledaccountid == 0;
    //         });
    //         $externGesamt = $extern->count();
    //         $externAngenommen = $extern->filter(function($cdr) {
    //             return $cdr->calledaccountid == 0 && $cdr->answered == true;
    //         })->count();
    //         $externNichtAngenommen = $externGesamt - $externAngenommen;

    //         // Detailliste (cdrs)
    //         $detailListe = $cdrCollection->map(function($cdr) {
    //             return [
    //                 // 'id' => $cdr->id,
    //                 // 'callid' => $cdr->callid,
    //                 // 'callercallerid' => $cdr->callercallerid,
    //                 // 'calledaccountid' => $cdr->calledaccountid,
    //                 // 'calledcallerid' => $cdr->calledcallerid,
    //                 // 'serviceid' => $cdr->serviceid,
    //                 // 'starttime' => $cdr->starttime,
    //                 // 'ringingtime' => $cdr->ringingtime,
    //                 // 'linktime' => $cdr->linktime,
    //                 // 'callresulttime' => $cdr->callresulttime,
    //                 // 'callresult' => $cdr->callresult,
    //                 // 'callbacknumber' => $cdr->callbacknumber,
    //                 // 'incoming' => $cdr->incoming,
    //                 // 'answered' => $cdr->answered,
    //                 // 'callbacknumberextern' => $cdr->callbacknumberextern,
    //                 // 'duration' => $cdr->duration,
    //                 // 'login' => $cdr->login,
    //                 'id'                   => $cdr->id,
    //                 'callid'               => $cdr->callid,
    //                 'callercallerid'       => $cdr->callercallerid,
    //                 // 'calledaccountid'      => $cdr->calledaccountid,
    //                 'calledaccountid'      => ($cdr->calledaccountid) ? $cdr->calledaccountid : '0',
    //                 'calledcallerid'       => $cdr->calledcallerid,
    //                 'serviceid'            => $cdr->serviceid,
    //                 'starttime'            => $cdr->starttime,
    //                 'ringingtime'          => $cdr->ringingtime,
    //                 'linktime'             => $cdr->linktime,
    //                 'callresulttime'       => $cdr->callresulttime,
    //                 'callresult'           => $cdr->callresult,
    //                 'callbacknumber'       => $cdr->callbacknumber,
    //                 // 'incoming'             => $cdr->incoming,
    //                 // 'answered'             => $cdr->answered,
    //                 // 'callbacknumberextern' => $cdr->callbacknumberextern,
    //                 // 'duration'             => $cdr->duration,
    //                 'incoming'             => ($cdr->incoming ? 'true' : 'false'),
    //                 'answered'             => ($cdr->answered ? 'true' : 'false'),
    //                 'callbacknumberextern' => ($cdr->callbacknumberextern ? 'true' : 'false'),
    //                 'duration'             => ($cdr->duration) ? $cdr->duration : '0',
    //                 'login'                => $cdr->login,
    //             ];
    //         })->values(); // values(), damit die Keys schön nummeriert sind

    //         $logins[$loginId] = [
    //             'name' => $name,
    //             'login' => $loginId,
    //             'anrufeGesamt' => $gesamt,
    //             'anrufeEingehend' => [
    //                 'anzahlGesamt' => $incomingGesamt,
    //                 'angenommen' => $incomingAngenommen,
    //                 'nichtangenommen' => $incomingNichtAngenommen,
    //             ],
    //             'anrufeAusgehend' => [
    //                 'anzahlGesamt' => $outgoingGesamt,
    //                 'angenommen' => $outgoingAngenommen,
    //                 'nichtangenommen' => $outgoingNichtAngenommen,
    //             ],
    //             'anrufeIntern' => [
    //                 'anzahlGesamt' => $internGesamt,
    //                 'angenommen' => $internAngenommen,
    //                 'nichtangenommen' => $internNichtAngenommen,
    //             ],
    //             'anrufeExtern' => [
    //                 'anzahlGesamt' => $externGesamt,
    //                 'angenommen' => $externAngenommen,
    //                 'nichtangenommen' => $externNichtAngenommen,
    //             ],
    //             'cdrs' => $detailListe,
    //         ];
    //     }

    //     // return response()->json([
    //     //     'kw'    => $letzteWoche->weekOfYear,
    //     //     'start' => $start,
    //     //     'ende'  => $ende,
    //     //     'logins' => $logins
    //     // ], 200, [], JSON_UNESCAPED_UNICODE);



    //     $loginsData[$loginId] = [
    //         'name'    => $name,
    //         'cdrRows' => $detailListe
    //     ];
    //     $fileName = 'cdr_report_multi_' . now()->format('Ymd_His') . '.xlsx';
    //     return Excel::store(
    //         new CdrReportMultipleLoginsExport($loginsData),
    //         $fileName
    //     );
    // }
    
    public function createCDRreports(Request $request)
    {
        $login = $request->users;  // Array von Login-IDs erwartet

        if (!is_array($login) || count($login) === 0) {
            return response()->json([
                'error' => 'Keine Login IDs übergeben'
            ], 400);
        }

        $jetzt = Carbon::now();
        $letzteWoche = $jetzt->copy()->subWeek();
        $start = $letzteWoche->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d H:i:s');
        $ende  = $letzteWoche->copy()->endOfWeek(Carbon::SUNDAY)->format('Y-m-d H:i:s');
        
        $kw = $letzteWoche->copy()->weekOfYear;
        $year = $letzteWoche->copy()->year;

        // Alle CDRs aus dem Zeitraum und den Logins holen
        $cdrs = Cdr::whereBetween('starttime', [$start, $ende])
            ->whereIn('login', $login)
            ->get();

        $cdrsGrouped = $cdrs->groupBy('login');

        $loginsData = [];  // Datenstruktur für Export

        foreach ($cdrsGrouped as $loginId => $cdrCollection) {
            // Name holen
            $user = SFUser::where('sf_login', $loginId)->first();
            $name = $user
                ? trim(($user->firstname ?? '') . ' ' . ($user->surname ?? ''))
                : ("Login_{$loginId}");

            // Deine Zählungen

            // Gesamt Anzahl
            $gesamt = $cdrCollection->count();

            // Eingehend (incoming == true)
            $incoming = $cdrCollection->filter(function($cdr) {
                return $cdr->incoming == true;
            });
            $incomingGesamt = $incoming->count();
            $incomingAngenommen = $incoming->filter(function($cdr) {
                return $cdr->answered == true;
            })->count();
            $incomingNichtAngenommen = $incomingGesamt - $incomingAngenommen;

            // Ausgehend (incoming == false)
            $outgoing = $cdrCollection->filter(function($cdr) {
                return $cdr->incoming == false;
            });
            $outgoingGesamt = $outgoing->count();
            $outgoingAngenommen = $outgoing->filter(function($cdr) {
                return $cdr->answered == true;
            })->count();
            $outgoingNichtAngenommen = $outgoingGesamt - $outgoingAngenommen;

            // Intern (calledaccountid != 0)
            $intern = $cdrCollection->filter(function($cdr) {
                return $cdr->calledaccountid != 0;
            });
            $internGesamt = $intern->count();
            $internAngenommen = $intern->filter(function($cdr) {
                return $cdr->calledaccountid != 0 && $cdr->answered == true;
            })->count();
            $internNichtAngenommen = $internGesamt - $internAngenommen;

            // Extern (calledaccountid == 0)
            $extern = $cdrCollection->filter(function($cdr) {
                return $cdr->calledaccountid == 0;
            });
            $externGesamt = $extern->count();
            $externAngenommen = $extern->filter(function($cdr) {
                return $cdr->calledaccountid == 0 && $cdr->answered == true;
            })->count();
            $externNichtAngenommen = $externGesamt - $externAngenommen;

            // Detailliste
            $detailListe = $cdrCollection->map(function($cdr) {
                return [
                    'id'                   => $cdr->id,
                    'callid'               => $cdr->callid,
                    'callercallerid'       => $cdr->callercallerid,
                    // 'calledaccountid'      => $cdr->calledaccountid,
                    'calledaccountid'      => ($cdr->calledaccountid) ? $cdr->calledaccountid : '0',
                    'calledcallerid'       => $cdr->calledcallerid,
                    'serviceid'            => $cdr->serviceid,
                    'starttime'            => $cdr->starttime,
                    'ringingtime'          => $cdr->ringingtime,
                    'linktime'             => $cdr->linktime,
                    'callresulttime'       => $cdr->callresulttime,
                    'callresult'           => $cdr->callresult,
                    'callbacknumber'       => $cdr->callbacknumber,
                    // 'incoming'             => $cdr->incoming,
                    // 'answered'             => $cdr->answered,
                    // 'callbacknumberextern' => $cdr->callbacknumberextern,
                    // 'duration'             => $cdr->duration,
                    'incoming'             => ($cdr->incoming ? 'true' : 'false'),
                    'answered'             => ($cdr->answered ? 'true' : 'false'),
                    'callbacknumberextern' => ($cdr->callbacknumberextern ? 'true' : 'false'),
                    'duration'             => ($cdr->duration) ? $cdr->duration : '0',
                    'login'                => $cdr->login,
                ];
            })->values();

            // Daten für Export zusammenstellen
            $loginsData[$loginId] = [
                'name'      => $name,
                'gesamt'    => $gesamt,
                'incoming'  => [
                    'gesamt'       => $incomingGesamt,
                    'angenommen'   => $incomingAngenommen,
                    'nichtangenommen' => $incomingNichtAngenommen
                ],
                'outgoing' => [
                    'gesamt'        => $outgoingGesamt,
                    'angenommen'    => $outgoingAngenommen,
                    'nichtangenommen'=> $outgoingNichtAngenommen
                ],
                'intern' => [
                    'gesamt'        => $internGesamt,
                    'angenommen'    => $internAngenommen,
                    'nichtangenommen'=> $internNichtAngenommen
                ],
                'extern' => [
                    'gesamt'        => $externGesamt,
                    'angenommen'    => $externAngenommen,
                    'nichtangenommen'=> $externNichtAngenommen
                ],
                'cdrRows'   => $detailListe,
                'startDate' => $start,
                'endDate'   => $ende
            ];
        }

        // $fileName = 'cdr_report_multi_' . now()->format('Ymd_His') . '.xlsx';
        $fileName = 'Auswertung_Anruferlisten_KW_' . $kw . $year . '_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::store(
            // new CdrReportMultipleLoginsExport($loginsData),
            new CdrReportMultipleLoginsExport($loginsData),
            $fileName
        );
    }

}

