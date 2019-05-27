<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/7/30
 * Time: 下午6:14
 */

namespace App\Api\Controllers\User;


use App\Api\Controllers\BaseController;
use App\Models\NoticeNew;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class InfoController extends BaseController
{


    public function notice_news(Request $request)
    {

        try {
            $user = $this->parseToken();
            $user_id = $user->user_id;
            $type = $request->get('type', '');
            $title = $request->get('title', '');

            $obj = DB::table('notice_news')
                ->join('users', 'notice_news.user_id', '=', 'users.id');

            $where[] = ['notice_news.user_id', '=', $user_id];


            if ($type) {
                $where[] = ['notice_news.type', '=', $type];
            }

            if ($title) {
                $where[] = ['notice_news.title', 'like', '%' . $title . '%'];

            }
            $obj = $obj->where($where)
                ->whereIn('notice_news.user_id', $this->getSubIds($user_id))
                ->orderBy('notice_news.updated_at', 'desc');
            $this->t = $obj->count();
            $data = $this->page($obj)
                ->select(
                    'notice_news.id',
                    'users.name as user_name',
                    'notice_news.title', 'notice_news.desc',
                    'notice_news.type', 'notice_news.redirect_url',
                    'notice_news.created_at',
                    'notice_news.type_desc',
                    'notice_news.icon_url'
                )
                ->get();
            $this->status = 1;
            $this->message = '数据返回成功';
            return $this->format($data);
        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }
    }


    public function add_notice_news(Request $request)
    {

        try {
            $user = $this->parseToken();
            $data = $request->except(['token']);
            $check_data = [
                'title' => '标题',
                'type' => '类型',
                'redirect_url' => '链接'
            ];
            $check = $this->check_required($data, $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }

            $data['config_id'] = $user->config_id;
            $data['user_id'] = $user->user_id;

            NoticeNew::create($data);
            $this->status = 1;
            $this->message = '添加成功';
            return $this->format();

        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }
    }


    public function del_notice_news(Request $request)
    {

        try {
            $user = $this->parseToken();
            $id = $request->get('id');
            NoticeNew::where('id', $id)->delete();
            $this->status = 1;
            $this->message = '删除成功';
            return $this->format();

        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }
    }


    //头条精选
    public function toutiao_top(Request $request)
    {

        try {
            $user = $this->parseToken();
            $config_id = $user->config_id;

            //读取平台的
            $obj_data = DB::table('notice_news')
                ->where('config_id', $config_id)
                ->where('type', 'jx')
                ->take(3)
                ->get();
            if ($obj_data->isEmpty()) {

                $obj_data = DB::table('notice_news')
                    ->where('config_id', $config_id)
                    ->where('type', 'jx')
                    ->take(3)
                    ->get();
            }

            //
            if (!$obj_data->isEmpty()) {
                foreach ($obj_data as $k => $v) {
                    $data[] = [
                        'title' => $v->title,
                        'rd' => "1",
                        'dz' => '1',
                        'img_url' => $v->icon_url,
                        'url' => $v->redirect_url,
                    ];
                }
            } else {
                //默认的
                $data = [
                    [
                        'title' => '万亿商机强势来袭，你准备好了吗？',
                        'rd' => '3049',
                        'dz' => '456',
                        'img_url' => 'https://sf6-ttcdn-tos.pstatp.com/img/web.business.image/201808025d0d168e574fc3bb4638945b~640x0.image',
                        'url' => 'https://linkmarket.aliyun.com/index.html?source=ruidao_toutiao_pc',
                    ], [
                        'title' => '万亿商机强势来袭，你准备好了吗？',
                        'rd' => '3049',
                        'dz' => '456',
                        'img_url' => 'https://sf6-ttcdn-tos.pstatp.com/img/web.business.image/201808025d0d168e574fc3bb4638945b~640x0.image',
                        'url' => 'https://linkmarket.aliyun.com/index.html?source=ruidao_toutiao_pc',
                    ], [
                        'title' => '万亿商机强势来袭，你准备好了吗？',
                        'rd' => '3049',
                        'dz' => '456',
                        'img_url' => 'https://sf6-ttcdn-tos.pstatp.com/img/web.business.image/201808025d0d168e574fc3bb4638945b~640x0.image',
                        'url' => 'https://linkmarket.aliyun.com/index.html?source=ruidao_toutiao_pc',
                    ]
                ];
            }

            return json_encode([
                'status' => 1,
                'message' => '数据返回成功',
                'data' => $data
            ]);
        } catch (\Exception $exception) {
            return json_encode([
                'status' => 2,
                'message' => '短信验证码不匹配'
            ]);
        }
    }

    //头条链接
    public function toutiao(Request $request)
    {

        try {
            $user = $this->parseToken();
            return json_encode([
                'status' => 1,
                'message' => '数据返回成功',
                'data' => [
                    'url' => 'https://www.qq.com/?fromdefault'
                ]
            ]);
        } catch (\Exception $exception) {
            return json_encode([
                'status' => 2,
                'message' => $exception->getMessage()
            ]);
        }
    }


    public function notice_news_type()
    {
        try {
            $data = [
                [
                    'type' => 'news',
                    'type_desc' => '新闻',
                ], [
                    'type' => 'notice',
                    'type_desc' => '公告',
                ], [
                    'type' => 'jx',
                    'type_desc' => '头条精选',
                ]
            ];
            $this->status = 1;
            $this->message = '数据返回成功';
            return $this->format($data);


        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }
    }
}