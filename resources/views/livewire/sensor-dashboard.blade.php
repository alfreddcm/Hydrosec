<div>
    <h2>Sensor Data for Tower ID: {{ $towerId }}</h2>

    @if ($sensorData)
        <ul>
            <li>pH Level: {{ $sensorData['ph'] }}</li>
            <li>Temperature: {{ $sensorData['temperature'] }} °C</li>
            <li>Nutrient Level: {{ $sensorData['nutrient_level'] }}</li>
            <li>Light Intensity: {{ $sensorData['light'] }}</li>
        </ul>
    @else
        <p>No sensor data available.</p>
    @endif

    <!-- Optionally add a refresh button -->
    <button wire:click="fetchSensorData">Refresh Data</button>
</div>
<script>
    document.addEventListener('livewire:load', function() {
    Livewire.on('sensorDataBeforeSave', (sensorData, towerId) => {
        if (towerId == towerId) {
            // Update the displayed values
            document.getElementById('ph-level').innerText = sensorData.ph;
            document.getElementById('temperature').innerText = sensorData.temperature + ' °C';
            document.getElementById('nutrient-level').innerText = sensorData.nutrient_level;
            document.getElementById('light').innerText = sensorData.light;
        }
    });
});

</script>