<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\diy\forms\common;


use app\forms\common\coupon\CommonCouponList;
use app\models\Model;
use app\models\User;

class DiyCouponForm extends Model
{
    public function getCoupons()
    {
        $common = new CommonCouponList();
        $common->user = \Yii::$app->user->identity;
        $common->page = 10;
        $res = $common->getList();

        return $res;
    }
}
