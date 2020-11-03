<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\forms\mall\live;

use app\core\response\ApiCode;
use app\models\Model;
use GuzzleHttp\Client;

class LiveForm extends Model
{
    public $room_id;
    public $is_refresh = 0;
    public $page = 1;

    private $limit = 20;
    private $second = 3600;

    public function rules()
    {
        return [
            [['room_id', 'page', 'is_refresh'], 'integer'],
        ];
    }

    public function getList()
    {
        try {
            $accessToken = \Yii::$app->getWechat()->getAccessToken();
            if (!$accessToken) {
                throw new \Exception('微信配置有误');
            }
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }

        $cacheKey = 'liveKey' . \Yii::$app->mall->id . $this->page;
        $res = \Yii::$app->cache->get($cacheKey);
        if (!$res || $this->is_refresh) {
            // 接口每天上限调用500次
            $api = "http://api.weixin.qq.com/wxa/business/getliveinfo?access_token={$accessToken}";
            $res = $this->post($api, [
                'start' => $this->page * $this->limit - $this->limit,
                'limit' => $this->limit,
            ]);
            $res = json_decode($res->getBody()->getContents(), true);
        }


        if ($res['errcode'] == 0) {
            \Yii::$app->cache->set($cacheKey, $res, $this->second);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => "请求成功",
                'data' => [
                    'list' => $this->getNewList($res['room_info']),
                    'pageCount' => ceil($res['total'] / $this->limit),
                    'total' => $res['total']
                ]
            ];
        } else if ($res['errcode'] == 1) {
            \Yii::$app->cache->set($cacheKey, $res, $this->second);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $res['errmsg'],
                'data' => [
                    'list' => [],
                    'pageCount' => 0,
                    'total' => 0,
                    'errmsg' => $res['errmsg'],
                ]
            ];
        } else {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $res['errmsg'],
            ];
        }
    }

    private function getNewList($list)
    {
        $newList = [];
        foreach ($list as $item) {
            $item = $this->getApiData($item);
            $item['start_time'] = date('Y-m-d H:i:s', $item['start_time']);
            $item['end_time'] = date('Y-m-d H:i:s', $item['end_time']);
            $item['status_text'] = $this->getLiveStatusText($item['live_status']);
            $newList[] = $item;
        }

        return $newList;
    }

    private function getApiData($item)
    {

        $item['text_time'] = date('H:i', $item['start_time']);
        // 今日预告
        if ($item['live_status'] === 102 || date('Y-m-d', $item['start_time']) == date('Y-m-d', time())) {
            $item['text_time'] = '今天' . date('H:i', $item['start_time']) . '开播';
        }

        // 长预告
        if (date('Y-m-d', $item['start_time']) > date('Y-m-d', time())) {
            $item['text_time'] = date('m', $item['start_time']) . '-' . date('d', $item['start_time']) . ' ' . date('H:i', $item['start_time']) . '开播';
        }

        // 判断时间上是否已结束
        if ($item['end_time'] < time()) {
            $item['live_status'] = 103;
        }

        return $item;
    }

    private function getLiveStatusText($status)
    {
        // 101: 直播中, 102: 未开始, 103: 已结束, 104: 禁播, 105: 暂停中, 106: 异常
        switch ($status) {
            case 101:
                $statusText = '直播中';
                break;
            case 102:
                $statusText = '未开始';
                break;
            case 103:
                $statusText = '已结束';
                break;
            case 104:
                $statusText = '禁播';
                break;
            case 105:
                $statusText = '暂停中';
                break;
            case 106:
                $statusText = '异常';
                break;
            default:
                $statusText = '未知错误';
                break;
        }
        return $statusText;
    }

    public function getPlayBack()
    {
        try {
            $accessToken = \Yii::$app->getWechat()->getAccessToken();
            if (!$accessToken) {
                throw new \Exception('微信配置有误');
            }
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }

        $cacheKey = 'liveBackKey' . \Yii::$app->mall->id . $this->page . $this->room_id;
        $res = \Yii::$app->cache->get($cacheKey);
        if (!$res || $this->is_refresh) {
            // 接口每天上限调用500次 自己限制每3分钟重新请求一次
            $api = "http://api.weixin.qq.com/wxa/business/getliveinfo?access_token={$accessToken}";
            $res = $this->post($api, [
                'action' => 'get_replay',
                'room_id' => $this->room_id,
                'start' => $this->page * $this->limit - $this->limit,
                'limit' => $this->limit,
            ]);
            $res = json_decode($res->getBody()->getContents(), true);
        }

        if ($res['errcode'] == 0) {
            \Yii::$app->cache->set($cacheKey, $res, $this->second);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => "请求成功",
                'data' => [
                    'room_info' => $res['room_info'],
                    'list' => $res['live_replay'],
                    'pageCount' => ceil($res['total'] / $this->limit),
                    'total' => $res['total']
                ]
            ];
        } else if ($res['errcode'] == 1) {
            \Yii::$app->cache->set($cacheKey, $res, $this->second);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $res['errmsg'],
                'data' => [
                    'room_info' => [],
                    'list' => [],
                    'pageCount' => 0,
                    'total' => 0,
                ]
            ];
        } else {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $res['errmsg']
            ];
        }
    }

    // 清理quota限制 每月10次机会，请勿滥用
    public function clearQuota($appid)
    {
        try {
            $accessToken = \Yii::$app->getWechat()->getAccessToken();
            if (!$accessToken) {
                throw new \Exception('微信配置有误');
            }
            $api = "https://api.weixin.qq.com/cgi-bin/clear_quota?access_token={$accessToken}";
            $res = $this->post($api, [
                'appid' => $appid,
            ]);
            $res = json_decode($res->getBody()->getContents(), true);
            dd($res);
        } catch (\Exception $exception) {
            dd($exception);
        }
    }

    private function post($url, $body = array())
    {
        $response = $this->getClient()->post($url, [
            'body' => json_encode($body)
        ]);

        return $response;
    }

    private function getClient()
    {
        return new Client([
            'verify' => false,
            'Content-Type' => 'application/json; charset=UTF-8'
        ]);
    }
}