<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    /**
     * Redirect to Google OAuth
     */
    public function redirect(): RedirectResponse
    {
        $clientId = config('services.google.client_id');
        // Use route helper to ensure exact URL match
        $redirectUri = route('google.callback', [], true);
        
        // Log for debugging
        \Log::info('Google OAuth redirect', [
            'redirect_uri' => $redirectUri,
            'client_id' => $clientId
        ]);
        
        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'access_type' => 'offline',
            'prompt' => 'consent',
        ];

        $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
        
        return redirect($authUrl);
    }

    /**
     * Handle Google OAuth callback
     */
    public function callback(Request $request): RedirectResponse
    {
        if ($request->has('error')) {
            return redirect()->route('login')
                ->withErrors(['error' => 'Đăng nhập Google bị từ chối.']);
        }

        $code = $request->input('code');
        
        if (!$code) {
            return redirect()->route('login')
                ->withErrors(['error' => 'Không nhận được mã xác thực từ Google.']);
        }

        // Exchange code for access token
        try {
            // Use route helper to ensure exact URL match with authorization request
            $redirectUri = route('google.callback', [], true);
            
            $tokenResponse = Http::withoutVerifying()
                ->asForm()
                ->post('https://oauth2.googleapis.com/token', [
                    'client_id' => config('services.google.client_id'),
                    'client_secret' => config('services.google.client_secret'),
                    'code' => $code,
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => $redirectUri,
                ]);

            if (!$tokenResponse->successful()) {
                \Log::error('Google OAuth token error', [
                    'status' => $tokenResponse->status(),
                    'body' => $tokenResponse->body()
                ]);
                return redirect()->route('login')
                    ->withErrors(['error' => 'Không thể xác thực với Google. Vui lòng thử lại.']);
            }

            $tokenData = $tokenResponse->json();
            $accessToken = $tokenData['access_token'] ?? null;

            if (!$accessToken) {
                \Log::error('Google OAuth no access token', ['response' => $tokenData]);
                return redirect()->route('login')
                    ->withErrors(['error' => 'Không nhận được token từ Google.']);
            }

            // Get user info from Google
            $userResponse = Http::withoutVerifying()
                ->withToken($accessToken)
                ->get('https://www.googleapis.com/oauth2/v2/userinfo');

            if (!$userResponse->successful()) {
                \Log::error('Google OAuth user info error', [
                    'status' => $userResponse->status(),
                    'body' => $userResponse->body()
                ]);
                return redirect()->route('login')
                    ->withErrors(['error' => 'Không thể lấy thông tin người dùng từ Google.']);
            }

            $googleUser = $userResponse->json();
            
            // Log Google user info for debugging
            \Log::info('Google user info received', [
                'email' => $googleUser['email'] ?? 'N/A',
                'name' => $googleUser['name'] ?? 'N/A',
                'picture' => isset($googleUser['picture']) ? 'Yes' : 'No',
                'full_response' => $googleUser
            ]);
        } catch (\Exception $e) {
            \Log::error('Google OAuth exception', ['error' => $e->getMessage()]);
            return redirect()->route('login')
                ->withErrors(['error' => 'Có lỗi xảy ra khi đăng nhập với Google. Vui lòng thử lại.']);
        }

        // Find or create user
        $user = User::where('email', $googleUser['email'])->first();

        if (!$user) {
            // Create new user with customer role (not employee)
            // Only admin can set user as employee later
            $user = User::create([
                'name' => $googleUser['name'] ?? $googleUser['email'],
                'email' => $googleUser['email'],
                'password' => bcrypt(Str::random(32)), // Random password since using OAuth
                'avatar' => $googleUser['picture'] ?? null,
                'status' => 'Hoạt động', // Use Vietnamese enum value
                'role_id' => 3, // Default to "Khách hàng" (Customer) role
            ]);
        } else {
            // Update user info from Google
            $updateData = [];
            
            // Update name from Google if available
            if (isset($googleUser['name']) && $googleUser['name']) {
                $updateData['name'] = $googleUser['name'];
            }
            
            // Update avatar if available and user doesn't have one or wants to update
            if (isset($googleUser['picture']) && $googleUser['picture']) {
                if (!$user->avatar || $user->avatar !== $googleUser['picture']) {
                    $updateData['avatar'] = $googleUser['picture'];
                }
            }
            
            if (!empty($updateData)) {
                $user->update($updateData);
            }
        }

        // Login user
        Auth::login($user, true);

        // Redirect based on user role
        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        }

        if ($user->isEmployee()) {
            return redirect()->intended(route('employee.appointments.index', absolute: false));
        }

        return redirect()->intended(route('site.home', absolute: false));
    }
}
