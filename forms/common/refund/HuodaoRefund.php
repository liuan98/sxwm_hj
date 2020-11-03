<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/18
 * Time: 15:54
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\forms\common\refund;


use app\core\payment\PaymentException;

class HuodaoRefund extends BaseRefund
{
    /**
     * @param \app\models\PaymentRefund $paymentRefund
     * @param \app\models\PaymentOrderUnion $paymentOrderUnion
     * @return bool|mixed
     * @throws PaymentException
     */
    public function refund($paymentRefund, $paymentOrderUnion)
    {
        $paymentRefund->is_pay = 1;
        $paymentRefund->pay_type = 2;
        if (!$paymentRefund->save()) {
            throw new PaymentException($this->getErrorMsg($paymentRefund));
        }
        return true;
    }
}
