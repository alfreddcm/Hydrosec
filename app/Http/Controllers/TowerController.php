<?php

namespace App\Http\Controllers;

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

    }


