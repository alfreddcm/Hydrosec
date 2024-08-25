@extends('Owner/sidebar')
@section('title', 'Dashboard')
@section('content')


<div class="container">


    <div class="card mb-3">
        <div class="row tower">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">

                        <h2 class="card-title">TOWER 1</h2>
                        <p class="card-text">Juan Mentiz</p>
                        <p class="card-text">
                        <div class="row justify-content-center align-items-center g-2">

                            <div class="col">
                                <h2>Nutrient Level</h2>
                                <canvas id="nutrientChart" width="40" height="20"></canvas>
                            </div>
                            <div class="col">
                                <h2>pH</h2>
                                <canvas id="phChart" width="40" height="20"></canvas>
                            </div>
                            <div class="col">
                                <h2>Temperature</h2>
                                <canvas id="temperatureChart" width="40" height="20"></canvas>
                            </div>
                        </div>
                    </div>
                    </p>
                    <center>
                    <p class="card-text">
                        <a name="" id="" class="btn btn-primary" href="#" role="button">START
                            CYCLE </a>
                    </p>
                    
                         <p class="card-text">Start Date: <spam>Loading..</spam></p>
                    <p class="card-text">Expected Harvest Date: <span>Loading..</span></p>

                    </center>
                   
                </div>
            </div>
        </div>
    </div>
</div>
</tbody>
</table>
</div>


<script>
$(document).ready(function() {
    let tempChart, nutrientChart, phChart;

    function fetchLatestSensorData() {
        $.ajax({
            url: '{{route('getsensor')}}',
            method: 'GET',
            success: function(response) {
                if (response.sensorData) {
                    const timestamps = response.sensorData.temperature.map(item => new Date(item.timestamp));
                    const tempData = response.sensorData.temperature.map(item => parseFloat(item.value));
                    const nutrientData = response.sensorData.nutrient_level.map(item => parseFloat(item.value));
                    const phData = response.sensorData.pH.map(item => parseFloat(item.value));

                    const tempCtx = document.getElementById('temperatureChart').getContext('2d');
                    if (tempChart) {
                        tempChart.data.labels = timestamps;
                        tempChart.data.datasets[0].data = tempData;
                        tempChart.update();
                    } else {
                        tempChart = new Chart(tempCtx, {
                            type: 'line',
                            data: {
                                labels: timestamps,
                                datasets: [{
                                    label: ' ',
                                    data: tempData,
                                    borderColor: 'rgba(75, 192, 192, 1)',
                                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                    fill: true,
                                    tension: 0.4
                                }]
                            },
                            options: {
                                responsive: true,
                                scales: {
                                    x: {
                                        type: 'time',
                                        time: {
                                            unit: false, 
                                            tooltipFormat: 'MMM D, YYYY h:mm a', 
                                            displayFormats: {
                                                second: 'h:mm a',
                                                minute: 'h:mm a',
                                                hour: 'MMM D, YYYY hA',
                                                day: 'MMM D',
                                                week: 'MMM D',
                                                month: 'MMM YYYY',
                                                quarter: 'MMM YYYY',
                                                year: 'YYYY'
                                            }
                                        },
                                        title: {
                                            display: true,
                                            text: 'Time'
                                        }
                                    },
                                    y: {
                                        title: {
                                            display: true,
                                            text: 'Temperature (°C)'
                                        }
                                    }
                                }
                            }
                        });
                    }

                    const nutrientCtx = document.getElementById('nutrientChart').getContext('2d');
                    if (nutrientChart) {
                        nutrientChart.data.labels = timestamps;
                        nutrientChart.data.datasets[0].data = nutrientData;
                        nutrientChart.update();
                    } else {
                        nutrientChart = new Chart(nutrientCtx, {
                            type: 'line',
                            data: {
                                labels: timestamps,
                                datasets: [{
                                    label: ' ',
                                    data: nutrientData,
                                    borderColor: 'rgba(153, 102, 255, 1)',
                                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                                    fill: true,
                                    tension: 0.4
                                }]
                            },
                            options: {
                                responsive: true,
                                scales: {
                                    x: {
                                        type: 'time',
                                        time: {
                                            unit: false, 
                                            tooltipFormat: 'MMM D, YYYY h:mm a',
                                            displayFormats: {
                                                second: 'h:mm a',
                                                minute: 'h:mm a',
                                                hour: 'MMM D, YYYY hA',
                                                day: 'MMM D',
                                                week: 'MMM D',
                                                month: 'MMM YYYY',
                                                quarter: 'MMM YYYY',
                                                year: 'YYYY'
                                            }
                                        },
                                        title: {
                                            display: true,
                                            text: 'Time'
                                        }
                                    },
                                    y: {
                                        title: {
                                            display: true,
                                            text: 'Nutrient Level'
                                        }
                                    }
                                }
                            }
                        });
                    }

                    const phCtx = document.getElementById('phChart').getContext('2d');
                    if (phChart) {
                        phChart.data.labels = timestamps;
                        phChart.data.datasets[0].data = phData;
                        phChart.update();
                    } else {
                        phChart = new Chart(phCtx, {
                            type: 'line',
                            data: {
                                labels: timestamps,
                                datasets: [{
                                    label: ' ',
                                    data: phData,
                                    borderColor: 'rgba(255, 99, 132, 1)',
                                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                    fill: true,
                                    tension: 0.4
                                }]
                            },
                            options: {
                                responsive: true,
                                scales: {
                                    x: {
                                        type: 'time',
                                        time: {
                                            unit: false, 
                                            tooltipFormat: 'MMM D, YYYY h:mm a',
                                            displayFormats: {
                                                second: 'h:mm a',
                                                minute: 'h:mm a',
                                                hour: 'MMM D, YYYY hA',
                                                day: 'MMM D',
                                                week: 'MMM D',
                                                month: 'MMM YYYY',
                                                quarter: 'MMM YYYY',
                                                year: 'YYYY'
                                            }
                                        },
                                        title: {
                                            display: true,
                                            text: 'Time'
                                        }
                                    },
                                    y: {
                                        title: {
                                            display: true,
                                            text: 'pH'
                                        }
                                    }
                                }
                            }
                        });
                    }
                } else {
                    console.error('No data available');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching sensor data:', error);
            }
        });
    }

    fetchLatestSensorData();
    setInterval(fetchLatestSensorData, 1000);
});

</script>



@endsection