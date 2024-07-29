@extends('Owner/sidebar')
@section('title', 'Dashboard')
@section('content')

<div class="container">

    <div class="card mb-3">
        <div class="row tower">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title">TOWER 1</h2>
                        <p class="card-text">Juan Mentiz</p>
                        <p class="card-text">
                        <div class="row justify-content-center align-items-center g-2">
                            <div class="col">
                                Nutrient Level: 76%
                            </div>
                            <div class="col">
                                pH: 6.1
                            </div>
                            <div class="col">
                                Temperature: 27°C
                            </div>
                        </div>
                        </p>
                        <p class="card-text">
                            <a name="" id="" class="btn btn-primary" href="#" role="button">START
                                CYCLE </a>
                        </p>
                        <p class="card-text">Start Date: Sun, May 12, 2024</p>
                        <p class="card-text">Expected Harvest Date: June 26, 2024</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
<h1 class="mt-5">Sensor Data</h1>
@php
$data = DB::table('sensor')->get();
@endphp
        <table class="table table-striped mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>pH</th>
                    <th>Temperature</th>
                    <th>Nutrient Level</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                </tr>
            </thead>
            <tbody>
                
                @foreach ($data as $sensor)
                    <tr>
                        <td>{{ $sensor->id }}</td>
                        <td>{{ $sensor->pH }}</td>
                        <td>{{ $sensor->temperature }}</td>
                        <td>{{ $sensor->nutrientlevel }}</td>
                        <td>{{ $sensor->status }}</td>
                        <td>{{ $sensor->created_at }}</td>
                        <td>{{ $sensor->updated_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
</div>

@endsection
