<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: zbj
 */

namespace app\plugins\advance\models;


class Order extends \app\models\Order
{
    public function getAdvanceOrder()
    {
        return $this->hasOne(AdvanceOrder::className(), ['order_id' => 'id']);
    }
}
