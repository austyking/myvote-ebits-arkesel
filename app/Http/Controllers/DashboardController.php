<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\Position;
use App\Models\Voter;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $electionId = $request->input('election_id');

        // Fetch all positions and elections
        $positions = Position::all();
        $elections = Election::all();

        // Fetch counts for the dashboard
        $activeElectionsCount = Election::ongoing()->count();
        $totalCandidates = Candidate::count();
        $totalPositions = Position::count();
        $totalVoters = Voter::count();

        return view('dashboard', compact('elections', 'positions', 'electionId', 'activeElectionsCount', 'totalCandidates', 'totalPositions', 'totalVoters'));
    }

    public function getVotingData(Request $request)
    {
        $electionId = $request->input('election_id');
        $positionId = $request->input('position_id');

        $votingData = $this->fetchVotingData($electionId, $positionId);

        return response()->json($votingData);
    }

    private function fetchVotingData($electionId, $positionId)
    {
        $votingData = [
            'overallVotes' => [],
            'votesPerPosition' => []
        ];

        // Fetch all candidates with their positions and votes
        $candidates = Candidate::when($electionId, function($query, $electionId) {
            return $query->whereHas('position.election', function($query) use ($electionId) {
                $query->where('id', $electionId);
            });
        })->when($positionId, function($query, $positionId) {
            return $query->where('position_id', $positionId);
        })->with('position')->get();

        // Prepare data vote counts
        foreach ($candidates as $candidate) {
            $votes = $candidate->votes->count () ?? 0;
            $votingData['overallVotes'][$candidate->name] = $votes;

            $positionName = $candidate->position->name;

            if (!isset($votingData['votesPerPosition'][$positionName])) {
                $votingData['votesPerPosition'][$positionName] = [
                    'categories' => [],
                    'data' => []
                ];
            }

            $votingData['votesPerPosition'][$positionName]['categories'][] = $candidate->name;
            $votingData['votesPerPosition'][$positionName]['data'][] = $votes;
        }

        return $votingData;
    }
}
