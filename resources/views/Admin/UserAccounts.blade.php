@extends('Admin/sidebar')
@section('title', 'Manage User Accounts')
@section('content')
@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Crypt;
    use App\Models\Owner;
    use App\Models\Admin;
    use App\Models\Worker;


    $accs = DB::table('tbl_useraccounts')->get();
    

@endphp
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<div class="container">

    <div id="wrapper">
        <nav>
            <div id="page-wrapper">
                <div class="row">
                    <div class="col-lg-12">
                        <h4 class="page-header"></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Owner Accounts List
                            </div>
                            <div class="panel-body">
    <div class="dataTable_wrapper">
        <table class="table table-striped table-bordered table-hover" id="dataTables-example">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($accs as $user)
                <tr class="odd gradeX owner-row">
                    <td>{{ $user->id }}</td>
                    <td>{{ Crypt::decryptString($user->name) }}</td>
                    <td>{{ Crypt::decryptString($user->username) }}</td>
                    <td>{{ Crypt::decryptString($user->email) }}</td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('admin.edit', $user->id) }}" class="btn btn-success">Edit</a>

                            <form action="/student/delete/{{ $user->id }}" method="post">
                                @method('DELETE')
                                @csrf
                                <button onclick="return confirm('Are you sure You want to delete this?')" type="submit"
                                    class="btn btn-danger ti-trash btn-rounded">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                <tr class="workerlist">
                    <td colspan="5">
                        <table class="table table-bordered worker-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Worker Name</th>
                                    <th>Worker Username</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $worker = DB::table('tbl_workeraccount')->get();
                                $counter2 = 1;
                                @endphp

                                @foreach ($worker as $workerUser)
                                @if (Crypt::decryptString($workerUser->OwnerID) == $user->id)

                                <tr>
                                    <td>{{ $counter2++ }}</td>
                                    <td>{{ Crypt::decryptString($workerUser->name) }}</td>
                                    <td>{{ Crypt::decryptString($workerUser->username) }}</td>
                                    <td>
                                        <div class="btn-group">
                            <a href="{{ route('admin.edit2', $workerUser->id) }}" class="btn btn-success">Edit</a>

                            <form action="/#/{{ $workerUser->id }}" method="post">
                                @method('DELETE')
                                @csrf
                                <button onclick="return confirm('Are you sure You want to delete this?')" type="submit"
                                    class="btn btn-danger ti-trash btn-rounded">
                                    Delete
                                </button>
                            </form>
                        </div>
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                </tr>
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
<style>
.workerlist {
    display: none;
}

.owner-row:hover + .workerlist,
.workerlist:hover {
    display: table-row;
}

.workerlist td {
    padding: 3px;
    border-top: none;
}

.worker-table {
    margin: 2px;
    width: 100%;
}

.worker-table th, .worker-table td {
    padding: 5px;
    text-align: left;
}

</style>

    @endsection