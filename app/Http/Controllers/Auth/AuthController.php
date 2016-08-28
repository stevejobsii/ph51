<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;
use Session;
use Auth;
use Users;
use Flash;
use App\Http\Requests\StoreUserRequest;
use Phphub\Listeners\UserCreatorListener;
use Jrean\UserVerification\Traits\VerifiesUsers;
use Jrean\UserVerification\Facades\UserVerification;
use Jrean\UserVerification\Exceptions\UserNotFoundException;
use Jrean\UserVerification\Exceptions\UserIsVerifiedException;
use Jrean\UserVerification\Exceptions\TokenMismatchException;
use App\Http\Controllers\Traits\SocialiteHelper;

class AuthController extends Controller implements UserCreatorListener
{
    use VerifiesUsers,SocialiteHelper;

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct(User $userModel)
    {
        $this->middleware('guest', ['except' => ['logout', 'oauth', 'callback', 'getVerification', 'userBanned']]);
    }

    
    //登录
    private function loginUser($user)
    {
        //被禁用户无法再登录
        if ($user->is_banned == 'yes') {
            return $this->userIsBanned($user);
        }

        return $this->userFound($user);
    }

    //退出
    public function logout()
    {
        Auth::logout();
        Flash::success(lang('Operation succeeded.'));
        return redirect(route('home'));
    }

    //显示小窗口需要登录后才能继续操作。
    public function loginRequired()
    {
        return view('auth.loginrequired');
    }

    //显示小窗口很抱歉, 当前用户没有权限继续操作. 有什么问题请联系管理员.
    public function adminRequired()
    {
        return view('auth.adminrequired');
    }

    /**
     * Shows a user what their new account will look like.
     */
    public function create()
    {
        if (! Session::has('oauthData')) {
            return redirect(route('login'));
        }

        $oauthData = array_merge(Session::get('oauthData'), Session::get('_old_input', []));
        return view('auth.signupconfirm', compact('oauthData'));
    }

    /**
     * Actually creates the new user account
     */
    public function store(StoreUserRequest $request)
    {
        if (! Session::has('oauthData')) {
            return redirect(route('login'));
        }
        $oauthUser = array_merge(Session::get('oauthData'), $request->only('name', 'email'));
        $userData = array_only($oauthUser, array_keys($request->rules()));
        $userData['register_source'] = $oauthUser['driver'];

        return app(\Phphub\Creators\UserCreator::class)->create($this, $userData);
    }

    //显示小窗口对不起，您的账号已被禁用！
    public function userBanned()
    {
        if (Auth::check() && Auth::user()->is_banned == 'no') {
            return redirect(route('home'));
        }

        return view('auth.userbanned');
    }

    /**
     * ----------------------------------------
     * UserCreatorListener Delegate
     * ----------------------------------------
     */

    public function userValidationError($errors)
    {
        return redirect('/');
    }

    public function userCreated($user)
    {
        Auth::login($user, true);
        Session::forget('oauthData');

        Flash::success(lang('Congratulations and Welcome!'));

        return redirect(route('users.edit', Auth::user()->id));
    }

    public function oauth(Request $request) { 
        $driver = $request->query('driver');      
        if ($driver == 'qq') {
        return \Socialite::with('qq')->redirect();}
    }

    public function weixin() {
        return \Socialite::with('weixin')->redirect();
    }

    public function weibo() {
        return \Socialite::with('weibo')->redirect();
    }

    //callback
    public function callback($provider) {
        if (Input::has('code')) {
            $oauthUser = \Socialite::with($provider)->user();
            if (is_null($user = Users::where('name', '=', $oauthUser->nickname)->first())){
            $user = Users::create([
                'name' => $oauthUser->nickname,
                'email'=> $oauthUser->email,
                'avatar'=>$oauthUser->avatar,
            ]);
            }
            Auth::login($user,true);
        }
        return redirect('');
    }

    /**
     * ----------------------------------------
     * GithubAuthenticatorListener Delegate
     * ----------------------------------------
     */
    public function userNotFound($driver, $registerUserData)
    {
        // if ($driver == 'github') {
        //     $oauthData['image_url'] = $registerUserData->user['avatar_url'];
        //     $oauthData['github_id'] = $registerUserData->user['id'];
        //     $oauthData['github_url'] = $registerUserData->user['url'];
        //     $oauthData['github_name'] = $registerUserData->nickname;
        //     $oauthData['name'] = $registerUserData->user['name'];
        //     $oauthData['email'] = $registerUserData->user['email'];
        // } else
        if ($driver == 'qq') {
            $oauthData['image_url'] = $registerUserData->avatar;
            $oauthData['name'] = $registerUserData->nickname;
            $oauthData['email'] = $registerUserData->email;
        }elseif ($driver == 'wechat') {
            $oauthData['image_url'] = $registerUserData->avatar;
            $oauthData['wechat_openid'] = $registerUserData->id;
            $oauthData['name'] = $registerUserData->nickname;
            $oauthData['email'] = $registerUserData->email;
            $oauthData['wechat_unionid'] = $registerUserData->user['unionid'];
        }

        $oauthData['driver'] = $driver;
        Session::put('oauthData', $oauthData);

        return redirect(route('signup'));
    }

    // 数据库有用户信息, 登录用户
    public function userFound($user)
    {
        Auth::loginUsingId($user->id);
        Session::forget('oauthData');

        Flash::success(lang('Login Successfully.'));

        //成功登录进入用户修改界面https://phphub.org/users/5578/edit
        return redirect(route('users.edit', Auth::user()->id));
    }

    // 用户屏蔽
    public function userIsBanned($user)
    {
        return redirect(route('user-banned'));
    }

    /**
     * ----------------------------------------
     * Email Validation邮箱验证
     * ----------------------------------------
     */
    public function getVerification(Request $request, $token)
    {
        $this->validateRequest($request);
        try {
            UserVerification::process($request->input('email'), $token, 'users');
            Flash::success(lang('Email validation successed.'));
            return redirect('/');
        } catch (UserNotFoundException $e) {
            Flash::error(lang('Email not found'));
            return redirect('/');
        } catch (UserIsVerifiedException $e) {
            Flash::success(lang('Email validation successed.'));
            return redirect('/');
        } catch (TokenMismatchException $e) {
            Flash::error(lang('Token mismatch'));
            return redirect('/');
        }

        return redirect('/');
    }
}
