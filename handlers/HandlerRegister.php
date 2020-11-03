<?php
/**
 * @copyright (c)天幕网络
 * @author Lu Wei
 * @link http://www.67930603.top/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/23 16:32
 */


namespace app\handlers;

use yii\base\BaseObject;

class HandlerRegister extends BaseObject
{
    const BECOME_SHARE = 'become_share';
    const CHANGE_SHARE_MEMBER = 'change_share_member'; // 用户变更上级事件

    public function getHandlers()
    {
        return [
            OrderCreatedHandler::class,
            OrderCanceledHandler::class,
            OrderPayedHandler::class,
            OrderSentHandler::class,
            OrderConfirmedHandler::class,
            OrderSalesHandler::class,
            OrderRefundConfirmedHandler::class,
            MyHandler::class,
            BecomeShareHandle::class,
            AppMessageTestHandler::class,
            AppBuyMessageHandler::class,
            GoodsEditHandler::class,
            GoodsDestroyHandler::class,
            ChangeShareMemberHandle::class,
            OrderChangePriceHandler::class,
        ];
    }
}
