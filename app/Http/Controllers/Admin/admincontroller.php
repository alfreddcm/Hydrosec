<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Owner;
use App\Models\Admin;
use App\Models\Worker;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class admincontroller extends Controller
{
    public function edit($id)
    {
        $user = Owner::find($id);
        $user->name = Crypt::decryptString($user->name);

        $user->username = Crypt::decryptString($user->username);
        $user->email = Crypt::decryptString($user->email);
        return view('Admin.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required',
        ]);

        $user = Owner::find($id);
        $user->name = Crypt::encryptString($request->input('name'));
        $user->username = Crypt::encryptString($request->input('username'));
        $user->email = Crypt::encryptString($request->input('email'));
        $user->save();

        return redirect()->route('UserAccounts')->with('success', 'User updated successfully.');
    }

    

        public function destroy(int $id)
    {
        try {
            Owner::destroy($id);
        } catch (\Exception $exception){
            echo $exception->getMessage();
        }
        return redirect(route('UserAccounts'));
    }
}
