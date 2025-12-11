<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('appointment.{appointmentId}', function ($user, $appointmentId) {
    // Chỉ user sở hữu appointment mới được lắng nghe
    $appointment = \App\Models\Appointment::find($appointmentId);
    
    if (!$appointment) {
        return false;
    }
    
    // Cho phép user sở hữu appointment hoặc admin/employee
    return $appointment->user_id === $user->id || $user->isAdmin() || $user->isEmployee();
});

