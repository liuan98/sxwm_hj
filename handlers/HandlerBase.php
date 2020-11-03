<?php
/**
 * @copyright (c)天幕网络
 * @author Lu Wei
 * @link http://www.67930603.top/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/23 16:32
 */


namespace app\handlers;


use yii\base\BaseObject;

abstract class HandlerBase extends BaseObject
{
    /**
     * 事件处理注册
     */
    abstract public function register();
}
