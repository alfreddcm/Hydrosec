<?php

namespace App\Console\Commands;

use App\Mail\Alert;
use App\Models\Owner;
use App\Models\Sensor;
use App\Models\Tower;
use App\Models\Towerlog; // Fixed the model name to match Laravel's naming conventions
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
// Ensure Log is imported

class IfOff extends Command
{
    protected $signature = 'app:ifoff';

    protected $description = 'Send email alert if tower did not receive sensor data';

    protected $emailCooldown = 1;

    public function handle()
    {
        $now = Carbon::now();

        $towers = Tower::all();

        foreach ($towers as $tower) {
            if (Crypt::decryptString($tower->status) == '1') {
                $latestSensor = Sensor::where('towerid', $tower->id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                $towerId = $tower->id;

                if (!$latestSensor || Carbon::parse($latestSensor->created_at)->diffInMinutes($now) > 1) {
                    // Check if cooldown period has elapsed
                    if ($tower->last_email_sensor_off) {
                        $lastEmailTime = Carbon::parse($tower->last_email_sensor_off);
                        if ($lastEmailTime->diffInMinutes($now) < $this->emailCooldown) {
                            $this->info('Skipping email for Tower ID ' . $tower->id . ', cooldown period not elapsed.');
                            continue;
                        }
                    }

                    $owner = Owner::find($tower->OwnerID);

                    if ($owner && $owner->email) {
                        $email = Crypt::decryptString($owner->email);
                        $decryptedTowerName = Crypt::decryptString($tower->name);

                        $body = "The Tower '" . $decryptedTowerName . "' did not receive sensor data at " . $now->toDateTimeString() . ". Please check the system.";

                        $details = [
                            'title' => 'Physical set up is down.',
                            'body' => $body,
                        ];

                        $mailStatus = 'Failed'; // Default to 'Failed'

                        try {
                            Mail::to($email)->send(new Alert($details));
                            $mailStatus = 'Sent';
                            $this->info('Alert email sent to ' . $email);

                            // Update last email sent time
                            $tower->last_email_sensor_off = $now;
                            $tower->save();
                        } catch (\Exception $e) {
                            $this->error('Failed to send alert email to ' . $email . ': ' . $e->getMessage());
                        } finally {
                            $activityLog = Crypt::encryptString("Alert: " . json_encode($details['body']) . " Mail Status: " . $mailStatus);

                            Towerlog::create([
                                'ID_tower' => $towerId,
                                'activity' => $activityLog,
                            ]);

                            Log::channel('custom')->info('Alert logged in tbl_towerlogs', ['tower_id' => $towerId, 'activity' => $body]);
                        }
                    } else {
                        $this->error("Owner not found or email not available for Tower ID {$tower->id}.");
                    }
                }
            }
        }
    }
}
