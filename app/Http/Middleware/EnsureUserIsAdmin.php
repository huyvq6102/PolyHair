<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra đăng nhập
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để truy cập trang quản trị.');
        }

        $user = auth()->user();
        
        // Load role nếu chưa được load
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }
        
        // Kiểm tra quyền admin
        if (!$user->isAdmin()) {
            abort(403, 'Bạn không có quyền truy cập trang quản trị. Vui lòng liên hệ quản trị viên.');
        }

        return $next($request);
    }
}
