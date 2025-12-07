<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;

class UpdateAppointmentBookingCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:update-booking-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update booking codes for existing appointments with new format POLY-HB-XXX';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating booking codes for existing appointments with new format POLY-HB-XXX...');

        // Get all appointments ordered by ID to maintain sequence
        $appointments = Appointment::orderBy('id', 'asc')
            ->get();

        if ($appointments->isEmpty()) {
            $this->info('No appointments found.');
            return 0;
        }

        $bar = $this->output->createProgressBar($appointments->count());
        $bar->start();

        $updated = 0;
        $sequence = 1;
        $prefix = 'POLY-HB';

        foreach ($appointments as $appointment) {
            try {
                // Generate new booking code with format POLY-HB-001
                $sequenceFormatted = str_pad($sequence, 3, '0', STR_PAD_LEFT);
                $bookingCode = "{$prefix}-{$sequenceFormatted}";
                
                // Check if booking code already exists (shouldn't happen, but just in case)
                while (Appointment::where('booking_code', $bookingCode)
                    ->where('id', '!=', $appointment->id)
                    ->exists()) {
                    $sequence++;
                    $sequenceFormatted = str_pad($sequence, 3, '0', STR_PAD_LEFT);
                    $bookingCode = "{$prefix}-{$sequenceFormatted}";
                }
                
                $appointment->update(['booking_code' => $bookingCode]);
                $updated++;
                $sequence++;
            } catch (\Exception $e) {
                $this->error("Error updating appointment {$appointment->id}: " . $e->getMessage());
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Successfully updated {$updated} appointment(s) with new format POLY-HB-XXX.");

        return 0;
    }
}
