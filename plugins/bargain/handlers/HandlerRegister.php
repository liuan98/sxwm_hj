<?php
/**
 * @copyright (c)天幕网络
 * @author Lu Wei
 * @link http://www.67930603.top/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/23 16:32
 */


namespace app\plugins\bargain\handlers;


use yii\base\BaseObject;

class HandlerRegister extends BaseObject
{
    const BARGAIN_TIMER = 'bargain_timer';
    const BARGAIN_USER_JOIN = 'bargain_user_join';
    public function getHandlers()
    {
        return [
            GoodsTimeHandler::class,
            BargainUserJoinHandler::class,
            OrderCreatedHandler::class,
        ];
    }
}
