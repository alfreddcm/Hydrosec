@extends('layout')

@section('title', 'Hydrosec')

@section('content')


<div class="container mt-5" style="max-width: 750px">

    <h1>Laravel Send Email using PHPMailer Example - ItSolutionStuff.com</h1>

    @if ($message = Session::get('success'))
        <div class="alert alert-success  alert-dismissible">
            <strong>{{ $message }}</strong>
        </div>
    @endif

    @if ($message = Session::get('error'))
        <div class="alert alert-danger  alert-dismissible">
            <strong>{{ $message }}</strong>
        </div>
    @endif

    <form method="post" action="{{ route('send.email.post') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label>Recipient Email:</label>
            <input type="email" name="email" class="form-control" />
        </div>
        <div class="form-group">
            <label>Subject:</label>
            <input type="text" name="subject" class="form-control" />
        </div>
        <div class="form-group">
            <label>Body:</label>
            <textarea class="form-control" name="body"></textarea>
        </div>
        <div class="form-group mt-3 mb-3">
            <button type="submit" class="btn btn-success btn-block">Send Email</button>
        </div>
    </form>

</div>

<footer class="fixed-bottom">
    <hr class="text-black-50">
    <div class="container text-center">
        <div class="row padding-0">
            <div class="col copyright">
                <p>© 2025 Hydrosec All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>
@endsection
