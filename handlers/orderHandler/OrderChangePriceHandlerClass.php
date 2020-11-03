<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/8/13
 * Time: 16:07
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\handlers\orderHandler;


class OrderChangePriceHandlerClass extends BaseOrderHandler
{
    public function handle()
    {
        \Yii::error('--改价事件触发--');
        $this->addShareOrder();
    }
}
