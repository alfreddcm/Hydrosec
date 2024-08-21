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
                                Nutrient Level: <span id="nutrient-level">Loading...</span>%
                            </div>
                            <div class="col">
                                pH: <span id="ph">Loading...</span>
                            </div>
                            <div class="col">
                                Temperature: <span id="temperature">Loading...</span>°C
                            </div>
                        </div>
                        </p>
                        <p class="card-text">
                            <a name="" id="" class="btn btn-primary" href="#" role="button">START
                                CYCLE </a>
                        </p>
                        <p class="card-text">Start Date: Sun, May 12, 2024</p>
                        <p class="card-text">Expected Harvest Date: June 26, 2024</p>

                    </div>
                </div>
            </div>
        </div>
    </div>
    </tbody>
    </table>
</div>

<script>
    function fetchLatestSensorData() {
        $.ajax({
            url: '/sensor/latest',
            method: 'GET',
            success: function (data) {
                // Decrypt data here or send the decrypted data from the server
                var method = "AES-128-CBC";
                var key = "aaaaaaaaaaaaaaaa";
                var iv = atob(data.iv);

                function decrypt_data(encrypted_data, key, iv) {
                    var decrypted = CryptoJS.AES.decrypt({
                        ciphertext: CryptoJS.enc.Base64.parse(encrypted_data)
                    }, CryptoJS.enc.Utf8.parse(key), {
                        iv: CryptoJS.enc.Base64.parse(iv),
                        mode: CryptoJS.mode.CBC,
                        padding: CryptoJS.pad.NoPadding
                    });
                    return decrypted.toString(CryptoJS.enc.Utf8).replace(/\0/g, '');
                }

                var ph = decrypt_data(data.pH, key, iv);
                var temp = decrypt_data(data.temperature, key, iv);
                var nutrientLevel = decrypt_data(data.nutrientlevel, key, iv);

                // Update the DOM with the new data
                $('#nutrient-level').text(nutrientLevel + '%');
                $('#ph').text(ph);
                $('#temperature').text(temp + '°C');
            },
            error: function (xhr, status, error) {
                console.error('Failed to fetch sensor data:', error);
            }
        });
    }

    // Fetch the latest sensor data every 10 seconds
    setInterval(fetchLatestSensorData, 10000);
</script>


@endsection