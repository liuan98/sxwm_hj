<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/10
 * Time: 16:41
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\handlers;



use app\events\ShareMemberEvent;
use app\forms\common\share\CommonShareLevel;
use app\forms\common\template\tplmsg\RemoveIdentityTemplate;
use app\jobs\ChangeParentJob;
use app\models\User;

class ChangeShareMemberHandle extends HandlerBase
{
    public function register()
    {
        \Yii::$app->on(HandlerRegister::CHANGE_SHARE_MEMBER, function ($event) {
            /* @var ShareMemberEvent $event */
            \Yii::$app->queue->delay(0)->push(new ChangeParentJob([
                'mall' => $event->mall,
                'beforeParentId' => $event->beforeParentId,
                'parentId' => $event->parentId,
                'user_id' => $event->userId
            ]));
            try {
                $commonShareLevel = CommonShareLevel::getInstance($event->mall);
                // 改变前的分销商分销等级修改
                $commonShareLevel->userId = $event->beforeParentId;
                $commonShareLevel->levelShare(CommonShareLevel::CHILDREN_COUNT);
            } catch (\Exception $exception) {
                \Yii::error('分销等级修改出错：');
                \Yii::error($exception);
            }
            try {
                $commonShareLevel = CommonShareLevel::getInstance($event->mall);
                // 改变后的分销商分销等级修改
                $commonShareLevel->userId = $event->parentId;
                $commonShareLevel->levelShare(CommonShareLevel::CHILDREN_COUNT);
            } catch (\Exception $exception) {
                \Yii::error('分销等级修改出错：');
                \Yii::error($exception);
            }

            try {
                $time = date('Y-m-d H:i:s', time());
                $tplMsg = new RemoveIdentityTemplate([
                    'page' => 'pages/share/index/index',
                    'user' => $event->user,
                    'remark' => "分销商解除:" . ($event->remark ?? '你的分销商身份已被解除'),
                    'time' => $time
                ]);
                $tplMsg->send();
            } catch (\Exception $exception) {
                \Yii::error("发送解除分销商模板消息失败");
                \Yii::error($exception);
            }
            return true;
        });
    }
}
