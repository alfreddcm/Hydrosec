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
                            <div class="panel-heading">
                                Edit User
                            </div>
                            <div class="panel-body">
                                <form action="{{ route('admin.update2', $worker->id) }}" method="POST">
                                    @csrf
                                    <div class="form-group">
                                        <label for="name">Name:</label>
                                        <input type="text" class="form-control" name="name" value="{{ $worker->name }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="name">Username:</label>
                                        <input type="text" class="form-control" name="username" value="{{ $worker->username }}" required>
                                    </div>
                                    <a href="javascript:void(0);" onclick="openPasswordModal({{ $worker->id }})" class="btn btn-success">Update Password</a>
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
      <form id="passwordForm" action="{{ route('admin.updatePassword2', ['id' => $worker->id]) }}" method="post">
      @csrf
          <div class="form-group">
            <label for="password">New Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
          </div>
          <div class="form-group">
            <label for="password_confirmation">Confirm Password</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
          </div>
          <input type="hidden" id="userId">
          <button type="submit" class="btn btn-primary">Update</button>
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
