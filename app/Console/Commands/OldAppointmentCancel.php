<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Console\Command;

class OldAppointmentCancel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointment:cancel';

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
        $updated = Appointment::where('start_time', '<', Carbon::now())->where('status', 'booked')->update(['status' => 'cancelled']);
        $this->info("Total appointments cancelled: {$updated}");
    }
}
