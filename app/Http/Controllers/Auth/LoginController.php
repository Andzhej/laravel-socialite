<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

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

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback($provider)
    {
        if($provider == 'github') {
            $gitHubUser = Socialite::driver('github')->user();

            //check if user exists
            $user = User::where('github_id', $gitHubUser->getId())->first();
        }
        
        if(!$user) {
        //add user
            $user = User::create([
                'email' => $gitHubUser->getEmail(),
                'name' => (($gitHubUser->getName()) ? $gitHubUser->getName() : $gitHubUser->getNickname()),
                'github_id' => $gitHubUser->getId()
            ]);
        }

        //login user
        Auth::login($user, true);

        return redirect($this->redirectTo);
    }
}
