<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\events;


use app\models\OrderRefund;
use yii\base\Event;

/**
 * @property OrderRefund $order_refund
 */
class OrderRefundEvent extends Event
{
    public $order_refund;
    public $advance_refund;
}
