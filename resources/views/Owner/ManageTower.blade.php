@extends('Owner/sidebar')
<link href="{{ asset('css/owner/managetower.css') }}" rel="stylesheet">
@section('title', 'Manage Tower')
@section('content')
    @php
        use Illuminate\Support\Facades\Auth;
        use App\Models\Tower;
        use App\Models\Owner;

        $towers = Tower::where('OwnerID', Auth::id())->get();

    @endphp
    <style>
        .addtowerb {
            position: absolute;
            bottom: 20px;
            right: 20px;
        }

        .addtowerbutton {
            position: sticky;
        }

        a {
            text-decoration: none;
        }

        .card {
            text-transform: uppercase;
        }

        .card {
            border-radius: 10px;
            transition: transform 0.2s ease-in-out;
        }

        .card:hover {
            transform: scale(1.02);
        }

        .card-title {
            letter-spacing: 1px;
        }

        .card-text span.font-weight-bold {
            font-size: 1.1em;
        }

        .badge {
            font-size: 1em;
            padding: 5px 10px;
            color: #000;
        }

        .card-body img {
            height: 100px;
        }

        .no-line-spacing {
            margin-bottom: 0;
            padding-bottom: 0;
        }
    </style>
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="row">
                    <h4>Tower List:</h4>
                    @if ($towers->isNotEmpty())
                        @foreach ($towers as $data)
                            @php
                                $decryptedStatus = Crypt::decryptString($data->status);
                            @endphp
                    
                            @if ($decryptedStatus == '0' || $decryptedStatus == '1')
                                <div class="col-sm-3">
                                    <a href="{{ route('towerdata', ['id' => $data->id]) }}">
                                        <div class="card shadow-sm mb-4 border-0">
                                            <div class="card-body">
                                                <div class="row g-0 mr-0">
                                                    <div class="col-sm-4">
                                                        <img src="{{ asset('images/icon/towericon.png') }}" alt="towericon">
                                                    </div>
                                                    <div class="col">
                                                        <h5 class="card-title text-uppercase text-primary">
                                                            <b>{{ $data->id }} {{ Crypt::decryptString($data->name) }}</b>
                                                        </h5>
                                                        <p class="card-text">
                                                            <span class="text-muted">Code:</span> 
                                                            <span class="font-weight-bold">{{ Crypt::decryptString($data->towercode) }}</span>
                                                        </p>
                                                        <p class="card-text">
                                                            <b>Status:</b>
                                                            <span class="badge">
                                                                {{ $decryptedStatus == '1' ? 'Active' : 'Pending' }}
                                                            </span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @elseif ($decryptedStatus == '4')
                                <!-- Display card for towers ready for harvesting -->
                                <div class="col-sm-3">
                                    <div class="card shadow-sm mb-1 border-0">
                                        <div class="card-body">
                                            <a href="">

                                                <h5 class="card-title text-uppercase text-success">
                                                Tower {{ Crypt::decryptString($data->name) }} ready for harvesting!
                                                <p class="card-text">
                                                    <span class="text-muted">Code:</span> 
                                                    <span class="font-weight-bold">{{ Crypt::decryptString($data->towercode) }}</span>
                                                </p>
                                                <center>
                                                    <form action="{{ route('tower.restart') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="tower_id" value="{{ $data->id }}">
                                                    <button type="submit" class="btn btn-primary mb-1">Restart</button>
                                                </form>
                                                </center>
                                                
                                                
                                            </h5>
                                            </a>
                                            
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @else
                        <span>No towers</span>
                    @endif
                    


                </div>
                <div class="addtowerb">
                    <a href="#" class="btn btn-success mt-1 addtowerbutton" data-bs-toggle="modal"
                        data-bs-target="#addTowerModal">
                        Add Tower
                    </a>
                </div>

            </div>
        </div>
    </div>



    <!-- Bootstrap Modal -->
    <div class="modal fade" id="addTowerModal" tabindex="-1" aria-labelledby="addTowerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTowerModalLabel">Add Tower</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('posttower') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="towercode" class="form-label">Tower Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="towercode" class="form-label">Tower Code</label>
                            <input type="text" class="form-control" id="towercode" name="towercode" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- <form action="{{ url('/send-to-esp') }}" method="POST">
    @csrf
    <input type="text" name="data" value="your_data_here">
    <button type="submit" class="btn btn-primary">Send to ESP8266</button>
</form> --}}


    <script>
    
    </script>

@endsection
