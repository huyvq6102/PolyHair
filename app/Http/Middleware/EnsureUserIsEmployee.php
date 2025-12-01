<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsEmployee
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
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để truy cập trang nhân viên.');
        }

        $user = auth()->user();
        
        // Kiểm tra quyền nhân viên
        if (!$user->isEmployee()) {
            abort(403, 'Bạn không có quyền truy cập trang nhân viên. Vui lòng liên hệ quản trị viên.');
        }

        return $next($request);
    }
}
