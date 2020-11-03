<?php

namespace app\plugins\booking\models;


class Goods extends \app\models\Goods
{
    public function getBookingGoods()
    {
        return $this->hasOne(BookingGoods::className(), ['goods_id' => 'id'])
            ->andWhere(['is_delete' => 0]);
    }
}
