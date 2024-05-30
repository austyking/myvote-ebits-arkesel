<?php

namespace App\Http\Controllers;

use App\Models\Election;
use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Election $election)
    {
        $positions = $election->positions;
        return view('positions.index', compact('election', 'positions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Election $election)
    {
        return view('positions.create', compact('election'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Election $election)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $election->positions()->create($request->all());
        return redirect()->route('elections.positions.index', $election->id)->with('success', 'Position created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Position $position)
    {
        return view('positions.show', compact('position'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Position $position)
    {
        return view('positions.edit', compact('position'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Position $position)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $position->update($request->all());
        return redirect()->route('elections.positions.index', $position->election_id)->with('success', 'Position updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Position $position)
    {
        $position->delete();
        return redirect()->route('elections.positions.index', $position->election_id)->with('success', 'Position deleted successfully.');
    }
}
