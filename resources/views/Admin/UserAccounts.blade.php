@extends('Admin/sidebar')
@section('title', 'Manage User Accounts')
@section('content')
    @php
        use Illuminate\Support\Facades\DB;
        use Illuminate\Support\Facades\Crypt;
        use App\Models\Owner;
        use App\Models\Worker;
        use App\Models\Tower;

        $activeOwners = Owner::all()->filter(function ($owner) {
            return Crypt::decryptString($owner->status) === '1';
        });

        $deactivatedOwners = Owner::all()->filter(function ($owner) {
            return Crypt::decryptString($owner->status) === '0';
        });

        $owners = Owner::all();
        $workers = Worker::all();
        $towers = Tower::all()->keyBy('id');

    @endphp
    <style>

    </style>
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
                                        @if ($owners->isNotEmpty())
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
                                                    @foreach ($owners as $owner)
                                                        @php
                                                            $status = Crypt::decryptString($owner->status);
                                                        @endphp
                                                        <tr class="odd gradeX owner-row">
                                                            <td>{{ $owner->id }}</td>
                                                            <td>{{ Crypt::decryptString($owner->name) }}</td>
                                                            <td>{{ Crypt::decryptString($owner->username) }}</td>
                                                            <td>{{ Crypt::decryptString($owner->email) }}</td>
                                                            <td>
                                                                @if ($status === '1')
                                                                    <!-- Owner is active -->
                                                                    <div class="btn-group">
                                                                        <a href="{{ route('admin.edit', $owner->id) }}"
                                                                            class="btn btn-success">Edit</a>
                                                                        <form action="{{ route('admin.dis', $owner->id) }}"
                                                                            method="post" style="display:inline;">
                                                                            @csrf
                                                                            <button
                                                                                onclick="return confirm('Are you sure you want to disable this?')"
                                                                                type="submit"
                                                                                class="btn btn-danger btn-rounded">Disable</button>
                                                                        </form>
                                                                    </div>
                                                                @else
                                                                    <!-- Owner is deactivated -->
                                                                    <form action="{{ route('admin.en', $owner->id) }}"
                                                                        method="post" style="display:inline;">
                                                                        @csrf
                                                                        <button
                                                                            onclick="return confirm('Are you sure you want to enable this?')"
                                                                            type="submit"
                                                                            class="btn btn-info btn-rounded">Enable</button>
                                                                    </form>
                                                                @endif
                                                            </td>
                                                        </tr>

                                                        @if ($status === '1')
                                                            <tr class="workerlist">
                                                                <td colspan="5">
                                                                    @if ($workers && $workers->isNotEmpty())
                                                                        <table class="table table-bordered worker-table">

                                                                            @php $counter = 1; @endphp
                                                                            @foreach ($workers as $worker)
                                                                                @if ($worker->OwnerID == $owner->id)
                                                                                    <thead>
                                                                                        <tr>
                                                                                            <th>No</th>
                                                                                            <th>Worker Name</th>
                                                                                            <th>Worker Username</th>
                                                                                            <th>Tower Assigned</th>
                                                                                            <th>Action</th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                        @php
                                                                                            $workerStatus = Crypt::decryptString(
                                                                                                $worker->status,
                                                                                            );
                                                                                            $tower = $towers->get(
                                                                                                $worker->towerid,
                                                                                            );
                                                                                        @endphp
                                                                                        <tr>
                                                                                            <td>{{ $counter++ }}</td>
                                                                                            <td>{{ Crypt::decryptString($worker->name) }}
                                                                                            </td>
                                                                                            <td>{{ Crypt::decryptString($worker->username) }}
                                                                                            </td>
                                                                                            <td>{{ $tower ? Crypt::decryptString($tower->name) : 'N/A' }}
                                                                                            </td>
                                                                                            <td>
                                                                                                @if ($workerStatus == '1')
                                                                                                    <!-- Active Worker -->
                                                                                                    <div class="btn-group">
                                                                                                        <a href="{{ route('admin.edit2', $worker->id) }}"
                                                                                                            class="btn btn-success">Edit</a>
                                                                                                        <form
                                                                                                            action="{{ route('admin.dis2', $worker->id) }}"
                                                                                                            method="POST"
                                                                                                            style="display:inline;">
                                                                                                            @csrf
                                                                                                            <button
                                                                                                                onclick="return confirm('Are you sure you want to disable this?')"
                                                                                                                type="submit"
                                                                                                                class="btn btn-danger btn-rounded">Disable</button>
                                                                                                        </form>
                                                                                                    </div>
                                                                                                @else
                                                                                                    <!-- Deactivated Worker -->
                                                                                                    <form
                                                                                                        action="{{ route('admin.en2', $worker->id) }}"
                                                                                                        method="POST"
                                                                                                        style="display:inline;">
                                                                                                        @csrf
                                                                                                        <button
                                                                                                            onclick="return confirm('Are you sure you want to enable this?')"
                                                                                                            type="submit"
                                                                                                            class="btn btn-secondary btn-rounded">Enable</button>
                                                                                                    </form>
                                                                                                @endif
                                                                                            </td>
                                                                                        </tr>
                                                                                    @else
                                                                                        <tr>
                                                                                            <td colspan="5"
                                                                                                class="text-center">No
                                                                                                worker accounts
                                                                                            </td>
                                                                                        </tr>
                                                                                @endif
                                                                            @endforeach
                                                </tbody>
                                            </table>
                                        @endif
                                        </td>
                                        </tr>
                                        @endif
                                        @endforeach
                                        </tbody>
                                        </table>
                                    @else
                                        <tr>
                                            <td colspan="5" class="text-center">No active accounts available</td>
                                        </tr>
                                        @endif
                                    </div>
                                </div>

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
    <div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountModalLabel" aria-hidden="true">
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
        #toggleButton {
            border: none;
            background: none;
        }

        #toggleButton:hover {
            text-decoration: underline;
            color: rgb(0, 0, 0);
        }

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
            // Display success message if present
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: '{{ session('success') }}',
                    timer: 3000,
                    showConfirmButton: false
                });
            @endif

            // Display error messages if present
            @if ($errors->any())
                var errors = @json($errors->all());
                var errorText = errors.join('\n');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorText,
                    timer: 5000,
                    showConfirmButton: true
                });
            @endif
        });
        document.getElementById('toggleButton').addEventListener('click', function() {
            var div = document.getElementById('dis');

            if (div.style.display === 'none' || div.style.display === '') {
                div.style.display = 'block';
            } else {
                div.style.display = 'none';
            }
        });
    </script>

@endsection
