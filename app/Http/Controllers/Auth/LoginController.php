<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class LoginController extends Controller {

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

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';


    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index() {
        return view('auth.login');
    }


    /**
     * @return Response
     */
    public function redirect($provider) {
        session()->put('oauth_provider', $provider);

        return Socialite::driver(sprintf('olafnorge_%s', $provider))->redirect();
    }


    /**
     * @return Response
     */
    public function callback() {
        try {
            /** @var \Laravel\Socialite\Two\User $socialiteUser */
            $socialiteUser = Socialite::driver(sprintf('olafnorge_%s', session()->get('oauth_provider')))->user();
        } catch (InvalidStateException $invalidStateException) {
            return redirect()->route('auth.redirect', ['provider' => session()->get('oauth_provider')]);
        }

        $user = User::where('email', $socialiteUser->getEmail())->first();

        if ($user && $user->disabled) {
            return redirect()->route('index')->with('info', 'Account disabled.');
        }

        if (!$user) {
            User::create([
                'name' => $socialiteUser->getName(),
                'email' => $socialiteUser->getEmail(),
                'password' => uniqid(),
            ]);

            return redirect()->route('index')->with('info', 'Account created.');
        }

        Auth::guard()->login($user);

        return redirect()->intended(route('satis.configuration.index'));
    }
}
