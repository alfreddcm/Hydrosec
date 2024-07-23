@extends('Owner/sidebar')
<link href="{{ asset('css/owner/workeraccount.css') }}" rel="stylesheet">
@section('title', 'Worker Account')
@section('content')
    <div class="container">
        <div class="row text-start">
        </div>
        <a href="{{route('addworker')}}" class="btn btn-success mt-1">
            Add Worker Account
        </a>
    </div>


@endsection
