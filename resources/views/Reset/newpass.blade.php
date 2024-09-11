@extends('layout')
@section('title', 'Password')

@section('content')

<style>
    .card{
        width: 500px;
    }
</style>

<div class="container">

    <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

                   
                    <div class="card mb-3" >
                        <div class="card-body">
                            <div class="pt-4 pb-2">
                                <h5 class="card-title text-center pb-0 fs-4">Enter New Password</h5>
                                <br>
                                @if ($errors->any())
                                <div class="alert alert-danger mt-3 mb-0">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                                
                                <form method="POST" action="{{ route('reset-password-form') }}">
                                    @csrf
                                    
                                    <div class="mb-3">
                                        <label for="password" class="form-label">New Password:</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="password_confirmation" class="form-label">Confirm Password:</label>
                                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                                    </div>

                                    <div class="text-center">
                                        <button type="submit" class="btn btn-success">Confirm</button>
                                    </div>
                                </form>

                            
                            
                          
                            
                                <div class="col-12 mb-1 text-center">
                                    <p class="small mb-0"><a href="{{ route('cancel') }}">Login?</a></p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </section>
</div>

@endsection
