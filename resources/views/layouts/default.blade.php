<!DOCTYPE html>

<html lang="zh">
	<head>

		<meta charset="UTF-8">

		<title>
       社区
		</title>


		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
		<meta name="keywords" content="社区" />
		<meta name="description" content="社区" />
        <meta name="_token" content="{{ csrf_token() }}">
        <meta property="qc:admins" content="44573310677477476375" />
        <link rel="stylesheet" href="{{ cdn(elixir('assets/css/styles.css')) }}">

        <link rel="shortcut icon" href="{{ cdn('favicon.ico') }}"/>

	    @yield('styles')

	</head>
	<body id="body">

		<div id="wrap">
      
            <!-- 加载nav -->
			@include('layouts.partials.nav')

			<div class="container main-container">

				@include('flash::message')
                
                <!-- 加载主内容 -->
				@yield('content')

			</div>

<!-- 加载footer -->
@include('layouts.partials.footer')

		</div>


      <script src="{{ cdn(elixir('assets/js/scripts.js')) }}"></script>

	    @yield('scripts')

	</body>
</html>
