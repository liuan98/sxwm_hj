<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/13
 * Time: 18:07
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\plugins\bargain\handlers;


use app\handlers\HandlerBase;
use app\plugins\bargain\events\BargainGoodsEvent;
use app\plugins\bargain\jobs\BargainGoodsTimeJob;

class GoodsTimeHandler extends HandlerBase
{
    public function register()
    {
        \Yii::$app->on(HandlerRegister::BARGAIN_TIMER, function ($event) {
            /* @var BargainGoodsEvent $event */
            if ($event->bargainGoods->goods->status == 1) {
                $time = strtotime($event->bargainGoods->end_time) - time();
                $time = $time < 0 ? 0 : $time;
                \Yii::$app->queue->delay($time)->push(new BargainGoodsTimeJob([
                    'bargainGoods' => $event->bargainGoods
                ]));
            } else {
//                $time = time() - strtotime($event->bargainGoods->begin_time);
//                $time = $time > 0 ? 0 : $time;
            }
        });
    }
}
