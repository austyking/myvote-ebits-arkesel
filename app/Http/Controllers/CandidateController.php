<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class CandidateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Election $election, Position $position)
    {
        $candidates = $position->candidates;
        return view('candidates.index', compact('position', 'candidates', 'election'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Election $election, Position $position)
    {
        return view('candidates.create', compact('position', 'election'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Election $election, Position $position)
    {
        $request->validate([
            'name' => 'required',
            'image' => 'nullable|image|max:2048',
        ]);

        DB::transaction (function() use ($position, $request, $election){
            $position->candidates()->create([
                'name' => $request->name,
                'election_id' => $election->id
            ]);

            if ($request->hasFile('image')) {
                try {
//                    $candidate->addMediaFromRequest('image');
                } catch (FileDoesNotExist|FileIsTooBig $e) {
                    return redirect()->route('elections.positions.candidates.index', $position->id)
                        ->with('success', $e->getMessage ());
                }
            }
        });

        return redirect()->route('elections.positions.candidates.index', [$election->id, $position->id])
            ->with('success', 'Candidate created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Candidate $candidate)
    {
        return view('candidates.show', compact('candidate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Candidate $candidate)
    {
        return view('candidates.edit', compact('candidate'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Candidate $candidate)
    {
        $request->validate([
            'name' => 'required',
            'image' => 'nullable|image|max:2048',
        ]);

        $candidate->update($request->only('name'));

        if ($request->hasFile('image')) {
//            $candidate->clearMediaCollection('images');
            try {
//                $candidate->addMediaFromRequest ('image')->toMediaCollection ('images');
            } catch (FileDoesNotExist|FileIsTooBig $e) {
                return redirect()->route('elections.positions.candidates.index', $candidate->position_id)
                    ->with('success', $e->getMessage ());
            }
        }

        return redirect()->route('elections.positions.candidates.index', $candidate->position_id)
            ->with('success', 'Candidate updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Candidate $candidate)
    {
        $candidate->delete();
        return redirect()->route('elections.positions.candidates.index', $candidate->position_id)->with('success', 'Candidate deleted successfully.');
    }
}
