@extends('Admin/sidebar')
@section('title', 'Manage User Accounts')
@section('content')
    @php
        use Illuminate\Support\Facades\DB;
        use Illuminate\Support\Facades\Crypt;
        use App\Models\Owner;
        use App\Models\Worker;

        $accs = Owner::where('status', '1')->get();
        $accsdeac = Owner::where('status', '0')->get();

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
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                @if (session('success'))
                                    <div class="alert alert-success">
                                        {{ session('success') }}
                                    </div>
                                @endif
                                <div class="panel-body">
                                    @if ($accs->isNotEmpty())
                                        <div class="dataTable_wrapper">
                                            <table class="table table-striped table-bordered table-hover"
                                                id="dataTables-example">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
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
                                                                    <a href="{{ route('admin.edit', $user->id) }}"
                                                                        class="btn btn-success">Edit</a>
                                                                    <form action="{{ route('admin.dis', $user->id) }}"
                                                                        method="post">
                                                                        @method('DELETE')
                                                                        @csrf
                                                                        <button
                                                                            onclick="return confirm('Are you sure You want to delete this?')"
                                                                            type="submit"
                                                                            class="btn btn-danger ti-trash btn-rounded">Delete</button>
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
                                                                            $worker = Worker::get();
                                                                            $counter2 = 1;
                                                                        @endphp
                                                                        @foreach ($worker as $workerUser)
                                                                            @if (Crypt::decryptString($workerUser->OwnerID) == $user->id)
                                                                                <tr>
                                                                                    <td>{{ $counter2++ }}</td>
                                                                                    <td>{{ Crypt::decryptString($workerUser->name) }}
                                                                                    </td>
                                                                                    <td>{{ Crypt::decryptString($workerUser->username) }}
                                                                                    </td>
                                                                                    <td>
                                                                                        <div class="btn-group">
                                                                                            <a href="{{ route('admin.edit2', $workerUser->id) }}"
                                                                                                class="btn btn-success">Edit</a>
                                                                                           
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
                                    @else
                                        <p>No accounts available</p>
                                    @endif
                                </div>

                                 <div class="panel-heading">
                                   Disabled Owner Accounts List
                                </div>
                                <div class="panel-body">
                                    @if ($accsdeac->isNotEmpty())
                                        <div class="dataTable_wrapper">
                                            <table class="table table-striped table-bordered table-hover"
                                                id="dataTables-example">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Name</th>
                                                        <th>Username</th>
                                                        <th>Email</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($accsdeac as $user)
                                                        <tr class="odd gradeX owner-row">
                                                            <td>{{ $user->id }}</td>
                                                            <td>{{ Crypt::decryptString($user->name) }}</td>
                                                            <td>{{ Crypt::decryptString($user->username) }}</td>
                                                            <td>{{ Crypt::decryptString($user->email) }}</td>
                                                            <td>
                                                              <form action="{{ route('admin.en', $user->id) }}"
                                                                        method="post">
                                                                        @csrf
                                                                        <button
                                                                            onclick="return confirm('Are you sure You want to enable this?')"
                                                                            type="submit"
                                                                            class="btn btn-info ti-trash btn-rounded">Enable</button>
                                                                    </form>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                       
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p>No deativated accounts </p>
                                    @endif
                                </div>

                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#addAccountModal">
                                    Add Account
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
        </div>

        <!-- Add Account Modal -->
        <div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addAccountModalLabel">Add Account</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('PUserAccounts') }}" id="addAccountForm" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" form="addAccountForm">Add Account</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Styles for Worker List Display -->
        <style>
            .workerlist {
                display: none;
            }

            .owner-row:hover+.workerlist,
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

            .worker-table th,
            .worker-table td {
                padding: 5px;
                text-align: left;
            }
        </style>
    </div>

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
            @elseif ($errors->any())
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
