<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/26
 * Time: 17:06
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\plugins\check_in\forms;


use app\models\Mall;

/**
 * @property Mall $mall
 */
class Model extends \app\models\Model
{
    protected $mall;

    public function setMall($val)
    {
        $this->mall = $val;
    }
}
