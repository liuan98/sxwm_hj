<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/11/22
 * Time: 16:33
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\plugins\gift\handlers;


use app\handlers\orderHandler\OrderCreatedHandlerClass;

class OrderCreatedHandler extends OrderCreatedHandlerClass
{

    protected function setShareMoney()
    {
        return $this;
    }


}
