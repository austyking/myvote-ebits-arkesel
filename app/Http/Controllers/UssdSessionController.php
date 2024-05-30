<?php

namespace App\Http\Controllers;

use App\Services\Election\ElectionService;
use App\Services\UssdSessions\ProcessSessions;
use App\Services\UssdSessions\StageService;
use App\Services\UssdSessions\UssdSessionService;
use App\Services\Vote\VoteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UssdSessionController extends Controller
{
    public function __invoke(
        Request $request,
        UssdSessionService $ussdSessionService,
        ProcessSessions $processSessions,
        StageService $stageService,
        ElectionService $electionService,
        VoteService $voteService
    ) {
        $requestBody = $request->all ();
        try {
            $response = $ussdSessionService->handleUssdSession ($requestBody, $processSessions, $stageService, $electionService, $voteService);
            return response ()->json ($response);
        } catch (\Exception $e) {
            Log::error ($e->getMessage ());
            return [
                'sessionID' => $requestBody['sessionID'],
                'userID' => $requestBody['userID'],
                'msisdn' => $requestBody['msisdn'],
                'message' => $ussdSessionService->setMessage('Your session may have timed out or unknown error occurred'),
                'continueSession' => false,
            ];
        }
    }
}
