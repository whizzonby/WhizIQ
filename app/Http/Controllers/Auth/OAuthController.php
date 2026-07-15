<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\Trait\RedirectAwareTrait;
use App\Http\Controllers\Controller;
use App\Mail\User\WelcomeMail;
use App\Models\OauthLoginProvider;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends Controller
{
    use RedirectAwareTrait;

    public function redirect(string $provider)
    {
        $providerObj = OauthLoginProvider::where('provider_name', $provider)->firstOrFail();

        if (! $providerObj->enabled) {
            abort(404);
        }

        if (Auth::check()) {
            return redirect()->route('home');
        }

        if (Redirect::getIntendedUrl() === null) {
            $this->rememberIntendedUrl(url()->previous());
        }

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        $providerObj = OauthLoginProvider::where('provider_name', $provider)->firstOrFail();

        if (! $providerObj->enabled) {
            abort(404);
        }

        $oauthUser = Socialite::driver($provider)->user();

        $isRegistration = false;
        $resolvedUser   = null;

        $password = Str::random();
        DB::transaction(function () use ($provider, $oauthUser, $password, &$isRegistration, &$resolvedUser) {
            $user = User::where('email', $oauthUser->email)->first();

            if ($user) {
                $user->update([
                    'name' => $oauthUser->name ?? $user->name ?? $oauthUser->nickname,
                ]);
            } else {
                $user = User::create([
                    'name' => $oauthUser->name ?? $oauthUser->nickname ?? '',
                    'email' => $oauthUser->email,
                    'password' => $password,
                ]);

                $isRegistration = true;
            }

            $user->userParameters()->updateOrCreate(
                ['name' => 'oauth_provider_'.$provider],
                ['value' => $provider]
            );

            if (property_exists($oauthUser, 'id') && $oauthUser->id) {
                $user->userParameters()->updateOrCreate(
                    ['name' => 'oauth_'.$provider.'_id'],
                    ['value' => $oauthUser->id]
                );
            }

            if (property_exists($oauthUser, 'token') && $oauthUser->token) {
                $user->userParameters()->updateOrCreate(
                    ['name' => 'oauth_'.$provider.'_token'],
                    ['value' => $oauthUser->token]
                );
            }

            if (property_exists($oauthUser, 'refreshToken') && $oauthUser->refreshToken) {
                $user->userParameters()->updateOrCreate(
                    ['name' => 'oauth_'.$provider.'_refresh_token'],
                    ['value' => $oauthUser->refreshToken]
                );
            }

            if (property_exists($oauthUser, 'expiresIn') && $oauthUser->expiresIn) {
                $user->userParameters()->updateOrCreate(
                    ['name' => 'oauth_'.$provider.'_expires_in'],
                    ['value' => $oauthUser->expiresIn]
                );
            }

            if (property_exists($oauthUser, 'avatar') && $oauthUser->avatar) {
                $user->userParameters()->updateOrCreate(
                    ['name' => 'oauth_'.$provider.'_avatar'],
                    ['value' => $oauthUser->avatar]
                );
            }

            if (property_exists($oauthUser, 'nickname') && $oauthUser->nickname) {
                $user->userParameters()->updateOrCreate(
                    ['name' => 'oauth_'.$provider.'_nickname'],
                    ['value' => $oauthUser->nickname]
                );
            }

            $user->markEmailAsVerified();

            $resolvedUser = $user;
        });

        // Login AFTER the transaction commits so the session is tied to a
        // fully persisted user record
        Auth::login($resolvedUser);

        if ($isRegistration) {
            Mail::to($resolvedUser)->queue(new WelcomeMail($resolvedUser));
            return redirect()->route('registration.thank-you');
        }

        return redirect($this->getRedirectUrl($resolvedUser));
    }
}
