<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBannedUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            // Refresh user từ database để lấy trạng thái mới nhất (kể cả khi admin thay đổi trong MySQL)
            $user = auth()->user();
            $user->refresh(); // Lấy dữ liệu mới nhất từ database
            
            // Kiểm tra nếu tài khoản bị khóa
            if ($user->isBanned()) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                // Refresh lại để lấy thông tin mới nhất
                $user->refresh();
                
                $bannedUntil = $user->banned_until;
                $timeRemaining = $this->formatBanTimeRemaining($bannedUntil);
                
                // Tạo thông báo dựa trên status
                if ($user->status === 'Cấm') {
                    $banMessage = 'Tài khoản của bạn đã bị cấm vĩnh viễn. ' . 
                                 ($user->ban_reason ? "Lý do: {$user->ban_reason}" : '');
                } else {
                    $banMessage = 'Tài khoản của bạn đã bị khóa. ' . 
                                 ($timeRemaining 
                                     ? "Tài khoản sẽ được mở khóa sau {$timeRemaining}. " 
                                     : 'Tài khoản sẽ được mở khóa sớm. ') .
                                 ($user->ban_reason ? "Lý do: {$user->ban_reason}" : '');
                }
                
                return redirect()->route('login')
                    ->with('error', $banMessage);
            }
        }
        
        return $next($request);
    }

    /**
     * Format thời gian còn lại của ban thành chuỗi dễ đọc.
     */
    protected function formatBanTimeRemaining($bannedUntil)
    {
        if (!$bannedUntil) {
            return null;
        }

        $now = now();
        if ($now->greaterThanOrEqualTo($bannedUntil)) {
            return null;
        }

        // Sử dụng diffInRealMinutes để có số chính xác hơn, sau đó làm tròn lên
        $diffInMinutes = (int) ceil($now->diffInRealMinutes($bannedUntil, false));
        
        if ($diffInMinutes < 60) {
            return $diffInMinutes . ' phút';
        }

        $hours = floor($diffInMinutes / 60);
        $minutes = $diffInMinutes % 60;

        if ($minutes == 0) {
            return $hours . ' giờ';
        }

        return $hours . ' giờ ' . $minutes . ' phút';
    }
}
