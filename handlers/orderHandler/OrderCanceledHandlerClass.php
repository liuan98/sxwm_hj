<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/12
 * Time: 10:58
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\handlers\orderHandler;

class OrderCanceledHandlerClass extends BaseOrderCanceledHandler
{
    public function handle()
    {
        $this->user = $this->event->order->user;

        $this->cancel();
    }
}
