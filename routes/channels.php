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
    // Kiểm tra appointment có tồn tại không
    $appointment = \App\Models\Appointment::find($appointmentId);
    
    if (!$appointment) {
        return false;
    }
    
    // Nếu user không authenticated, Laravel sẽ tự động reject private channel
    // Guest users sẽ fallback sang polling (đã được implement trong success.blade.php)
    if (!$user) {
        return false;
    }
    
    // Cho phép user sở hữu appointment, admin, hoặc employee
    // Điều này đảm bảo authenticated users có thể nhận real-time updates
    return $appointment->user_id === $user->id || $user->isAdmin() || $user->isEmployee();
});

