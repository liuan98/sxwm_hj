<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\forms\mall\order;


use app\core\response\ApiCode;
use app\models\Model;
use app\models\OrderRefund;

class OrderRefundForm extends Model
{
    public $refund_order_id;

    public function rules()
    {
        return [
            [['refund_order_id'], 'integer']
        ];
    }

    public function shouHuo()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            /** @var OrderRefund $orderRefund */
            $orderRefund = OrderRefund::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'status' => 2,
                'type' => 1,
                'is_send' => 1,
                'id' => $this->refund_order_id
            ])->one();

            if (!$orderRefund) {
                throw new \Exception('售后订单不存在');
            }

            $orderRefund->is_confirm = 1;
            $orderRefund->confirm_time = mysql_timestamp();
            $res = $orderRefund->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($orderRefund));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '确认收货成功'
            ];

        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}