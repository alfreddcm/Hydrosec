@extends('Admin/sidebar')
@section('title', 'Edit User')
@section('content')

    <div class="container">
        <div id="wrapper">
            <nav>
                <div id="page-wrapper">
                    <div class="row">
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    @if (session('success'))
                                        <div class="alert alert-success">
                                            {{ session('success') }}
                                        </div>
                                    @endif
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                </div>
                                <div class="panel-body mt-2">
                                    <form action="{{ route('ownerworker.update', $user->id) }}" method="POST">
                                        @csrf
                                        <div class="form-group">
                                            <label>Name:</label>
                                            <input type="text" class="form-control" name="name"
                                                value="{{ $user->name }}" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Username:</label>
                                            <input type="text" class="form-control" name="username"
                                                value="{{ $user->username }}" required>
                                        </div>

                                        <div class="mb-2">
                                            <label for="tower" class="form-label">Towers</label>
                                            @php
                                                use App\Models\Tower;
                                                use Illuminate\Support\Facades\Auth;

                                                $allTowers = Tower::where('OwnerID', Auth::id())->get();
                                            @endphp

                                            <select id="tower" name="tower" class="form-select" required>
                                                <option value="" disabled>Select a tower</option>
                                                @foreach ($allTowers as $tower)
                                                    <option value="{{ $tower->id }}"
                                                        {{ isset($selectedTowerId) && $tower->id == $selectedTowerId ? 'selected' : '' }}>
                                                        {{ Crypt::decryptString($tower->name) }}
                                                    </option>
                                                @endforeach
                                            </select>

                                        </div>

                                        <a href="javascript:void(0);" onclick="openPasswordModal({{ $user->id }})"
                                            class="btn btn-success">Update Password</a>
                                        <button type="submit" class="btn btn-primary">Update</button>
                                        <a href="{{ route('UserAccounts') }}" class="btn btn-secondary">Cancel</a>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
    </div>

    <div class="modal fade" id="passwordModal" tabindex="-1" role="dialog" aria-labelledby="passwordModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="passwordModalLabel">Update Password</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <span>Password must be have atleast one uppercase and lowercase letter, number and a special character.
                    </span>
                    <form id="passwordForm" action="{{ route('owner.workerupdatePassword', ['id' => $user->id]) }}"
                        method="post">
                        @csrf
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation">Confirm Password</label>
                            <input type="password" class="form-control" id="password_confirmation"
                                name="password_confirmation" required>
                        </div>

                        <input type="hidden" id="userId" name='id' value="{{ $user->id }}">
                        <button type="submit" class="btn btn-primary"> Update </button>
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
    </script>

@endsection
