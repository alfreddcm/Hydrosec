@extends('Owner/sidebar')
@section('title', 'Dashboard')
@section('content')
<style>

.table-responsive {
    max-height: 400px; /* Adjust the height as needed */
    overflow-y: auto;
}

.table thead th {
    position: sticky;
    top: 0;
    background-color: #f8f9fa; /* Bootstrap’s light background color for headers */
    z-index: 1; /* Ensure header stays on top of other rows */
}


</style>
<div class="container">
<div class="container mt-5">
        <div class="row">
            <!-- Tower Count Card -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Tower Count</h5>
                    </div>
                    <div class="card-body">
                        <h1 class="display-4">{{ $towerCount }}</h1>
                    </div>
                </div>
            </div>

            <!-- Worker Count Card -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Worker Count</h5>
                    </div>
                    <div class="card-body">
                        <h1 class="display-4">{{ $workerCount }}</h1>
                    </div>
                </div>
            </div>
        </div>

                
</div>
</tbody>
</table>

<div class="mb-4">
    <h2>Tower Alert Logs</h2>
<div class="table-responsive">
    <table class="table table-striped">
        <thead class="thead-light">
            <tr>
                <th>Tower Name</th>
                <th>Tower Code</th>
                <th>Alert Activity</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            @foreach($towerLogs as $log)
                <tr>
                    <td>{{ Crypt::decryptString($log->tower_name) }}</td>
                    <td>{{ Crypt::decryptString($log->tower_code) }}</td>
                    <td>{{ Crypt::decryptString($log->activity) }}</td>
                    <td>{{ \Carbon\Carbon::parse($log->created_at)->format('g:i A D m/d/Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

</div>

</div>


@endsection