@extends('Owner/sidebar')
<link href="{{ asset('css/owner/managetower.css') }}" rel="stylesheet">
@section('title', 'Manage Tower')
@section('content')


    <div class="container">
        <div class="row">
            <div class="col">

                <div class="row">
                    <h4>Tower List:</h4>

                    <div class="col-sm-3">
                        <a href="">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Tower 1</h5>
                                    <p class="card-text">
                                        Nutrient Level:
                                    </p>
                                </div>
                            </div>

                        </a>

                    </div>
                </div>
                <!-- route('addtower') -->
                <a href="{{ }}" class="btn btn-success mt-1">
                    Add Tower
                </a>
            </div>
        </div>


    </div>


@endsection
