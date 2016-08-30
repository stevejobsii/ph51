<!DOCTYPE html>

<html lang="zh">
	<head>

		<meta charset="UTF-8">

		<title>
       中国最酷的社区
		</title>


		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
		<meta name="keywords" content="最酷的社区" />
		<meta name="description" content="最酷的社区" />
        <meta name="_token" content="{{ csrf_token() }}">
        <meta property="qc:admins" content="44573310677477476375" />
        <link rel="stylesheet" href="{{ cdn(elixir('assets/css/styles.css')) }}">

        <link rel="shortcut icon" href="{{ cdn('favicon.ico') }}"/>

        <script>
            Config = {
                'cdnDomain': '{{ get_cdn_domain() }}',
                'user_id': {{ $currentUser ? $currentUser->id : 0 }},
                'user_avatar': {!! $currentUser ? '"'.$currentUser->present()->gravatar() . '"' : '""' !!},
                'user_link': {!! $currentUser ? '"'. route('users.show', $currentUser->id) . '"' : '""' !!},
                'routes': {
                    'notificationsCount' : '{{ route('notifications.count') }}',
                    'upload_image' : '{{ route('upload_image') }}'
                },
                'token': '{{ csrf_token() }}',
                'environment': '{{ app()->environment() }}',
                'following_users': []
            };

			      var ShowCrxHint = '{{isset($show_crx_hint) ? $show_crx_hint : 'no'}}';
        </script>

	    @yield('styles')

	</head>
	<body id="body">

		<div id="wrap">
      
      <!-- 加载nav -->
			@include('layouts.partials.nav')

			<div class="container main-container">

				<!-- 激活邮件提示 -->
        @if(\Auth::check() && !\Auth::user()->verified && !Request::is('email-verification-required'))
				<div class="alert alert-warning">
		            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
		            邮箱未激活，请前往 {{ \Auth::user()->email }} 查收激活邮件，激活后才能完整地使用社区功能，如发帖和回帖。未收到邮件？请前往 <a href="{{ route('email-verification-required') }}">重发邮件</a> 。
		    </div>
				@endif

				@include('flash::message')

				@yield('content')

			</div>

<!-- 加载footer -->
@include('layouts.partials.footer')

		</div>


      <script src="{{ cdn(elixir('assets/js/scripts.js')) }}"></script>

	    @yield('scripts')

	</body>
</html>
