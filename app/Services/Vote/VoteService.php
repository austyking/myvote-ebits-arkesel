<?php

namespace App\Services\Vote;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\Position;
use App\Models\Voter;
use Illuminate\Database\Eloquent\Collection;
use LaravelIdea\Helper\App\Models\_IH_Position_C;

class VoteService
{
    public function vote(Election $election, Candidate $candidate, Position $position, Voter $voter)
    {
        return $voter->votes ()->create ([
            'election_id' => $election->id,
            'candidate_id' => $candidate->id,
            'position_id' => $position->id
        ]);
    }

    public function notVotedPositions(Voter $voter, Election $election): array|Collection|_IH_Position_C
    {
        $positionsVoted = $voter->votes()->where('election_id', $election->id)->pluck('position_id')->toArray();
        return $election->positions()->whereNotIn('id', $positionsVoted)->get();
    }
}
