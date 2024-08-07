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
                                    <table class="table table-striped table-bordered table-hover"
                                        id="dataTables-example">
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
        <tr class="odd gradeX">
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
                        <button onclick="return confirm('Are you sure You want to delete this?')" type="submit" class="btn btn-danger ti-trash btn-rounded">
                            Delete
                        </button>
                    </form>
                   
                </div>
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

<!-- modal -->
<!-- Modal -->



@endsection