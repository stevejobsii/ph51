<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Topic;
use App\Models\Banner;
use App\Models\Role;
use Illuminate\Http\Request;
use Rss;
use Purifier;
use Phphub\Handler\EmailHandler;
use Jrean\UserVerification\Facades\UserVerification;

class PagesController extends Controller
{
    /**
     * The home page主页
     */
    public function home(Topic $topic)
    {
        //筛选精华主题
        $topics = $topic->getTopicsWithFilter('excellent');
        //赞助
        $banners = Banner::allByPosition();
        return $banners;
        return view('pages.home', compact('topics', 'banners'));
    }

    /**
     * About us page关于这个社区
     */
    public function about()
    {
        return view('pages.about');
    }

    /**
     * Search page, using google's.用BING来搜索论坛内容
     */
    public function search(Request $request)
    {
        $query = Purifier::clean($request->input('q'), 'search_q');
        return redirect()->away('https://www.bing.com/search?q=site:goodgoto.com ' . $query, 301);
    }

    /**
     * Feed function  网站动态
     */
    public function feed()
    {
        $topics = Topic::excellent()->recent()->limit(20)->get();

        $channel =[
            'title'       => 'PHPHub - PHP & Laravel的中文社区',
            'description' => 'PHPHub是 PHP 和 Laravel 的中文社区，在这里我们讨论技术, 分享技术。',
            'link'        => url(route('feed')),
        ];

        $feed = Rss::feed('2.0', 'UTF-8');

        $feed->channel($channel);

        foreach ($topics as $topic) {
            $feed->item([
                'title'             => $topic->title,
                'description|cdata' => str_limit($topic->body, 200),
                'link'              => url(route('topics.show', $topic->id)),
                'pubDate'           => date('Y-m-d', strtotime($topic->created_at)),
                ]);
        }

        return response($feed, 200, array('Content-Type' => 'text/xml'));
    }

    public function sitemap()//网站动态
    {
        return app('Phphub\Sitemap\Builder')->render();
    }

    public function hallOfFames()//社区名人堂
    {
        $users = User::hallOfFamesUsers();
        return view('pages.hall_of_fame', compact('users'));
    }
}
