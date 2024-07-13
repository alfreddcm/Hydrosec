@extends('Owner/sidebar')
<link href="{{ asset('css/owner/workeraccount.css') }}" rel="stylesheet">
@section('title', 'Worker Account')
@section('content')
          <div class="container">
            <div class="row">
                <div class="col-sm-3">
                    <a href="#">
                     <div class="card">
                    <div class="card-body">
                      <h5 class="card-title">Name</h5>
                      <p class="card-text">User Name</p>
                    </div>
                    <div class="card-footer text-muted">
                        <a href="#" class="btn btn-primary">Update</a>
                        <a href="#" class="btn btn-Danger">Disable</a>
                      </div>
                  </div>
                    </a>

                </div>
                <div class="col-sm-3">
                    <a href="#">
                    <div class="card">
                        <div class="card-body">
                          <h5 class="card-title">Name</h5>
                          <p class="card-text">User Name</p>
                        </div>
                        <div class="card-footer text-muted">
                            <a href="#" class="btn btn-primary">Update</a>
                            <a href="#" class="btn btn-Danger">Disable</a>
                          </div>
                      </div>
                    </a>
                </div>
              </div>

        </div>


      </div>
  </div>


@endsection
