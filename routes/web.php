<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerificationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    try {
        return redirect()->route('login');
    } catch (\Exception $e) {
        \Log::error('dev-send-test-email failed', ['error' => $e->getMessage()]);
    }
});

Route::get('/test', function () {
    return 'Laravel is working! Config loaded: ' . (config('app.key') ? 'YES' : 'NO');
});

// Local-only: send a test email using current mail configuration
Route::get('/dev-send-test-email', function (Request $request) {
    if (config('app.env') !== 'local') {
        return response('Not available', 404);
    }
    $ip = $request->ip();
    if (!in_array($ip, ['127.0.0.1', '::1'])) {
        return response('Forbidden', 403);
    }
    $request->validate(['email' => 'required|email']);
    $to = $request->query('email');

    try {
        \Illuminate\Support\Facades\Mail::to($to)->send(new \App\Mail\OtpMail('000000'));
        return response()->json(['status' => 'sent']);
    } catch (\Exception $e) {
        \Log::error('dev-send-test-email failed', ['error' => $e->getMessage()]);
        return response()->json(['status' => 'error', 'message' => 'Unable to send test email. Check logs.'], 500);
    }
});

// Authentication Routes
Route::get('/login', function() {
    try {
        return view('auth.login');
    } catch (\Exception $e) {
        return 'Error loading login view: ' . $e->getMessage();
    }
})->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Registration Routes
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Email Verification Routes
Route::get('/email/verify', [VerificationController::class, 'show'])->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
Route::post('/email/resend', [VerificationController::class, 'resend'])->name('verification.resend');

// Protected Route
Route::get('/home', function () {
    return view('home');
})->middleware(['auth', 'verified'])->name('home');

// Development helper routes removed for safety.

    // OTP endpoints
    use Illuminate\Support\Facades\Mail;
    use App\Mail\OtpMail;
    use Illuminate\Http\Request as HttpRequest;

    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/send-otp', function (HttpRequest $request) {
            $request->validate(['email' => 'required|email|max:255']);
            $email = $request->input('email');

            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expires = now()->addMinutes(10);

            \DB::table('email_otps')->insert([
                'email' => $email,
                'code' => $code,
                'expires_at' => $expires,
                'used' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            try {
                Mail::to($email)->send(new OtpMail($code));
            } catch (\Exception $e) {
                // Log details but return a generic error to the client
                \Log::error('OTP send failed', ['email' => $email, 'error' => $e->getMessage()]);
                return response()->json(['status' => 'error', 'message' => 'Unable to send OTP at this time.'], 500);
            }

            return response()->json([
                'status' => 'sent',
                'expires_at' => $expires->toDateTimeString(),
                'expires_unix' => $expires->timestamp,
            ]);
        });

        Route::post('/verify-otp', function (HttpRequest $request) {
            $request->validate(['email' => 'required|email|max:255', 'code' => 'required|digits:6']);
            $email = $request->input('email');
            $code = $request->input('code');

            $otp = \DB::table('email_otps')
                ->where('email', $email)
                ->where('code', $code)
                ->where('used', false)
                ->where('expires_at', '>', now())
                ->first();

            if (!$otp) {
                // return generic invalid response to avoid leaking which part failed
                return response()->json(['status' => 'invalid'], 400);
            }

            // mark used
            \DB::table('email_otps')->where('id', $otp->id)->update(['used' => true, 'updated_at' => now()]);

            // If a user exists, mark email_verified_at; otherwise return ok for registration flow
            $user = \App\Models\User::where('email', $email)->first();
            if ($user) {
                $user->email_verified_at = now();
                $user->save();
            }

            return response()->json(['status' => 'verified']);
        });
    });

    // Development helpers were temporary and have been removed.

