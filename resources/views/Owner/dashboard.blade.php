
@extends('Owner/header')
<link href="{{ asset('css/owner/dashboard.css') }}" rel="stylesheet">
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
                    <a href="/Owner/dashboard" class="nav-link active" aria-current="page">
                      <svg class="bi me-2" width="16" height="16"><use xlink:href="#home"></use></svg>
                      Dashboard
                    </a>
                  </li>
                  <li>
                    <a href="/Owner/ManageTower" class="nav-link text-white">
                      <svg class="bi me-2" width="16" height="16"><use xlink:href="#speedometer2"></use></svg>
                      Towers
                    </a>
                  </li>
                  <li>
                    <a href="/Owner/WorkerAccounts" class="nav-link text-white">
                      <svg class="bi me-2" width="16" height="16"><use xlink:href="#table"></use></svg>
                      Worker Accounts
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

            <div class="card">
              <div class="row solution-status">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <p class="card-title">A</p>
                            <p class="card-text">76%</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <p class="card-title">B</p>
                            <p class="card-text">89%</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <p class="card-title">pH +</p>
                            <p class="card-text">80%</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <p class="card-title">pH -</p>
                            <p class="card-text">76%</p>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            

            <div class="card mb-3" >
                
  
            <div class="row tower">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="card-title">TOWER 1</h2>
                            <p class="card-text">Juan Mentiz</p>
                            <p class="card-text">
                              <div
                                class="row justify-content-center align-items-center g-2">
                                <div class="col">
                                  Nutrient Level: 76%
                                </div>
                                <div class="col">
                                  pH: 6.1
                                </div>
                                <div class="col">
                                  Temperature: 27°C
                                </div>
                              </div>
                            </p>
                            <p class="card-text">
                              <a name="" id="" class="btn btn-primary" href="#" role="button">START CYCLE </a>
                             </p>
                            <p class="card-text">Start Date: Sun, May 12, 2024</p>
                            <p class="card-text">Expected Harvest Date: June 26, 2024</p>
                        </div>
                    </div>
                </div>
            </div>
          </div>
        </div>
    </div>
  </div>

  </div>


@endsection