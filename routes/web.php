<?php
# ------------------ Page Route ------------------------
Route::get('/', 'PagesController@home')->name('home');//主页
Route::get('/about', 'PagesController@about')->name('about');//关于这个社区
Route::get('/search', 'PagesController@search')->name('search');//用BING来搜索论坛内容
Route::get('/feed', 'PagesController@feed')->name('feed');//网站动态
Route::get('/sitemap', 'PagesController@sitemap');//网站动态
Route::get('/sitemap.xml', 'PagesController@sitemap');//网站动态xml

Route::get('/hall_of_fames', 'PagesController@hallOfFames')->name('hall_of_fames');//社区名人堂

# ------------------ User stuff ------------------------

Route::get('/users/{id}/replies', 'UsersController@replies')->name('users.replies');
Route::get('/users/{id}/topics', 'UsersController@topics')->name('users.topics');
Route::get('/users/{id}/votes', 'UsersController@votes')->name('users.votes');
Route::get('/users/{id}/following', 'UsersController@following')->name('users.following');
Route::get('/users/{id}/followers', 'UsersController@followers')->name('users.followers');
Route::get('/users/{id}/refresh_cache', 'UsersController@refreshCache')->name('users.refresh_cache');
Route::get('/users/{id}/access_tokens', 'UsersController@accessTokens')->name('users.access_tokens');
Route::get('/access_token/{token}/revoke', 'UsersController@revokeAccessToken')->name('users.access_tokens.revoke');
Route::get('/users/regenerate_login_token', 'UsersController@regenerateLoginToken')->name('users.regenerate_login_token');
Route::post('/users/follow/{id}', 'UsersController@doFollow')->name('users.doFollow');
Route::get('/users/{id}/edit_email_notify', 'UsersController@editEmailNotify')->name('users.edit_email_notify');
Route::post('/users/{id}/update_email_notify', 'UsersController@updateEmailNotify')->name('users.update_email_notify');
Route::get('/users/{id}/edit_social_binding', 'UsersController@editSocialBinding')->name('users.edit_social_binding');
//会员列表，新加入会员
Route::get('/users', 'UsersController@index')->name('users.index');
Route::get('/users/create', 'UsersController@create')->name('users.create');
Route::post('/users', 'UsersController@store')->name('users.store');
Route::get('/users/{id}', 'UsersController@show')->name('users.show');
Route::get('/users/{id}/edit', 'UsersController@edit')->name('users.edit');
Route::patch('/users/{id}', 'UsersController@update')->name('users.update');
Route::delete('/users/{id}', 'UsersController@destroy')->name('users.destroy');
Route::get('/users/{id}/edit_avatar', 'UsersController@editAvatar')->name('users.edit_avatar');
Route::patch('/users/{id}/update_avatar', 'UsersController@updateAvatar')->name('users.update_avatar');
//通知中心
Route::group(['middleware' => 'auth'], function () {
    Route::get('/notifications', 'NotificationsController@index')->name('notifications.index');
    Route::get('/notifications/count', 'NotificationsController@count')->name('notifications.count');
});
//邮箱认证账号
Route::get('/email-verification-required', 'UsersController@emailVerificationRequired')->name('email-verification-required');
//发送邮箱认证
Route::post('/users/send-verification-mail', 'UsersController@sendVerificationMail')->name('users.send-verification-mail');

# ------------------ Authentication ------------------------

Route::get('/login', 'Auth\AuthController@oauth')->name('login');
//显示小窗口需要登录后才能继续操作。
Route::get('/login-required', 'Auth\AuthController@loginRequired')->name('login-required');
//显示小窗口很抱歉, 当前用户没有权限继续操作. 有什么问题请联系管理员.
Route::get('/admin-required', 'Auth\AuthController@adminRequired')->name('admin-required');
//显示小窗口对不起，您的账号已被禁用！
Route::get('/user-banned', 'Auth\AuthController@userBanned')->name('user-banned');
//创造用户
Route::get('/signup', 'Auth\AuthController@create')->name('signup');
//创造用户post
Route::post('/signup', 'Auth\AuthController@store')->name('signup');
//登出
Route::get('/logout', 'Auth\AuthController@logout')->name('logout');
//第三方登录包括qq、weixin等
Route::get('/oauth', 'Auth\AuthController@getOauth');

