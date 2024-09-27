<?php

namespace App\Console\Commands;

use App\Mail\Alert;
use App\Models\Owner;
use App\Models\Tower;
use App\Models\Towerlog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
    protected $description = 'Command';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Log the execution start of the command
        Log::info('Harvest Reminder command executed.');

        // Fetch towers with a startdate and enddate
        $towers = Tower::whereNotNull('enddate')->whereNotNull('startdate')->get();

        foreach ($towers as $tower) {
            // Use tower's enddate instead of startdate for calculations
            $towerEndDate = Carbon::parse($tower->enddate)->startOfDay(); // Ensure time is set to 00:00:00

            // Calculate the "one day later" and "one week later" based on the enddate
            $now = Carbon::now()->startOfDay(); // Current date (today) at 00:00:00
            $oneDayLater = $now->copy()->addDay(); // Tomorrow
            $oneWeekLater = $now->copy()->addWeek(); // A week later

            // Log the dates for debugging purposes
            Log::info('Dates:', [
                'now' => $now->toDateString(),
                'oneDayLater' => $oneDayLater->toDateString(),
                'oneWeekLater' => $oneWeekLater->toDateString(),
                'enddate' => $towerEndDate->toDateString(),
            ]);

            $owner = Owner::find($tower->OwnerID);
            if ($owner) {
                // Decrypt the email
                $ownerEmail = Crypt::decryptString($owner->email);
                Log::info("Owner's email for tower ID {$tower->id}: {$ownerEmail}");

                // Check if harvest is today, tomorrow, or in one week
                if ($towerEndDate->isSameDay($now)) {
                    // Harvest today
                    $subject = "Reminder: Tower Harvest Date Today";
                    $body = "Dear Owner, Today is the end date for tower {$tower->id}. Please take the necessary actions.";
                    Log::info('Condition matched: Harvest today', ['tower_id' => $tower->id, 'email' => $ownerEmail]);

                } elseif ($towerEndDate->isSameDay($oneDayLater)) {
                    // Harvest tomorrow
                    $subject = "Reminder: Tower Harvest Date Tomorrow";
                    $body = "Dear Owner, The end date for tower {$tower->id} is tomorrow ({$towerEndDate->toDateString()}). Please take the necessary actions.";
                    Log::info('Condition matched: Harvest tomorrow', ['tower_id' => $tower->id, 'email' => $ownerEmail]);

                } elseif ($towerEndDate->isSameDay($oneWeekLater)) {
                    // Harvest in one week
                    $subject = "Reminder: Tower Harvest Date in One Week";
                    $body = "Dear Owner, The end date for tower {$tower->id} is in one week ({$towerEndDate->toDateString()}). Please take the necessary actions.";
                    Log::info('Condition matched: Harvest in one week', ['tower_id' => $tower->id, 'email' => $ownerEmail]);

                } else {
                    continue; // Skip if no conditions matched
                }

                // Try sending the email and log the outcome
                try {
                    Log::info('Attempting to send email to: ', ['email' => $ownerEmail]);
                    $details = ['title' => $subject, 'body' => $body];
                    Mail::to($ownerEmail)->send(new Alert($details));
                    Log::info('Email sent successfully to: ', ['email' => $ownerEmail]);
                    $mailStatus = 'Sent';
                } catch (\Exception $e) {
                    $mailStatus = 'Failed';
                    Log::error('Failed to send alert email', [
                        'email' => $ownerEmail,
                        'tower_id' => $tower->id,
                        'error' => $e->getMessage(),
                    ]);
                } finally {
                    // Encrypt and log the activity
                    $activityLog = Crypt::encryptString(json_encode(['body' => $body]) . " Mail Status: " . $mailStatus);
                    Towerlog::create([
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
