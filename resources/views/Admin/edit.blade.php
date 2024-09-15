@extends('Admin/sidebar')
@section('title', 'Edit User')
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
                            <div class="panel-heading">
                                Edit User
                            </div>
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
                                <form action="{{ route('admin.update', $user->id) }}" method="POST">
                                    @csrf
                                    <div class="form-group">
                                        <label for="name">Name:</label>
                                        <input type="text" class="form-control" name="name" value="{{ $user->name }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="name">Username:</label>
                                        <input type="text" class="form-control" name="username" value="{{ $user->username }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="email">Email:</label>
                                        <input type="email" class="form-control" name="email" value="{{ $user->email }}" required>
                                    </div>
                                    <a href="javascript:void(0);" onclick="openPasswordModal({{ $user->id }})" class="btn btn-success">Update Password</a>
                                    <button type="submit" class="btn btn-primary sub">Update</button>
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

<div class="modal fade" id="passwordModal" tabindex="-1" role="dialog" aria-labelledby="passwordModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="passwordModalLabel">Update Password</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      <form id="passwordForm" action="{{ route('admin.updatePassword')}}" method="post">
      @csrf
                          <span>Password must be have atleast one uppercase and lowercase letter, number and a special character. </span>

          <div class="form-group">
            <label for="password">New Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
          </div>
          <div class="form-group">
            <label for="password_confirmation">Confirm Password</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
          </div>
          <input type="hidden" id="" name="idd" value=" {{$user->id}}">
          <button type="submit" class="btn btn-primary sub">Update</button>
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
