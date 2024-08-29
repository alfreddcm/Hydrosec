@extends('Owner/sidebar')
@section('title', 'Tower ')
@section('content')
@php
use App\Models\Tower;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

    //$towerinfo=Tower::where('id',$id)->get();
@endphp
<style>
    canvas {
        height: auto !important;
    }

    .card2 {
        height: max-content;
        width: auto;
    }

    .chart-container {
        text-align: center;/
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container">


    <div class="card mb-3">
       

        <div class="row tower">
            <div class="col-md-12">
            
                <div class="card">
                <a name="" id="" class=" " href="{{route('ownermanagetower')}}" role="button">back</a>
                    <div class="card-body">

                        <h2 class="card-title">
                    </h2>
                        <p class="card-text">Juan Mentiz</p>
                        <div class="card card2 text-center">
                            <div class="row justify-content-center align-items-center g-1">

                                <div class="col">
                                    <div class="chart-container">
                                        <canvas id="nutrientChart"></canvas>
                                        <div id="nutrientValue">N/A</div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="chart-container">
                                        <canvas id="tempChart"></canvas>
                                        <div id="tempValue">N/A</div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="chart-container">
                                        <canvas id="phChart"></canvas>
                                        <div id="phValue"> N/A</div>
                                    </div>
                                </div>

                            </div>
                        </div>


                        <p class="card-text">
                            <a name="" id="" class="btn btn-primary" href="#" role="button">START CYCLE</a>
                        </p>
                        <p class="card-text">Start Date: Sun, May 12, 2024</p>
                        <p class="card-text">Expected Harvest Date: June 26, 2024</p>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let nutrientChart, tempChart, phChart;
        const towerId = 1; // Replace with your actual tower ID

        // Function to fetch sensor data and update charts
        function fetchSensorData(towerId) {
            $.ajax({
                url: '/sensor-data/' + towerId,
                method: 'GET',
                success: function (response) {
                    if (response.sensorData) {
                        // Extract data from the response
                        const nutrientLevel = response.sensorData.nutrient_level[0];
                        const temperature = response.sensorData.temperature[0];
                        const ph = response.sensorData.pH[0];

                        // Update charts with the retrieved data
                        createNutrientChart(nutrientLevel);
                        createTemperatureGauge(temperature);
                        createPhGauge(ph);

                        // Update sensor values displayed below the charts
                        document.getElementById('nutrientValue').textContent = `${nutrientLevel}`;
                        document.getElementById('tempValue').textContent = `${temperature} °C`;
                        document.getElementById('phValue').textContent = `${ph}`;
                    } else {
                        console.error('No sensor data available');
                    }
                },
                error: function (xhr) {
                    console.error('AJAX Error:', xhr.responseJSON.error);
                }
            });
        }

        function createNutrientChart(nutrientLevel) {
            if (nutrientChart) {
                nutrientChart.destroy();
            }

            const remainder = 100 - nutrientLevel;

            const nutrientData = {
                labels: ['Nutrient Level', 'Remaining'],
                datasets: [{
                    label: 'Nutrient Levels',
                    data: [nutrientLevel, remainder],
                    backgroundColor: [
                        'rgb(54, 162, 235)',
                        'rgb(211, 211, 211)'
                    ],
                    hoverOffset: 4
                }]
            };

            const nutrientConfig = {
                type: 'doughnut',
                data: nutrientData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Nutrient Levels'
                        }
                    }
                }
            };

            nutrientChart = new Chart(document.getElementById('nutrientChart'), nutrientConfig);
        }

        function createTemperatureGauge(temperature) {
            if (tempChart) {
                tempChart.destroy();
            }

            const tempData = {
                labels: ['Temperature'],
                datasets: [{
                    label: 'Current Temperature',
                    data: [temperature],
                    backgroundColor: 'rgb(255, 159, 64)'
                }]
            };

            const tempConfig = {
                type: 'bar',
                data: tempData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Current Temperature'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Temperature (°C)'
                            }
                        }
                    }
                }
            };

            tempChart = new Chart(document.getElementById('tempChart'), tempConfig);
        }

        function createPhGauge(ph) {
            if (phChart) {
                phChart.destroy();
            }

            const acidic = Math.max(0, Math.min(ph, 7)); // pH below 7 is acidic
            const neutral = Math.max(0, Math.min(7, ph)); // pH of exactly 7 is neutral
            const basic = Math.max(0, Math.min(14, ph - 7)); // pH above 7 is basic

            const phData = {
                labels: ['Acidic', 'Neutral', 'Basic'],
                datasets: [{
                    label: 'pH Level',
                    data: [acidic, neutral, basic],
                    backgroundColor: [
                        'rgb(255, 99, 132)', // Acidic
                        'rgb(75, 192, 192)', // Neutral
                        'rgb(54, 162, 235)'  // Basic
                    ],
                    borderWidth: 0
                }]
            };

            const phConfig = {
                type: 'doughnut',
                data: phData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'pH'
                        },
                        datalabels: {
                            display: true,
                            align: 'center',
                            anchor: 'center',
                            color: '#000',
                            font: {
                                size: 24,
                                weight: 'bold'
                            },
                            formatter: () => `${ph}`, // Display pH value in center
                            backgroundColor: 'rgba(255, 255, 255, 0.8)', // Optional: Background color for better visibility
                            borderRadius: 3, // Optional: Rounded corners for background
                            padding: 4 // Optional: Padding around the text
                        }
                    },
                    cutout: '75%', // Makes it look like a gauge
                    rotation: -90, // Rotate to start from the top
                    circumference: 180 // Show half circle
                }
            };

            phChart = new Chart(document.getElementById('phChart'), phConfig);
        }

        // Initial data load and setup
        fetchSensorData(towerId);

        // Refresh charts every 5 seconds
        setInterval(() => fetchSensorData(towerId), 5000);

    </script>
    @endsection