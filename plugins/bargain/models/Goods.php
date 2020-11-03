<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/15
 * Time: 14:19
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\plugins\bargain\models;

/**
 * Class Goods
 * @package app\plugins\bargain\models
 * @property BargainGoods $bargainGoods
 */
class Goods extends \app\models\Goods
{
    public function getBargainGoods()
    {
        return $this->hasOne(BargainGoods::className(), ['goods_id' => 'id']);
    }
}
