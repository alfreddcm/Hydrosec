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
    .addtowerb{
        position: absolute;
    bottom: 20px;
    right: 20px;

    
}
    .addtowerbutton{
        position: sticky;
}
a{
    text-decoration: none;
}
.card{
    text-transform: uppercase;
}
</style>
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="row">
                    <h4>Tower List:</h4>
                    @if ($towers->isNotEmpty())
                        @foreach ($towers as $data)
                            <div class="col-sm-3">
                                <a href="{{ route('towerdata', ['id' => $data->id]) }}">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <span><b>{{ $data->id }}</b></span>
                                                {{ Crypt::decryptString($data->name) }}</h5>
                                            <p class="card-text">
                                               Code: {{ Crypt::decryptString($data->towercode) }}
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    @else
                        <span>No towers</span>
                    @endif


                </div>
                <div class="addtowerb">
                    <a href="#" class="btn btn-success mt-1 addtowerbutton" data-bs-toggle="modal" data-bs-target="#addTowerModal">
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
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: '{{ session('success') }}',
                    timer: 3000,
                    showConfirmButton: false
                });
            @elseif (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '{{ session('error') }}',
                    timer: 3000,
                    showConfirmButton: false
                });
            @endif
        });
    </script>

@endsection
