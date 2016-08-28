@extends('layouts.default')

@section('content')

<div class="box text-center site-intro rm-link-color">
  <!-- 社区信息QQ群号等 -->
  {!! lang('site_intro') !!}
</div>
  
<!-- 头牌广告 -->
@include('layouts.partials.topbanner')

<div class="panel panel-default list-panel home-topic-list">
  <!-- 社区精华帖几个大字 -->
  <div class="panel-heading">
    <h3 class="panel-title text-center">
      {{ lang('Excellent Topics') }} &nbsp;
      <a href="{{ route('feed') }}" style="color: #E5974E; font-size: 14px;" target="_blank">
         <i class="fa fa-rss"></i>
      </a>
    </h3>
  </div>

  <!-- 社区精华帖 -->
  <div class="panel-body ">
	@include('pages.partials.topics')
  </div>

  <!-- 查看更多精华帖 -->
  <div class="panel-footer text-right">
  	<a href="topics?filter=excellent" class="more-excellent-topic-link">
  		{{ lang('More Excellent Topics') }} <i class="fa fa-arrow-right" aria-hidden="true"></i>
  	</a>
  </div>
</div>

@stop
