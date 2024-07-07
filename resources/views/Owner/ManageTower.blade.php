
@extends('Owner/header')
<link href="{{ asset('css/owner/managetower.css') }}" rel="stylesheet">
@section('title', 'Hydrosec')
@section('Owner.content')


<div class="container" style="max-width:100%; padding:0%;">
    <div class="row">
      <!-- Sidebar -->
        <div class="col-md-3 side">      
            <div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark">
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
                    <a href="/Owner/dashboard" class="nav-link text-white" aria-current="page">
                      <svg class="bi me-2" width="16" height="16"><use xlink:href="#home"></use></svg>
                      Dashboard
                    </a>
                  </li>
                  <li>
                    <a href="#" class="nav-link active ">
                      <svg class="bi me-2" width="16" height="16"><use xlink:href="#speedometer2"></use></svg>
                      Manage Tower
                    </a>
                  </li>
                  <li>
                    <a href="#" class="nav-link text-white">
                      <svg class="bi me-2" width="16" height="16"><use xlink:href="#table"></use></svg>
                      Worker Accounts
                    </a>
                  </li>
                 
                </ul>
              </div>
        </div>
        
        <div class="col-md-9">
            <div class="bg-info">
              <div class="dashboard-header">

                <div
                  class="row align-items-center">
                  <div class="col">
                    <h1>Tower Info</h1>
                  </div>
                  <div class="col">                
                    <p>Wed | May 16, 2024</p>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="container dashboard-container">
              <div class="row">
                  <div class="col-12">
                      <h2>TOWER 1</h2>
                      <p>Juan Mentiz</p>
                      <button class="btn btn-primary">CYCLE STARTED</button>
                  </div>
              </div>
              <div class="row status">
                  <div class="col">
                      <p>76%</p>
                      <p>Nutrient</p>
                  </div>
                  <div class="col">
                      <p>6.1</p>
                      <p>pH</p>
                  </div>
                  <div class="col">
                      <p>27°C</p>
                      <p>Humidity</p>
                  </div>
                  <div class="col">
                      <p>27°C</p>
                      <p>Temperature</p>
                  </div>
              </div>
              <div class="row cycle-info">
                  <div class="col-12">
                      <p>Start Date: Sun, May 16, 2024</p>
                      <p>Expected Harvest Date: June 26, 2024</p>
                  </div>
              </div>
              <div class="row">
                  <div class="col-12 chart">
                      <!-- Chart Placeholder -->
                      <p>Chart goes here</p>
                  </div>
              </div>
              <div class="row footer-buttons">
                  <div class="col">
                      <button class="btn btn-primary">TOWER CYCLE</button>
                  </div>
                  <div class="col">
                      <button class="btn btn-primary">MANAGE NUTRIENT</button>
                  </div>
              </div>
          </div>
          </div>

              <div class="row add-tower-btn">
                <div class="col-md-12">
                    <button class="btn btn-primary">ADD TOWER</button>
                </div>
              </div>
        </div>
    </div>
  </div>


@endsection