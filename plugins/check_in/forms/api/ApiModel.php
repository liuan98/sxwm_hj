<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/27
 * Time: 9:55
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\plugins\check_in\forms\api;


use app\models\User;
use app\plugins\check_in\forms\Model;

/**
 * @property User $user
 */
class ApiModel extends Model
{
    protected $user;

    public function setUser($val)
    {
        $this->user = $val;
    }
}
