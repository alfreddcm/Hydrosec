<?php

namespace App\Http\Controllers;

use App\Models\Tower;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

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
            'name' => 'required',
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

        $tower = Tower::create([
            'OwnerID' => $ownerId,
            'name' => Crypt::encryptString($request->name),
            'towercode' => Crypt::encryptString($request->towercode),
            'status' => Crypt::encryptString('0'),
            'mode' => Crypt::encryptString('0'),
        ]);

        // Check if the tower was created successfully
        if ($tower) {
            return redirect()->back()->with('success', 'Tower added and owner updated successfully!');
        } else {
            return redirect()->back()->with('error', 'Failed to create tower');
        }
    }

    public function updateDates(Request $request)
    {
        $days = (int) $request->input('days', 0);
        $newDays = (int) $request->input('newDays', 0);
        $towerId = $request->input('tower_id');

        $tower = Tower::find($towerId);

        if ($tower) {
            if ($days > 0) {
                // Handle starting a new cycle
                $startdate = Carbon::now();
                $enddate = $startdate->copy()->addDays($days);
                $tower->status = Crypt::encryptString('1');
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

    public function modestat(Request $request, $id)
    {
        $tower = Tower::find($id)->first();

        $decrypted_data = [
            'mode' => Crypt::decryptString($tower->mode),
            'status' => Crypt::decryptString($tower->status),
        ];

        return response()->json(['modestat' => $decrypted_data]);
    }
}
