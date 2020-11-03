<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/21
 * Time: 15:44
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\plugins\fxhb\events;


use app\models\Mall;
use app\plugins\fxhb\models\FxhbUserActivity;
use yii\base\Event;

/**
 * @property FxhbUserActivity $userActivity
 * @property FxhbUserActivity $parentActivity
 * @property Mall $mall
 */
class JoinActivityEvent extends Event
{
    public $userActivity;
    public $parentActivity;
    public $mall;
}
