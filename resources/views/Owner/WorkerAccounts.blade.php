@extends('Owner/sidebar')
<link href="{{ asset('css/owner/workeraccount.css') }}" rel="stylesheet">
@section('title', 'Worker Account')
@section('content')

    @php
        use Illuminate\Support\Facades\DB;
        use Illuminate\Support\Facades\Crypt;
        use App\Models\Owner;
        use App\Models\Admin;
        use App\Models\Worker;

        $accs = DB::table('tbl_workeraccounts')
            ->join('tbl_tower', 'tbl_workeraccounts.OwnerID', '=', 'tbl_tower.OwnerID')
            ->where('tbl_workeraccounts.OwnerID', Auth::id())
            ->select('tbl_workeraccounts.*', 'tbl_tower.name as tower_name', 'tbl_tower.name as towerid')
            ->get();
        $counter = 1;

    @endphp
    <div class="container">
        <div class="row text-start">
            <div id="wrapper">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
                <nav>
                    <div id="page-wrapper">
                        <div class="row">
                            <div class="col-lg-12">
                                <h4 class="page-header"></h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="panel-heading">
                                    Owner Worker Accounts List
                                </div>

                                <div class="panel-body">
                                    <div class="dataTable_wrapper">
                                        <!-- Active Accounts Table -->
                                        <h3>Active Accounts</h3>
                                        <table class="table table-striped table-bordered table-hover"
                                            id="dataTables-example-active">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Name</th>
                                                    <th>Username</th>
                                                    <th>Tower</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($accs as $user)
                                                    @if (Crypt::decryptString($user->status) == '1')
                                                        <tr class="odd gradeX">
                                                            <td>{{ $loop->iteration }}</td>
                                                            <td>{{ Crypt::decryptString($user->name) }}</td>
                                                            <td>{{ Crypt::decryptString($user->username) }}</td>
                                                            <td>{{ Crypt::decryptString($user->tower_name) }}</td>
                                                            <td>
                                                                <div class="btn-group">
                                                                    <a href="{{ route('ownerworker.edit', $user->id) }}"c1
                                                                        class="btn btn-primary btn-rounded">Edit</a>
                                                                    <form action="{{ route('ownerworker.dis', $user->id) }}"
                                                                        method="POST">
                                                                        @csrf
                                                                        <button
                                                                            onclick="return confirm('Are you sure you want to delete this?')"
                                                                            type="submit"
                                                                            class="btn btn-danger btn-rounded">
                                                                            Disable
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>

                                        <!-- Disabled Accounts Table -->
                                        <h3>Disabled Accounts</h3>
                                        <table class="table table-striped table-bordered table-hover"
                                            id="dataTables-example-disabled">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Name</th>
                                                    <th>Username</th>
                                                    <th>Tower</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($accs as $user)
                                                    @if (Crypt::decryptString($user->status) == '0')
                                                        <tr class="odd gradeX">
                                                            <td>{{ $loop->iteration }}</td>
                                                            <td>{{ Crypt::decryptString($user->name) }}</td>
                                                            <td>{{ Crypt::decryptString($user->username) }}</td>
                                                            <td>{{ Crypt::decryptString($user->tower_name) }}</td>
                                                            <td>
                                                                <div class="btn-group">
                                                                    <form action="{{ route('ownerworker.en', $user->id) }}"
                                                                        method="POST">
                                                                        @csrf
                                                                        <button
                                                                            onclick="return confirm('Are you sure you want to enable this?')"
                                                                            type="submit"
                                                                            class="btn btn-secondary ti-trash btn-rounded">
                                                                            Enable
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>

            </div>
        </div>
        <a href="{{ route('addworker') }}" class="btn btn-success mt-1">
            Add Worker Account
        </a>
    </div>


@endsection
