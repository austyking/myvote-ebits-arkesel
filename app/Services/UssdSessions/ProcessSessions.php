<?php

namespace App\Services\UssdSessions;

use App\Models\Position;
use App\Models\Voter;
use App\Services\Election\ElectionService;
use App\Services\Vote\VoteService;
use Illuminate\Support\Facades\Cache;

class ProcessSessions
{
    public function handleNewUssdSession(
        array $requestBody,
        StageService $stageService,
        ElectionService $electionService,
        VoteService $voteService
    ): array {
        if (($election = $electionService->isElectionActive($requestBody['userData']))) { // 1
            $key = $requestBody['msisdn'].'|'.$requestBody['sessionID'];
            $voter = Voter::where([
                'phone' => $requestBody['msisdn'],
                'election_id' => $election->id
            ])->first ();

            if (!$voter){
                return [
                    'status' => 'completed',
                    'message' => "Sorry! You are not a registered voter for $election->name",
                ];
            }

            $positions = $voteService->notVotedPositions ($voter, $election);

            if (count ($positions) < 1){
                return [
                    'status' => 'completed',
                    'message' => "There are no (remaining) positions for $election->name to engage. You may have already voted in all available positions, or positions have not been defined yet.",
                ];
            }

            $positionString = '';
            $positionsArray = [];
            $positions->each (function (Position $position, int $index) use (&$positionString, &$positionsArray) {
                $positionNumber = ++$index;
                $positionsArray[$positionNumber] = $position;
                return $positionString .= $positionNumber . '. '. $position->name ."\n";
            });

            Cache::put($key, [
                'election' => $election,
                'next_stage' => 'ST1',
                'positions' => $positionsArray,
                'positionsList' => $positionString,
                'voter' => $voter
            ], 1200); // store in cache for 20 minutes

            $message = sprintf($stageService->getNextStageMessage ('ST1'), $election->name, $positionString);
            return [
                'status' => 'success',
                'message' => $message,
            ];
        }

        return [
            'status' => 'next',
            'message' => 'Next',
        ];
    }

    public function handleSubsequentUssdSessions(
        array $requestBody,
        StageService $stageService
    ): array {
        $key = $this->getCacheKey($requestBody);

        $data = Cache::get($key);

        switch ($data['next_stage']) {
            case 'ST1':
                return $stageService->stageOne($data, $requestBody);
            case 'ST2':
                return $stageService->stageTwo($data, $requestBody);
            case 'ST3':
                return $stageService->stageThree($data, $requestBody);
            case 'ST4':
                return $stageService->stageFour($data, $requestBody);
        }
    }

    public function getCacheKey(array $requestBody): string
    {
        return $requestBody['msisdn'].'|'.$requestBody['sessionID'];
    }
}
