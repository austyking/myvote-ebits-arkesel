<?php

namespace App\Services\UssdSessions;

use App\Services\Election\ElectionService;
use App\Services\Vote\VoteService;

class UssdSessionService
{
    public function handleUssdSession(
        array $requestBody,
        ProcessSessions $processSessions,
        StageService $stageService,
        ElectionService $electionService,
        VoteService $voteService
    ): array {
        // Handle timeouts
        if ($requestBody['userData'] == 'User timeout') {
            return $this->response ($requestBody, 'Your session may have timed out or unknown error occurred', false);
        }

        $response = $processSessions->handleNewUssdSession($requestBody, $stageService, $electionService, $voteService);

        if ($response['status'] === 'completed') {
            return $this->response ($requestBody, $response['message'], false);
        }

        if ($response['status'] === 'success') {
            return $this->response ($requestBody, $response['message'], true);
        }

        if ($response['status'] === 'next') {
            $response = $processSessions->handleSubsequentUssdSessions($requestBody, $stageService);

            if ($response['status'] === 'completed') {
                return $this->response ($requestBody, $response['message'], false);
            }

            if ($response['status'] === 'error') {
                $message = isset($response['message'])
                    ? $this->setMessage($response['message'])
                    : $this->setMessage('Invalid user or session!');

                return $this->response ($requestBody, $message, false);
            }
        }

        return $this->response ($requestBody, $response['message'], true);
    }

    public function setMessage(string $message): string
    {
        return $message. "\n\nPowered by EBITS";
    }

    private function response(array $requestBody, string $message, bool $continueSession): array
    {
        return [
            'sessionID' => $requestBody['sessionID'],
            'userID' => $requestBody['userID'],
            'msisdn' => $requestBody['msisdn'],
            'message' => $this->setMessage ($message),
            'continueSession' => $continueSession,
        ];
    }
}
