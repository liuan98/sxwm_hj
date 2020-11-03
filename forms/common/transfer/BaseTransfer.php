<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/19
 * Time: 11:14
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\forms\common\transfer;


use app\models\Model;
use app\models\PaymentTransfer;
use app\models\User;

abstract class BaseTransfer extends Model
{
    /**
     * @param PaymentTransfer $paymentTransfer
     * @param User $user
     * @return mixed
     */
    abstract public function transfer($paymentTransfer, $user);
}
