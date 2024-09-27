<?php

namespace App\Console\Commands;

use App\Mail\Harvest;
use App\Models\Owner;
use App\Models\Tower;
use App\Models\TowerLogs;
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
        Log::info('Harvest Remainder command executed.');

        $now = Carbon::now();
        $oneDayLater = $now->copy()->addDay();
        $oneWeekLater = $now->copy()->addWeek(); // One week later
        $daysBefore = -1;

        $towers = Tower::whereNotNull('enddate')
            ->where(function ($query) use ($now, $oneDayLater, $oneWeekLater, $daysBefore) {
                $query->whereBetween('enddate', [$now->copy()->addDays($daysBefore), $oneDayLater])
                    ->orWhereBetween('enddate', [$oneDayLater, $oneDayLater])
                    ->orWhere('enddate', $oneWeekLater); // Adding a condition for one week later
            })
            ->get();

        foreach ($towers as $tower) {
            $owner = Owner::find($tower->OwnerID);
            if ($owner) {
                $ownerEmail = Crypt::decryptString($owner->email);

                // Ensure enddate is a Carbon instance
                $endDate = Carbon::parse($tower->enddate); // Cast enddate to Carbon

                // Calculate remaining days
                $remainingDays = $endDate->diffInDays($now, false);
                Log::debug("Tower ID: {$tower->id}, Remaining days until harvest: {$remainingDays}");

                if ($endDate->isSameDay($oneDayLater)) {
                    $subject = "Reminder: Tower Harvest Date Today";
                    $body = "Dear Owner, This is a reminder that today is the end date for tower {$tower->id}. Please take the necessary actions.";

                    $mode = '4';
                    $encryptedMode = Crypt::encryptString($mode);
                    Tower::query()->update(['mode' => $encryptedMode]);

                } elseif ($endDate->isSameDay($oneDayLater->addDay())) {
                    $subject = "Reminder: Tower Harvest Date Tomorrow";
                    $body = "Dear Owner, This is a reminder that the end date for tower {$tower->id} is tomorrow on {$endDate->format('Y-m-d')}. Please take the necessary actions.";

                } elseif ($endDate->isSameDay($oneWeekLater)) { // New condition for one week later
                    $subject = "Reminder: Tower Harvest Date in One Week";
                    $body = "Dear Owner, This is a reminder that the end date for tower {$tower->id} is one week away on {$endDate->format('Y-m-d')}. Please take the necessary actions.";

                } else {
                    continue;
                }

                try {
                    Log::info('Attempting to send email to: ', ['email' => $ownerEmail]);

                    Mail::to($ownerEmail)->send(new Harvest($subject, $body));

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
