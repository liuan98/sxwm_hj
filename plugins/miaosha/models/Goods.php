<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\miaosha\models;


/**
 * @property MiaoshaGoods $miaoshaGoods
 */
class Goods extends \app\models\Goods
{
    public function getMiaoshaGoods()
    {
        return $this->hasOne(MiaoshaGoods::className(), ['goods_id' => 'id'])
            ->andWhere(['is_delete' => 0]);
    }
}
