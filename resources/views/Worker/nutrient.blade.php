
@extends('Owner/header')
<link href="{{ asset('css/worker/nutrient.css') }}" rel="stylesheet">
@section('title', 'Hydrosec')
@section('Owner.content')


<div class="container" style="max-width:100%; padding:0%;">
    <div class="row">
      <!-- Sidebar -->
        <div class="col-md-3 ">      
            <div class="side p-3 text-white bg-dark " >
                <a href="#" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                  <img src="" alt="logo">
                  <span class="fs-4">
                    <div class="dropdown">
                      <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="https://github.com/mdo.png" alt="" width="32" height="32" class="rounded-circle me-2">
                        <strong>Andre</strong>
                      </a>
                      <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                        <li><a class="dropdown-item" href="#">Manage Account</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#">Sign out</a></li>
                      </ul>
                    </div>
                      </span>
                </a>
                <hr>
                <ul class="nav nav-pills flex-column mb-auto">
                  <li class="nav-item">
                    <a href="/Worker/dashboard" class="nav-link text-white" aria-current="page">
                      <svg class="bi me-2" width="16" height="16"><use xlink:href="#home"></use></svg>
                      Dashboard
                    </a>
                  </li>
                  <li>
                    <a href="/Worker/Nutrient" class="nav-link active">
                      <svg class="bi me-2" width="16" height="16"><use xlink:href="#speedometer2"></use></svg>
                      Nutrient 
                    </a>
                  </li>
                                   
                </ul>
              </div>
        </div>
        
        <div class="col-md-9">
            <div class="bg-info">
              <div class="dashboard-header mb-2">
                <div
                  class="row">
                  <div class="col">
                    <h1>Dashboard</h1>
                  </div>
                  <div class="col text-end">                
                    <p> Wed | May 16, 2024</p>
                  </div>
                </div>
              </div>
            </div>

           <div class="container">
            <div class="status-container my-4">
                <div class="text-center">
                    <div class="circle">
                        <div class="circle-text">
                            <p>89%</p>
                        </div>
                    </div>
                    <p>A</p>
                </div>
                <div class="text-center">
                    <div class="circle">
                        <div class="circle-text">
                            <p>76%</p>
                        </div>
                    </div>
                    <p>B</p>
                </div>
                <div class="text-center">
                  <div class="circle">
                      <div class="circle-text">
                          <p>76%</p>
                      </div>
                  </div>
                  <p>pH +</p>
              </div>
              <div class="text-center">
                <div class="circle">
                    <div class="circle-text">
                        <p>76%</p>
                    </div>
                </div>
                <p>pH -</p>
            </div>
                
            </div>

            <div class="concentration-container text-center">
                <h5>TOWER SOLUTION CONCENTRATION</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th scope="col">1L WATER</th>
                            <th scope="col">2.5ml A</th>
                            <th scope="col">2.5ml B</th>
                        </tr>
                    </thead>
                </table>
            </div>
            
            </div>
            

        </div>
    </div>
  </div>

  </div>


@endsection