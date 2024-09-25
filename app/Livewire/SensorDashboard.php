namespace App\Http\Livewire;

use App\Models\SensorData;
use Livewire\Component; // Ensure you have this model created
use Log;

class SensorDashboard extends Component
{
    public $sensorData = [];
    public $towerId;

    protected $listeners = ['sensorDataBeforeSave'];

    public function mount($towerId)
    {
        $this->towerId = $towerId;
        $this->fetchSensorData();
    }

    public function render()
    {
        return view('livewire.sensor-dashboard');
    }

    public function fetchSensorData()
    {
        // Fetch the latest sensor data for the specified tower
        $this->sensorData = SensorData::where('tower_id', $this->towerId)->latest()->first();
    }

    public function sensorDataBeforeSave($data, $towerId)
    {
        if ($towerId == $this->towerId) {
            $this->sensorData = $data;
            Log::info('Sensor data updated', ['data' => $data, 'towerId' => $towerId]);
        }
    }
}
