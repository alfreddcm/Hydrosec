<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // check houly
        // Get the current time and the next hour
       
        $now = Carbon::now();
        $nextHour = $now->copy()->addHour();
        $hoursBefore = 1;
        
        $allTowers = Tower::pluck('id');
        
        $towersWithData = Tower::whereNotNull('enddate')
            ->where(function ($query) use ($now, $nextHour, $hoursBefore) {
                $query->whereBetween('enddate', [$now->copy()->subHours($hoursBefore), $nextHour])
                    ->orWhereBetween('enddate', [$nextHour, $nextHour]);
            })
            ->pluck('id');
        
        $towersWithoutData = $allTowers->diff($towersWithData);
        
        foreach ($towersWithoutData as $towerId) {
            $tower = Tower::find($towerId); // Make sure to use the correct tower instance
            if (!$tower) {
                Log::error("Tower not found with ID $towerId.");
                continue;
            }
        
            // Check for two consecutive statuses of 0
            $recentStatuses = Tower::where('id', $towerId)
                ->orderBy('created_at', 'desc')
                ->limit(2)
                ->pluck('status');
        
            if (count($recentStatuses) === 2 && 
                Crypt::decryptString($recentStatuses[0]) == 0 && 
                Crypt::decryptString($recentStatuses[1]) == 0) {
        
                $owner = Owner::find($tower->OwnerID);
                if ($owner) {
                    $subject = "Pump Reminder";
                    $body = "Dear Owner,
                    \n\nTower did not pump \n\n
                    Best regards,\nYour Team";
        
                    $ownerEmail = Crypt::decryptString($owner->email);
                    try {
                        // Your email sending logic here
                        $mailStatus = 'Sent';
                        Log::info('Alert email sent to', ['email' => $ownerEmail, 'tower_id' => $tower->id]);
                    } catch (\Exception $e) {
                        $mailStatus = 'Failed';
                        Log::error('Failed to send alert email', ['email' => $ownerEmail, 'tower_id' => $tower->id, 'error' => $e->getMessage()]);
                    } finally {
                        // Encrypt and log the activity, regardless of email success or failure
                        $activityLog = Crypt::encryptString("Alert: Conditions detected - " . json_encode(['body' => $body]) . " Mail Status: " . $mailStatus);
        
                        $tow = Tower::find($tower->id);
                        $tow->status = Crypt::encryptString('4');
                        $tow->save();
        
                        TowerLogs::create([
                            'ID_tower' => $tower->id,
                            'activity' => $activityLog,
                        ]);
        
                        Log::info('Alert logged in tbl_towerlogs', ['tower_id' => $tower->id, 'activity' => $body]);
                    }
        
                } else {
                    $this->error("Owner not found for tower ID {$tower->id}.");
                }
            } else {
                Log::info("No consecutive pump status 0 for tower ID $towerId.");
            }
        }
        
    }
}
