<?php

namespace App\Console\Commands;

use App\Mail\Alert;
use App\Models\Owner;
use App\Models\Tower;
use App\Models\Towerlogs;
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

        // Fetch only dates (without time)
        $now = Carbon::now()->toDateString();
        $oneDayLater = Carbon::now()->addDay()->toDateString();
        $oneWeekLater = Carbon::now()->addWeek()->toDateString();

        // Log the dates (without time)
        Log::info('Dates:', [
            'now' => $now,
            'oneDayLater' => $oneDayLater,
            'oneWeekLater' => $oneWeekLater,
        ]);

        $towers = Tower::whereNotNull('enddate')
            ->where(function ($query) use ($now, $oneDayLater, $oneWeekLater) {
                $query->whereBetween('enddate', [$now, $oneDayLater])
                    ->orWhere('enddate', $oneWeekLater); // Correct condition for one week later
            })
            ->get();

        foreach ($towers as $tower) {
            $owner = Owner::find($tower->OwnerID);
            if ($owner) {
                $ownerEmail = Crypt::decryptString($owner->email);
                Log::info("Owner's email for tower ID {$tower->id}: {$ownerEmail}");

                // Ensure enddate is a Carbon instance and log it
                $endDate = Carbon::parse($tower->enddate)->toDateString();
                Log::info("End date for tower ID {$tower->id}: {$endDate}");

                if ($endDate === $oneDayLater) {
                    $subject = "Reminder: Tower Harvest Date Today";
                    $body = "Dear Owner, Today is the end date for tower {$tower->id}. Please take the necessary actions.";

                    $mode = '4';
                    $encryptedMode = Crypt::encryptString($mode);
                    Tower::query()->update(['mode' => $encryptedMode]);

                    Log::info('Condition matched: Harvest today', ['tower_id' => $tower->id, 'email' => $ownerEmail]);

                } elseif ($endDate === Carbon::parse($oneDayLater)->addDay()->toDateString()) {
                    $subject = "Reminder: Tower Harvest Date Tomorrow";
                    $body = "Dear Owner, The end date for tower {$tower->id} is tomorrow ({$endDate}). Please take the necessary actions.";

                    Log::info('Condition matched: Harvest tomorrow', ['tower_id' => $tower->id, 'email' => $ownerEmail]);

                } elseif ($endDate === $oneWeekLater) {
                    $subject = "Reminder: Tower Harvest Date in One Week";
                    $body = "Dear Owner, The end date for tower {$tower->id} is in one week ({$endDate}). Please take the necessary actions.";

                    Log::info('Condition matched: Harvest in one week', ['tower_id' => $tower->id, 'email' => $ownerEmail]);

                } else {
                    continue;
                }

                try {
                    Log::info('Attempting to send email to: ', ['email' => $ownerEmail]);

                    // Prepare email details for the Alert mailable
                    $details = [
                        'title' => $subject,
                        'body' => $body,
                    ];

                    // Send the email using Alert mailable class
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
