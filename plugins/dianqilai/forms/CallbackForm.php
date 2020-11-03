<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/3
 * Time: 16:22
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\plugins\dianqilai\forms;


use app\core\response\ApiCode;
use app\models\Mall;
use app\models\Model;
use app\models\User;
use app\plugins\dianqilai\forms\common\CommonSetting;
use app\plugins\dianqilai\forms\common\CommonTemplate;

class CallbackForm extends Model
{
    public $token;

    public $visiter_id;
    public $content;
    public $timestamp;
    public $avatar;
    public $service_url;
    public $service_name;
    public $state;

    public function rules()
    {
        return [
            [['token', 'visiter_id'], 'required'],
            [['token', 'visiter_id', 'content', 'timestamp', 'avatar', 'service_url', 'service_name',
                'state'], 'string'],
            [['token', 'visiter_id', 'content', 'timestamp', 'avatar', 'service_url', 'service_name', 'state'], 'trim'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            if ($this->state != 'offline') {
                throw new \Exception('用户在线无需发送');
            }
            $array = json_decode(base64_decode($this->token), true);
            if (!isset($array['token'])) {
                throw new \Exception('错误链接x01');
            }
            if (!isset($array['mall_id'])) {
                throw new \Exception('错误链接x02');
            }
            $mall = Mall::findOne(['id' => $array['mall_id']]);
            $token = CommonSetting::getCommon($mall)->getToken();
            if ($token !== $array['token']) {
                throw new \Exception('无效的链接x03');
            }
            $user = User::findOne(['mall_id' => $mall->id, 'access_token' => $this->visiter_id]);
            if (!$user) {
                throw new \Exception('无效的用户');
            }
            $template = new CommonTemplate();
            $template->user = $user;
            $template->page = 'pages/web/web?url=' . $this->service_url;
            $template->contactTpl($this->service_name, $this->timestamp, $this->content);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '发送成功'
            ];
        } catch (\Exception $exception) {
            \Yii::error($exception);
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'data' => $exception
            ];
        }
    }
}
