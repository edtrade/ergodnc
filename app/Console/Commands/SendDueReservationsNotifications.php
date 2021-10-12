<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use App\Notifications\UserReservationStartingNotification;
use App\Notifications\HostReservationStartingNotification;
use Illuminate\Support\Facades\Notification;

class SendDueReservationsNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'due_reservations_notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Reservation::query()
            ->with('ofiice.user')
            ->where('status',Reservation::STATUS_ACTIVE)
            ->where('start_date',now()->toDateString())
            ->each(function($reservation){
                Notification::send(auth()->user(),new UserReservationStartingNotification($reservation));
                Notification::send($rservation->office->user ,new HostReservationStartingNotification($reservation));
            });
        return 0;
    }
}
