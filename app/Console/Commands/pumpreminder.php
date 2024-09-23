<?php

namespace App\Console\Commands;

use App\Mail\Alert;
use App\Models\Owner;
use App\Models\Pump;
use App\Models\Tower;
use App\Models\Towerlogs;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class pumpreminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'app:pumpreminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pumping Remainder';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // $nextHour = $now->copy()->addHour();
        // $hoursBefore = 1;

        // $allTowers = Tower::pluck('id');

        // $towersWithData = Tower::whereNotNull('enddate')
        //     ->where(function ($query) use ($now, $nextHour, $hoursBefore) {
        //         $query->whereBetween('enddate', [$now->copy()->subHours($hoursBefore), $nextHour])
        //             ->orWhereBetween('enddate', [$nextHour, $nextHour]);
        //     })
        //     ->pluck('id');

        // $towersWithoutData = $allTowers->diff($towersWithData);

        // foreach ($towersWithoutData as $towerId) {
        //     $tower = Tower::find($towerId);
        //     if (!$tower) {
        //         Log::error("Tower not found with ID $towerId.");
        //         continue;
        //     }

        $key_str = "ISUHydroSec2024!";
        $iv_str = "HydroVertical143";
        $method = "AES-128-CBC";

        $now = Carbon::now();
        $emailCooldown = 5;
        $towers = Tower::select('id', 'status', 'name', 'OwnerID', 'last_pumping_email_sent_at')->get();
        foreach ($towers as $data) {
            if (Crypt::decryptString($data->status) == '1') {
                $towerId = $data->id;
                Log::info('Processing tower', ['tower_id' => $towerId]);

                $now = Carbon::now();
                $emailCooldown = 5;
                $timeLimit = Carbon::now()->subMinutes(5);

                $recentStatuses = Pump::where('towerid', $towerId)
                    ->orderBy('created_at', 'desc')
                    ->limit(2)
                    ->get();

                if (count($recentStatuses) === 2 &&
                    $this->decrypt_data($recentStatuses[0]->status, $method, $key_str, $iv_str) == '0' &&
                    $this->decrypt_data($recentStatuses[1]->status, $method, $key_str, $iv_str) == '0') {

                    Log::info('Consecutive pump status 0 detected', ['tower_id' => $towerId]);

                    // Ensure last_pumping_email_sent_at is a Carbon instance
                    if ($data->last_pumping_email_sent_at) {
                        $lastEmailSentAt = Carbon::parse($data->last_pumping_email_sent_at);
                        if ($lastEmailSentAt->diffInMinutes($now) < $emailCooldown) {
                            $remainingTime = $emailCooldown - $lastEmailSentAt->diffInMinutes($now);
                            Log::info('Skipping email, recently sent', ['tower_id' => $towerId, 'remaining_time' => $remainingTime]);
                            continue;
                        }
                    }

                    $owner = Owner::select('email')->where('id', $data->OwnerID)->first();
                    if ($owner) {
                        $body = "The Tower '" . Crypt::decryptString($data->name) . "' did not pump at " . $now . "  Please check the plug.";

                        $details = [
                            'title' => 'Pumping stopped.',
                            'body' => $body,
                        ];

                        $ownerEmail = Crypt::decryptString($owner->email);
                        $mailStatus = 'Failed';

                        try {
                            Mail::to($ownerEmail)->send(new Alert($details));
                            $mailStatus = 'Sent';
                            Log::info('Alert email sent to', ['email' => $ownerEmail, 'tower_id' => $towerId]);

                            $data->last_pumping_email_sent_at = $now;
                            $data->save();
                        } catch (\Exception $e) {
                            $mailStatus = 'Failed';
                            Log::error('Failed to send alert email', ['email' => $ownerEmail, 'tower_id' => $towerId, 'error' => $e->getMessage()]);
                        } finally {
                            $activityLog = Crypt::encryptString("Alert: " . json_encode($details['body']) . " Mail Status: " . $mailStatus);

                            Towerlogs::create([
                                'ID_tower' => $towerId,
                                'activity' => $activityLog,
                            ]);

                            Log::info('Alert logged in tbl_towerlogs', ['tower_id' => $towerId, 'activity' => $body]);
                        }
                    } else {
                        Log::error("Owner not found for tower ID {$towerId}.");
                    }
                } else {
                    Log::info("No consecutive pump status 0 for tower ID $towerId.");
                }
            }
        }

    }

    private function decrypt_data($encrypted_data, $method, $key, $iv)
    {
        try {

            $encrypted_data = base64_decode($encrypted_data);
            $decrypted_data = openssl_decrypt($encrypted_data, $method, $key, OPENSSL_NO_PADDING, $iv);
            $decrypted_data = rtrim($decrypted_data, "\0");
            $decoded_msg = base64_decode($decrypted_data);
            return $decoded_msg;
        } catch (\Exception $e) {
            Log::error('Decryption error: ' . $e->getMessage());
            return null;
        }
    }

}
