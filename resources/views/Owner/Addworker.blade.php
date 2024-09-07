@extends('Owner/sidebar')
@section('title', 'Manage Profile')
@section('content')
@php
use Illuminate\Support\Facades\Auth;
use App\Models\Tower;
use Illuminate\Support\Facades\Crypt;

 $userId = Auth::id();
$towers = Tower::where('OwnerID', $userId)->get();
@endphp
<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-body">

                    <a href="{{ route('ownerworkeraccount') }}">Back</a>

                    <h5 class="card-title">Woker Profile Information</h5>
                    <p class="card-text">Instructions</p>

                    <form action="{{ route('addownerworkeraccount') }}" method="post" autocomplete="off">
                        @csrf <!-- Ensure CSRF protection -->

                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name"  autocomplete="off" required>
                            @error('name')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-2">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username"  autocomplete="off" required>
                            @error('username')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-2">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password"  autocomplete="off" required>
                            @error('password')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-2">
                            <label for="tower" class="form-label">Towers</label>
                            <select id="tower" name="tower" class="form-select" required>
                                <option value="" disabled selected>Select a tower</option>
                                @foreach ($towers as $tower)
                                    <option value="{{ $tower->id }}">{{ Crypt::decryptString($tower->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        

                        <div class="mb-2 text">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>


@endsection