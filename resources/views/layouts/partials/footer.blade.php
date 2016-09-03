<footer class="footer">
      <div class="container">
        <div class="row footer-top">

          <div class="col-sm-5 col-lg-5">
          </div>

          <div class="col-sm-6 col-lg-6 col-lg-offset-1">

              <div class="row">
                <div class="col-sm-3">
                  <h4>赞助商</h4>
                  <ul class="list-unstyled">
                      @if(isset($banners['footer-sponsor']))
                          @foreach($banners['footer-sponsor'] as $banner)
                              <a href="{{ $banner->link }}" target="_blank"><img src="{{ $banner->image_url }}" class="popover-with-html footer-sponsor-link" width="98" data-placement="top" data-content="{{ $banner->title }}"></a>
                          @endforeach
                      @endif
                  </ul>
                </div>

                  <div class="col-sm-3">
                    <h4>{{ lang('Site Status') }}</h4>
                    <ul class="list-unstyled">
                        <li>{{ lang('Total User') }}: {{ $siteStat->user_count }} </li>
                        <li>{{ lang('Total Topic') }}: {{ $siteStat->topic_count }} </li>
                        <li>{{ lang('Total Reply') }}: {{ $siteStat->reply_count }} </li>
                    </ul>
                  </div>
                  <div class="col-sm-3">
                    <h4>其他信息</h4>
                    <ul class="list-unstyled">
                        <li><a href="/about">关于我们</a></li>
                        <li><a href="{{ route('hall_of_fames') }}"><i class="fa fa-star" aria-hidden="true"></i> {{ lang('Hall of Fame') }}</a></li>
                        <li class="popover-with-html" data-content="新手 QQ 群">Q 群：XXXXXXXXXXX</li>
                    </ul>
                  </div>
                  <div class="col-sm-3">
                      <h4>微信订阅号</h4>
                    <img class="image-border popover-with-html" data-content="扫码，或者搜索微信订阅号：XXXXX" src="" style="width:100px;height:100px;">
                  </div>

                </div>

          </div>
        </div>
        <br>
        <br>
      </div>
    </footer>
