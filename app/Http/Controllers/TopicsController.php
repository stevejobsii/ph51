<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Phphub\Core\CreatorListener;
use App\Models\Topic;
use App\Models\SiteStatus;
use App\Models\Link;
use App\Models\Notification;
use App\Models\Append;
use App\Models\Category;
use App\Models\Banner;
use App\Models\ActiveUser;
use App\Models\HotTopic;
use Phphub\Handler\Exception\ImageUploadException;
use Phphub\Markdown\Markdown;
use Illuminate\Http\Request;
use App\Http\Requests\StoreTopicRequest;
use Auth;
use Flash;
use Image;

class TopicsController extends Controller implements CreatorListener
{

    //除了index、show其他都要登陆才能查看
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show']]);
    }

    //route:'/topics'
    public function index(Request $request, Topic $topic)
    {
        //根据topics?filter=筛选查询主题内容
        $topics = $topic->getTopicsWithFilter($request->get('filter'), 40);
        //赞助sidebar
        $links  = Link::allFromCache();
        //友情社区sidebar
        $banners = Banner::allByPosition();
        //活跃用户sidebar
        $active_users = ActiveUser::fetchAll();
        //热话题sidebar
        $hot_topics = HotTopic::fetchAll();

        return view('topics.index', compact('topics', 'links', 'banners', 'active_users', 'hot_topics'));
    }

    //route:'/topics/create'
    public function create(Request $request)
    {
        $category = Category::find($request->input('category_id'));
        $categories = Category::all();

        return view('topics.create_edit', compact('categories', 'category'));
    }

    //route:
    public function store(StoreTopicRequest $request)
    {
        return app('Phphub\Creators\TopicCreator')->create($this, $request->except('_token'));
    }

    //route:'/topics/{id}'
    public function show($id, Topic $topic)
    {
        //获取这个话题，连同作者和最后回复者信息
        $topic = Topic::where('id', $id)->with('user', 'lastReplyUser')->first();

        if ($topic->user->is_banned == 'yes') {
            Flash::error('你访问的文章已被屏蔽');
            return redirect(route('topics.index'));
        }
        
        //随机推荐话题sidebar
        $randomExcellentTopics = $topic->getRandomExcellent();
        //获取前30回复
        $replies = $topic->getRepliesWithLimit(config('phphub.replies_perpage'));
        //分类下其他主题
        $categoryTopics = $topic->getSameCategoryTopics();
        //作者的其他话题
        $userTopics = $topic->byWhom($topic->user_id)->with('user')->recent()->limit(8)->get();
        //赞过的用户
        $votedUsers = $topic->votes()->orderBy('id', 'desc')->with('user')->get()->pluck('user');
        //用户看过＋1
        $topic->increment('view_count', 1);
        //赞助sidebar
        $banners  = Banner::allByPosition();

        return view('topics.show', compact(
                            'topic', 'replies', 'categoryTopics',
                            'category', 'banners', 'randomExcellentTopics',
                            'votedUsers', 'userTopics'));
    }

    //route:
    public function edit($id)
    {
        $topic = Topic::findOrFail($id);
        $this->authorize('update', $topic);
        $categories = Category::all();
        $category = $topic->category;

        $topic->body = $topic->body_original;

        return view('topics.create_edit', compact('topic', 'categories', 'category'));
    }

    //话题附加，ps
    public function append($id, Request $request)
    {
        $topic = Topic::findOrFail($id);
        $this->authorize('append', $topic);

        $markdown = new Markdown;
        $content = $markdown->convertMarkdownToHtml($request->input('content'));

        $append = Append::create(['topic_id' => $topic->id, 'content' => $content]);

        app('Phphub\Notification\Notifier')->newAppendNotify(Auth::user(), $topic, $append);

        return response([
                    'status'  => 200,
                    'message' => lang('Operation succeeded.'),
                    'append'  => $append
                ]);
    }

    //route:
    public function update($id, StoreTopicRequest $request)
    {
        $topic = Topic::findOrFail($id);
        $this->authorize('update', $topic);

        $data = $request->only('title', 'body', 'category_id');

        $markdown = new Markdown;
        $data['body_original'] = $data['body'];
        $data['body'] = $markdown->convertMarkdownToHtml($data['body']);
        $data['excerpt'] = Topic::makeExcerpt($data['body']);

        $topic->update($data);

        Flash::success(lang('Operation succeeded.'));
        return redirect(route('topics.show', $topic->id));
    }

    /**
     * ----------------------------------------
     * User Topic Vote function
     * ----------------------------------------
     */

    //route:话题点赞
    public function upvote($id)
    {
        $topic = Topic::find($id);
        app('Phphub\Vote\Voter')->topicUpVote($topic);

        return response(['status' => 200]);
    }

    //route:话题点差
    public function downvote($id)
    {
        $topic = Topic::find($id);
        app('Phphub\Vote\Voter')->topicDownVote($topic);

        return response(['status' => 200]);
    }

    /**
     * ----------------------------------------
     * Admin Topic Management
     * ----------------------------------------
     */
    //route:
    public function recommend($id)
    {
        $topic = Topic::findOrFail($id);
        $this->authorize('recommend', $topic);
        $topic->is_excellent = $topic->is_excellent == 'yes' ? 'no' : 'yes';
        $topic->save();
        Notification::notify('topic_mark_excellent', Auth::user(), $topic->user, $topic);

        return response(['status' => 200, 'message' => lang('Operation succeeded.')]);
    }

    //route:
    public function pin($id)
    {
        $topic = Topic::findOrFail($id);
        $this->authorize('pin', $topic);

        $topic->order = $topic->order > 0 ? 0 : 999;
        $topic->save();

        return response(['status' => 200, 'message' => lang('Operation succeeded.')]);
    }

    //route:
    public function sink($id)
    {
        $topic = Topic::findOrFail($id);
        $this->authorize('sink', $topic);

        $topic->order = $topic->order >= 0 ? -1 : 0;
        $topic->save();

        return response(['status' => 200, 'message' => lang('Operation succeeded.')]);
    }

    //route:
    public function destroy($id)
    {
        $topic = Topic::findOrFail($id);
        $this->authorize('delete', $topic);
        $topic->delete();
        Flash::success(lang('Operation succeeded.'));

        return redirect(route('topics.index'));
    }

    //route:
    public function uploadImage(Request $request)
    {
        if ($file = $request->file('file')) {
            try {
                $upload_status = app('Phphub\Handler\ImageUploadHandler')->uploadImage($file);
            } catch (ImageUploadException $exception) {
                return ['error' => $exception->getMessage()];
            }
            $data['filename'] = $upload_status['filename'];

            SiteStatus::newImage();
        } else {
            $data['error'] = 'Error while uploading file';
        }
        return $data;
    }

    /**
     * ----------------------------------------
     * CreatorListener Delegate
     * ----------------------------------------
     */
    //route:
    public function creatorFailed($errors)
    {
        return redirect('/');
    }

    //route:
    public function creatorSucceed($topic)
    {
        Flash::success(lang('Operation succeeded.'));
        return redirect(route('topics.show', array($topic->id)));
    }
}
