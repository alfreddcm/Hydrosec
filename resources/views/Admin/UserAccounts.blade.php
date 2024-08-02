@extends('Admin/sidebar')
@section('title', 'Manage User Accounts')
@section('content')
@php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;


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
                                            <tr class="odd gradeX ">
                                            @foreach ($accs as $user)
                                            <td>{{ $user->id }}</td>
                                            <td>{{ Crypt::decryptString($user->name) }}</td>
                                            <td>{{ Crypt::decryptString($user->username) }}</td>
                                            <td>{{ Crypt::decryptString($user->email) }}</td>
                                            <td>
                                                <div class="btn-group">
                                                <a href="{{ route('admin.edit', $user->id) }}" class="btn btn-success">Edit</a>

                                                <a href="javascript:void(0);" onclick="openPasswordModal({{ $user->id }})" class="btn btn-success">Update Password</a>


                                                    
                                                    <form action="/student/delete/{{ $user->id }}" method="post">
                                                        @method('DELETE')
                                                        @csrf
                                                        <button onclick="return confirm('Are you sure You want to delete this?')" type="submit" class="btn btn-danger ti-trash btn-rounded">
                                                            Delete</button>
                                                    </form>
                                                    
                                                </div>

                                            </td>
                                            @endforeach
                                                

                                            </tr>

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
        <form id="passwordForm">
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

    $(document).ready(function(){
        $('#passwordForm').on('submit', function(e){
            e.preventDefault();
            
            var userId = $('#userId').val();
            var password = $('#password').val();
            var password_confirmation = $('#password_confirmation').val();

            $.ajax({
                url: '/user/update-password/' + userId,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    password: password,
                    password_confirmation: password_confirmation
                },
                success: function(response) {
                    $('#passwordModal').modal('hide');
                    alert('Password updated successfully.');
                },
                error: function(response) {
                    alert('Error updating password.');
                }
            });
        });
    });
</script>





@endsection