<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\forms\api\home_page;


use app\forms\common\coupon\CommonCouponList;
use app\models\Model;

class HomeCouponForm extends Model
{
    public function getCouponList()
    {
        $common = new CommonCouponList();
        $common->user = \Yii::$app->user->identity;
        $list = $common->getList();

        return $list;
    }
}
