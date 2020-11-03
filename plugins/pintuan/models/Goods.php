<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\pintuan\models;


/**
 * Class Goods
 * @package app\plugins\pintuan\models
 * @property PintuanGoodsGroups[] $groups
 * @property PintuanGoods $pintuanGoods
 * @property PintuanOrders[] $pintuanOrder
 */
class Goods extends \app\models\Goods
{
    public function getGroups()
    {
        return $this->hasMany(PintuanGoodsGroups::className(), ['goods_id' => 'id'])->andWhere(['is_delete' => 0]);
    }

    public function getPintuanGoods()
    {
        return $this->hasOne(PintuanGoods::className(), ['goods_id' => 'id']);
    }

    public function getPintuanOrder()
    {
        return $this->hasMany(PintuanOrders::className(), ['goods_id' => 'id']);
    }
}
