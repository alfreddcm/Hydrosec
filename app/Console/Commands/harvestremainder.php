<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class harvestremainder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:harvestremainder';

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
        $towers = Tower::whereNotNull('enddate')
            ->where(function ($query) use ($now, $oneDayLater, $daysBefore) {
                $query->whereBetween('enddate', [$now->copy()->addDays($daysBefore), $oneDayLater])
                    ->orWhereBetween('enddate', [$oneDayLater, $oneDayLater]);
            })
            ->get();

        foreach ($towers as $tower) {
            $owner = Owner::find($tower->OwnerID);
            if ($owner) {
                $ownerEmail = Crypt::decryptString($owner->email);
                if ($tower->enddate->isSameDay($oneDayLater)) {
                    $subject = "Reminder: Tower Harvest Date Today";
                    $body = "Dear Owner,\n\nThis is a reminder that today is the end date for tower {$tower->id}. Please take the necessary actions.\n\nBest regards,\nHydrosec";
                    
                    $mode = '4';
                    $encryptedMode = Crypt::encryptString($mode);
                    Tower::query()->update(['mode' => $encryptedMode]);

                } elseif ($tower->enddate->isSameDay($oneDayLater->addDay())) {
                    $subject = "Reminder: Tower Harvest Date Tomorrow";
                    $body = "Dear Owner,\n\nThis is a reminder that the end date for tower {$tower->id} is tomorrow on {$tower->enddate->format('Y-m-d')}. Please take the necessary actions.\n\nBest regards,\nYour Team";
                } else {
                    continue;
                }

                try {
                    Mail::to($ownerEmail)->send(new Harvest($subject, $body));
                    $mailStatus = 'Sent';
                    Log::info('Alert email sent to', ['email' => $ownerEmail, 'tower_id' => $tower->id]);
                } catch (\Exception $e) {
                    $mailStatus = 'Failed';
                    Log::error('Failed to send alert email', ['email' => $ownerEmail, 'tower_id' => $tower->id, 'error' => $e->getMessage()]);
                } finally {
                    $activityLog = Crypt::encryptString("Alert: Conditions detected - " . json_encode(['body' => $body]) . " Mail Status: " . $mailStatus);

                    TowerLogs::create([
                        'ID_tower' => $tower->id,
                        'activity' => $activityLog,
                    ]);

                    Log::info('Alert logged in tbl_towerlogs', ['tower_id' => $tower->id, 'activity' => $body]);
                }

            } else {
                $this->error("Owner not found for tower ID {$tower->id}.");
            }
        }
    }
}
