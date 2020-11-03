<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/13
 * Time: 18:18
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\plugins\bargain\events;


use app\plugins\bargain\models\BargainGoods;
use yii\base\Event;

/**
 * @property BargainGoods $bargainGoods
 */
class BargainGoodsEvent extends Event
{
    public $bargainGoods;
}
