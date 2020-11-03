<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/9
 * Time: 17:07
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\plugins\bargain\controllers;



use app\plugins\bargain\Plugin;

class Controller extends \app\plugins\Controller
{
    public $sign;

    public function init()
    {
        parent::init();
        $this->sign = (new Plugin())->getName();
    }
}
