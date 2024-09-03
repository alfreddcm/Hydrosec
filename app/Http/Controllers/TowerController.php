<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;
use App\Models\Tower;
use App\Models\Owner;
use Illuminate\Support\Facades\Auth;


use Illuminate\Support\Facades\Crypt;


class TowerController extends Controller
{
    /**
     * Store a newly created tower in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
{
    // Validate the request data
    $request->validate([
        'name'=>'required',
        'towercode' => 'required|string|max:4',
    ], [
        'towercode.required' => 'The tower code is required.',
        'towercode.string' => 'The tower code must be a string.',
        'towercode.max' => 'The tower code may not be greater than 4 characters.',
    ]);

    // Check if a tower with the given code already exists
    $existingTower = Tower::all();
    foreach ($existingTower as $tower) {
        $codedecrypted = Crypt::decryptString($tower->towercode);
        if ($codedecrypted === $request->input('towercode')) {
            return redirect()->back()->with('error', 'Tower with this code already exists.');
        }
    }

    $ownerId = auth::id();
    $tower = new Tower;
    $tower->OwnerID =$ownerId;
    $tower->name = Crypt::encryptString($request->input('name'));
    $tower->towercode = Crypt::encryptString($request->input('towercode'));
    $tower->save();
    return redirect()->back()->with('success', 'Tower added and owner updated successfully!');
}



public function updateDates(Request $request)
{
    // Cast the 'days' input to an integer
    $days = (int) $request->input('days', 0); // Default to 0 if not present
    $newDays = (int) $request->input('newDays', 0); // Default to 0 if not present
    $towerId = $request->input('tower_id');
    
    $tower = Tower::find($towerId);

    if ($tower) {
        if ($days > 0) {
            // Handle starting a new cycle
            $startdate = Carbon::now();
            $enddate = $startdate->copy()->addDays($days);

            $tower->startdate = $startdate;
            $tower->enddate = $enddate;
            $tower->save();

            Log::info('New cycle started', [
                'tower_id' => $tower->id,
                'date_started' => $startdate,
                'date_end' => $enddate,
            ]);

            return redirect()->back()->with('success', 'Cycle started successfully!');
        } elseif ($newDays > 0) {
            // Handle updating an existing cycle
            $startdate = $tower->startdate;
            $enddate = Carbon::parse($startdate)->addDays($newDays);

            $tower->enddate = $enddate;
            $tower->save();

            Log::info('Cycle dates updated', [
                'tower_id' => $tower->id,
                'date_end' => $enddate,
            ]);

            return redirect()->back()->with('success', 'Cycle dates updated successfully!');
        }

        // If no valid input is provided
        return redirect()->back()->with('error', 'Invalid input.');
    }

    // Log the error if the tower was not found
    Log::error('Failed to handle cycle - Tower not found', [
        'tower_id' => $towerId,
    ]);

    return redirect()->back()->with('error', 'Tower not found!');
}
    }