Route::get('/auth/oauth', 'Auth\AuthController@oauth')->name('auth.oauth');
Route::get('/auth/{provider}/callback', 'Auth\AuthController@callback')->name('auth.callback');
Route::get('/verification/{token}', 'Auth\AuthController@getVerification')->name('verification');

# ------------------ Categories ------------------------
//节点1类2类
Route::get('categories/{id}', 'CategoriesController@show')->name('categories.show');

# ------------------ Site ------------------------
//交换友链，只接受 PHP、Laravel 相关话题的站点
Route::get('/sites', 'SitesController@index')->name('sites.index');

# ------------------ Replies ------------------------

Route::post('/replies', 'RepliesController@store')->name('replies.store');
//->middleware('verified_email');;创造话题不需要邮寄认证
Route::delete('replies/delete/{id}', 'RepliesController@destroy')->name('replies.destroy')->middleware('auth');

# ------------------ Topic ------------------------
//所有话题汇总
Route::get('/topics', 'TopicsController@index')->name('topics.index');
Route::get('/topics/create', 'TopicsController@create')->name('topics.create');
//->middleware('verified_email');创造话题不需要邮寄认证
Route::post('/topics', 'TopicsController@store')->name('topics.store');
//->middleware('verified_email');创造话题不需要邮寄认证

//展示每一个话题{id}
Route::get('/topics/{id}', 'TopicsController@show')->name('topics.show');
//修改每一个话题{id}
Route::get('/topics/{id}/edit', 'TopicsController@edit')->name('topics.edit');
//patch补丁每一个话题{id}
Route::patch('/topics/{id}', 'TopicsController@update')->name('topics.update');
//删除每一个话题{id}
Route::delete('/topics/{id}', 'TopicsController@destroy')->name('topics.destroy');
//话题附加，ps
Route::post('/topics/{id}/append', 'TopicsController@append')->name('topics.append');

# ------------------ Votes ------------------------
//点赞（话题和回复）,必须是登录后才能点赞
Route::group(['before' => 'auth'], function () {
    Route::post('/topics/{id}/upvote', 'TopicsController@upvote')->name('topics.upvote');
    Route::post('/topics/{id}/downvote', 'TopicsController@downvote')->name('topics.downvote');
    Route::post('/replies/{id}/vote', 'RepliesController@vote')->name('replies.vote');
});

# ------------------ Admin Route ------------------------
//必须是有权用户才能做以下4个操作推荐、置顶、删除、下沉
Route::group(['before' => 'manage_topics'], function () {
    Route::post('topics/recommend/{id}', 'TopicsController@recommend')->name('topics.recommend');
    Route::post('topics/pin/{id}', 'TopicsController@pin')->name('topics.pin');
    Route::delete('topics/delete/{id}', 'TopicsController@destroy')->name('topics.destroy');
    Route::post('topics/sink/{id}', 'TopicsController@sink')->name('topics.sink');
});

Route::group(['before' => 'manage_users'], function () {
    Route::post('users/blocking/{id}', 'UsersController@blocking')->name('users.blocking');
});

Route::post('/upload_image', 'TopicsController@uploadImage')->name('upload_image')->middleware('auth');

// Health Checking
Route::get('heartbeat', function () {
    return Category::first();
});

//github大众不需要 Route::get('/github-api-proxy/users/{username}', 'UsersController@githubApiProxy')->name('users.github-api-proxy');
// Route::get('/github-card', 'UsersController@githubCard')->name('users.github-card');

Route::group(['middleware' => ['auth', 'admin_auth']], function () {
    Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
});

# ------------------ Password reset stuff Auth2------------------------
Route::controllers([
            'auth'=>'Auth\Auth2Controller',
            'password'=>'Auth\PasswordController'
        ]);

