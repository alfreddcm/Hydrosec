@extends('Owner/sidebar')
@section('title', 'Dashboard')
@section('content')

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
</div>


@endsection