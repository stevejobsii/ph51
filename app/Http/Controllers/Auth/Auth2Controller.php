<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Validator;
use Auth;
use Input;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Intervention\Image\ImageManagerStatic as Image;

class Auth2Controller extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */


    use AuthenticatesAndRegistersUsers, ThrottlesLogins;
    //注册后返回主页
    protected $redirectPath = '/';
    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'getLogout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }
    

    public function qq() {       
        return \Socialite::with('qq')->redirect();
    }

    public function weixin() {
        return \Socialite::with('weixin')->redirect();
    }

    public function weibo() {
        return \Socialite::with('weibo')->redirect();
    }

    // public function weixinweb() {
    //     return \Socialite::with('weixinweb')->redirect();
    // }

    public function callback($provider) {
        if (Input::has('code')) {
            $oauthUser = \Socialite::with($provider)->user();
            if (is_null($user = User::where('name', '=', $oauthUser->nickname)->first())){
            $user = User::create([
                'name' => $oauthUser->nickname,
                'email'=> $oauthUser->email,
                'avatar'=>$oauthUser->avatar,
            ]);
            }
            Auth::login($user,true);
        }
        return redirect('');
    }
    
    //需要登录／管理员／用户被禁
    public function loginRequired()
    {
        return view('auth.loginrequired');
    }

    public function adminRequired()
    {
        return view('auth.adminrequired');
    }
     
    public function userBanned()
    {
        if (Auth::check() && !Auth::user()->is_banned) {
            return Redirect::route('home');
        }
        //force logout
        Auth::logout();
        return view('auth.userbanned');
    }

    // 用户屏蔽
    public function userIsBanned($user)
    {
        return Redirect::route('user-banned');
    }

}








