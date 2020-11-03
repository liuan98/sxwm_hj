<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\scan_code_pay\handlers;

use app\handlers\orderHandler\BaseOrderCanceledHandler;

class OrderCancelEventHandler extends BaseOrderCanceledHandler
{
    protected function action()
    {
        $this->integralResume()->couponResume()->refund()->cardResume()->shareResume()->updateGoodsInfo();
    }
}