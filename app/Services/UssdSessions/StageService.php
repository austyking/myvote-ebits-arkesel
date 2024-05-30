<?php

namespace App\Services\UssdSessions;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\Position;
use App\Models\Vote;
use App\Services\Vote\VoteService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class StageService
{
    private ProcessSessions $processSessions;
    private VoteService $voteService;

    public function __construct(ProcessSessions $processSessions, VoteService $voteService)
    {
        $this->processSessions = $processSessions;
        $this->voteService = $voteService;
    }

    public function stageOne($data, $requestBody): array
    {
        $key = $this->processSessions->getCacheKey($requestBody);
        $positions = $data['positions'];

        $selectedPosition = $positions[$requestBody['userData']];
        if ($selectedPosition) {
            $candidates = $selectedPosition->candidates;

            if (count ($candidates) < 1){
                return [
                    'status' => 'completed',
                    'message' => "There are no candidates defined for $selectedPosition->name. Please come back later",
                ];
            }

            $candidateString = '';
            $candidatesArray = [];
            $candidates->each (function (Candidate $candidate, int $index) use (&$candidateString, &$candidatesArray) {
                $candidateNumber = ++$index;
                $candidatesArray[$candidateNumber] = $candidate;
                return $candidateString .= $candidateNumber . '. '. $candidate->name ."\n";
            });

            $data['next_stage'] = 'ST2';
            $data['candidates'] = $candidatesArray;
            $data['candidatesList'] = $candidateString;
            $data['selectedPosition'] = $selectedPosition;
            Cache::put($key, $data, 1200);

            $message = sprintf($this->getNextStageMessage ('ST2'), $selectedPosition->name, $candidateString);

            return [
                'message' => $message,
                'status' => 'next',
            ];
        }

        return [
            'message' => 'Invalid position selected',
            'status' => 'completed'
        ];
    }

    public function stageTwo($data, $requestBody): array
    {
        $key = $this->processSessions->getCacheKey($requestBody);
        $candidates = $data['candidates'];

        $selectedCandidate = $candidates[$requestBody['userData']];
        if ($selectedCandidate) {
            $data['next_stage'] = 'ST3';
            $data['selectedCandidate'] = $selectedCandidate;
            Cache::put($key, $data, 1200);

            $position = $data['selectedPosition'];

            $message = sprintf($this->getNextStageMessage ('ST3'), $selectedCandidate->name, $position->name);
            return [
                'message' => $message,
                'status' => 'next',
            ];
        }

        return [
            'message' => 'Invalid candidate selected',
            'status' => 'completed'
        ];
    }

    public function stageThree($data, $requestBody): array
    {
        $key = $this->processSessions->getCacheKey($requestBody);
        $election = $data['election'];

        if ($requestBody['userData'] == 1) { // Yes
            $selectedCandidate = $data['selectedCandidate'];
            $selectedPosition = $data['selectedPosition'];
            $voter = $data['voter'];

            if ($this->voteService->vote ($election, $selectedCandidate, $selectedPosition, $voter)){
                $data['next_stage'] = 'ST4';

                $remainingPositions = $this->voteService->notVotedPositions ($voter, $election);
                $data['remainingPositions'] = $remainingPositions;

                Cache::put($key, $data, 1200);

                $message = sprintf($this->getNextStageMessage ('ST4'), $selectedCandidate->name, $selectedPosition->name, count ($remainingPositions));
                $status = 'next';
            } else {
                $message = 'Failed to vote. Please try again later';
                $status = 'completed';
            }

            return [
                'message' => $message,
                'status' => $status,
            ];
        }

        if ($requestBody['userData'] == 2) { // Back to Candidate selection (ST2)
            $data['next_stage'] = 'ST2';
            Cache::put($key, $data, 1200);
            $message = sprintf($this->getNextStageMessage ('ST2'), $data['selectedPosition']->name, $data['candidatesList']);

            return [
                'message' => $message,
                'status' => 'next',
            ];
        }

        if ($requestBody['userData'] == 3) { // Back to Position selection (ST1)
            $data['next_stage'] = 'ST1';
            Cache::put($key, $data, 1200);
            $positionsList = !empty($data['remainingPositions']) ? $data['remainingPositions'] : $data['positionsList'];
            $message = sprintf($this->getNextStageMessage ('ST1'), $election->name, $positionsList);

            return [
                'message' => $message,
                'status' => 'next',
            ];
        }

        return [
            'message' => 'Invalid option selected',
            'status' => 'completed'
        ];
    }

    public function stageFour($data, $requestBody): array
    {
        $key = $this->processSessions->getCacheKey($requestBody);
        $election = $data['election'];

        if (count ($data['remainingPositions']) < 1) {
            return [
                'message' => 'Thank you for exercising your right! Sit tight and expect results!',
                'status' => 'completed'
            ];
        }

        if ($requestBody['userData'] == 1) { // Continue voting
            $positions = $data['remainingPositions'];
            $positionString = '';
            $positionsArray = [];
            $positions->each (function (Position $position, int $index) use (&$positionString, &$positionsArray) {
                $positionNumber = ++$index;
                $positionsArray[$positionNumber] = $position;
                return $positionString .= $positionNumber . '. '. $position->name ."\n";
            });

            $data['next_stage'] = 'ST1';
            $data['positions'] = $positionsArray;
            $data['positionsList'] = $positionString;

            Cache::put($key, $data, 1200);
            $message = sprintf($this->getNextStageMessage ('ST1'), $election->name, $data['positionsList']);

            return [
                'message' => $message,
                'status' => 'next',
            ];
        }

        if ($requestBody['userData'] == 2) { // Exit
            $remainingPositions = $data['remainingPositions'];
            Cache::delete ($key);

            $message = 'Thank you exercising your right.';
            if (count ($remainingPositions) > 0){
                $message .= "\nYou still have ".count ($data['remainingPositions'])." positions to vote. You may dial again at any time between now and $election->end_date to complete voting";
            }

            return [
                'message' => $message,
                'status' => 'completed',
            ];
        }

        return [
            'message' => 'Invalid option selected',
            'status' => 'completed'
        ];
    }

    public function getNextStageMessage(string $stage): ?string
    {
        $stages = [
            'ST1' => "Welcome to %s. Kindly select a category to start voting\n %s",
            'ST2' => "Choose your candidate for the %s position.\n %s",
            'ST3' => "Are you sure you want to vote %s for %s?.\n 1. Yes\n2. Back to Candidate selection\n 3. Back to Position selection",
            'ST4' => "You have successfully voted %s for %s.\n 1. Continue (%s positions remaining)\n2. Exit",
        ];

        return $stages[$stage] ?? null;
    }
}
