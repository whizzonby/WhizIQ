<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\Trait\RedirectAwareTrait;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LoginService;
use App\Validator\LoginValidator;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;
    use RedirectAwareTrait;

    public function __construct(
        private LoginValidator $loginValidator,
        private LoginService $loginService,
    ) {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        if (Redirect::getIntendedUrl() === null) {
            $this->rememberIntendedUrl(url()->previous()); // make sure we redirect back to a safe page we came from
        }

        return view('auth.login', [
            'isOtpLoginEnabled' => config('app.otp_login_enabled'),
        ]);
    }

    protected function authenticated(Request $request, User $user)
    {
        if ($user->is_blocked) {
            $this->guard()->logout();

            return redirect()->route('login')->withErrors([
                'email' => 'Your account has been blocked. Please contact support.',
            ]);
        }

        return redirect()->intended($this->getRedirectUrl($user));
    }

    protected function validateLogin(Request $request)
    {
        $this->loginValidator->validateRequest($request);
    }

    protected function attemptLogin(Request $request)
    {
        return $this->loginService->attempt($this->credentials($request), $request->boolean('remember'));
    }
}
