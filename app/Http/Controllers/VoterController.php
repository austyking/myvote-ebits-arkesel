<?php

namespace App\Http\Controllers;

use App\Models\Election;
use App\Models\Voter;
use Illuminate\Http\Request;

class VoterController extends Controller
{
    public function index(Election $election)
    {
        $voters = Voter::where('election_id', $election->id)->get();
        return view('voters.index', compact('election', 'voters'));
    }

    public function create(Election $election)
    {
        return view('voters.create', compact('election'));
    }

    public function store(Request $request, Election $election)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15|unique:voters',
        ]);

        Voter::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'election_id' => $election->id,
        ]);

        return redirect()->route('elections.voters.index', $election->id)->with('success', 'Voter registered successfully.');
    }

    public function show(Voter $voter)
    {
        $election = $voter->election;
        return view('voters.show', compact('voter', 'election'));
    }

    public function edit(Voter $voter)
    {
        $election = $voter->election;
        return view('voters.edit', compact('voter', 'election'));
    }

    public function update(Request $request, Voter $voter)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15|unique:voters,phone,' . $voter->id,
        ]);

        $voter->update($request->all());

        return redirect()->route('elections.voters.index', $voter->election_id)->with('success', 'Voter updated successfully.');
    }

    public function destroy(Voter $voter)
    {
        $voter->delete();

        return redirect()->route('elections.voters.index', $voter->election_id)->with('success', 'Voter deleted successfully.');
    }
}
