<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/15
 * Time: 16:02
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\plugins\check_in\forms\common\continue_type;


use app\plugins\check_in\forms\common\Common;
use app\plugins\check_in\forms\Model;

/**
 * @property Common $common;
 */
abstract class BaseState extends Model
{
    public $common;

    abstract public function setJob();

    abstract public function clearContinue();
}
