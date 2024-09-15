@extends('Admin/sidebar')
@section('title', 'Edit Worker')
@section('content')

    <div class="container">
        <div id="wrapper">
            <nav>
                <div id="page-wrapper">
                    <div class="row">
                        <div class="col-lg-12">
                            <h4 class="page-header">Edit User</h4>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="panel panel-default">

                                <div class="panel-body">
                                     @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    <form action="{{ route('admin.update2', $worker->id) }}"
                                          method="POST">
                                        @csrf
                                        <div class="form-group">
                                            <label for="name">Name:</label>
                                            <input type="text"
                                                   class="form-control"
                                                   name="name"
                                                   value="{{ $worker->name }}"
                                                   required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="name">Username:</label>
                                            <input type="text"
                                                   class="form-control"
                                                   name="username"
                                                   value="{{ $worker->username }}"
                                                   required>
                                        </div>
                                        @php
                                            use App\Models\Tower;

                                            // Retrieve all towers
                                            $allTowers = Tower::all();

                                            // Filter towers based on the worker's owner_id
                                            $filteredTowers = $allTowers->filter(function ($tower) use ($worker) {
                                                return $tower->owner_id == $worker->owner_id;
                                            });
                                        @endphp

                                        <select id="tower"
                                                name="tower"
                                                class="form-select"
                                                required>
                                            <option value=""
                                                    disabled>Select a tower</option>
                                            @foreach ($filteredTowers as $tower)
                                                <option value="{{ $tower->id }}"
                                                        {{ $tower->id == $worker->towerid ? 'selected' : '' }}>
                                                    {{ Crypt::decryptString($tower->name) }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <a href="javascript:void(0);"
                                           onclick="openPasswordModal({{ $worker->id }})"
                                           class="btn btn-success">Update Password</a>
                                        <button type="submit"
                                                class="btn btn-primary sub">Update</button>
                                        <a href="{{ route('UserAccounts') }}"
                                           class="btn btn-secondary">Cancel</a>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
    </div>

    <div class="modal fade"
         id="passwordModal"
         tabindex="-1"
         role="dialog"
         aria-labelledby="passwordModalLabel"
         aria-hidden="true">
        <div class="modal-dialog"
             role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"
                        id="passwordModalLabel">Update Password</h5>
                    <button type="button"
                            class="close"
                            data-dismiss="modal"
                            aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="passwordForm"
                          action="{{ route('admin.updatePassword2') }}"
                          method="post">
                        @csrf
                        <div class="form-group">
                                                <span>Password must be have atleast one uppercase and lowercase letter, number and a special character. </span>

                            <label for="password">New Password</label>
                            <input type="password"
                                   class="form-control"
                                   id="password"
                                   name="password"
                                   required>
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation">Confirm Password</label>
                            <input type="password"
                                   class="form-control"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   required>
                        </div>
                        <input type="hidden"
                               name="id"
                               value="{{ $worker->id }}">
                        <button type="submit"
                                class="btn btn-primary sub">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    </div>
    <script>
        function openPasswordModal(userId) {
            $('#userId').val(userId);
            $('#passwordModal').modal('show');
        }

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
        const showLoading = function() {
            Swal.fire({
                title: '',
                html: '<b>Be patient.</b><br/>Checking Email.',
                allowEscapeKey: false,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        };
        // Attach event listener to the button
        document.getElementById('sub').addEventListener('click', showLoading);
    </script>

@endsection
