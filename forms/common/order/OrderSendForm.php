<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\forms\common\order;


use app\core\response\ApiCode;
use app\events\OrderSendEvent;
use app\forms\common\CommonDelivery;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderDetailExpress;
use app\models\OrderDetailExpressRelation;
use app\models\OrderExpressSingle;

class OrderSendForm extends Model
{
    public $order_id;
    public $is_express;
    public $express;
    public $express_no;
    public $merchant_remark;
    public $mch_id;
    public $customer_name;//京东物流特殊要求字段，商家编码
    public $order_detail_id;// 订单物流分开发送
    public $express_content;
    public $express_single_id;// 电子面单ID

    //同城配送
    public $man;
    public $city_mobile;

    public $express_id;


    public function rules()
    {
        return [
            [['order_id', 'is_express'], 'required'],
            [['order_id', 'is_express', 'mch_id', 'express_id'], 'integer'],
            [['merchant_remark', 'express_no', 'express', 'customer_name', 'city_mobile',
                'express_content', 'man'], 'string'],
            [['merchant_remark', 'express', 'express_no', 'customer_name'], 'default', 'value' => ''],
            [['order_detail_id', 'express_single_id'], 'safe']
        ];
    }

    //发货
    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        // TODO 兼容小程序端 小程序端需优化
        if ($this->express_single_id == 'undefined') {
            $this->express_single_id = 0;
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $this->checkData();
            if (substr_count($this->express, '京东') && empty($this->customer_name)) {
                throw new \Exception('京东物流必须填写京东商家编码');
            }
            $order = Order::findOne([
                'id' => $this->order_id,
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => $this->mch_id ?: 0,
                'is_confirm' => 0,
                'is_sale' => 0
            ]);

            if (!$order) {
                throw new \Exception('订单不存在');
            }

            if ($order->status == 0) {
                throw new \Exception('订单进行中,不能进行操作');
            }

            if ($order->is_pay == 0 && $order->pay_type != 2) {
                throw new \Exception('订单未支付');
            }

            if ($order->cancel_status == 2) {
                throw new \Exception('该订单正在申请取消操作，请先处理');
            }

            if ($this->is_express == 1) {
                switch ($this->is_express) {
                    case 1:
                        if (!($this->express && $this->express_no)) {
                            throw new \Exception('快递填写不全');
                        }
                        (new Order())->validateExpress($this->express);
                        break;
                }
            }

            // 同城配送
            if ($order->send_type == 2) {
                // 从字符串中截取配送员id
                $id = substr($this->man, 1, strpos($this->man, ')') - 1);
                $deliveryman = CommonDelivery::getInstance()->getManOne($id);
                if (!$deliveryman) {
                    throw new \Exception('所选配送员不存在');
                }
                $order->city_info = json_encode([
                    'id' => $deliveryman->id,
                    'name' => $deliveryman->name,
                    'mobile' => $deliveryman->mobile,
                ], JSON_UNESCAPED_UNICODE);
                $order->city_name = $deliveryman->name;
                $order->city_mobile = $deliveryman->mobile;
            }
            $order->words = $this->merchant_remark;
            // 到店自提订单 选择发货后不能再进行核销
            if ($order->send_type == 1) {
                $order->send_type = 0;
            }

            // 同城配送订单直接触发事件
            if ($order->send_type == 2) {
                $order->is_send = 1;
                $order->send_time = date('Y-m-d H:i:s');

                //触发
                try {
                    \Yii::$app->trigger(Order::EVENT_SENT, new OrderSendEvent(['order' => $order]));
                } catch (\Exception $exception) {
                    \Yii::error($exception);
                }
            } else {
                //物流发货 需全部商品都已发货 is_send 才改为1
                $this->saveOrderDetailExpress();
            }

            if (!$order->save()) {
                throw new \Exception($this->getErrorMsg($order));
            }
            $transaction->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '发货成功',
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    /**
     * @throws \Exception
     */
    public function saveOrderDetailExpress()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if (!is_array($this->order_detail_id)) {
            throw new \Exception('order_detail_id参数必须为数组');
        }

        if (count($this->order_detail_id) <= 0) {
            throw new \Exception('请勾选要发货的商品');
        }

        // 发货物流|编辑发货物流
        if ($this->express_id) {
            $orderDetailExpress = OrderDetailExpress::find()->where(['mall_id' => \Yii::$app->mall->id, 'id' => $this->express_id])->one();
            if (!$orderDetailExpress) {
                throw new \Exception('订单物流不存在');
            }
        } else {
            $orderDetailExpress = new OrderDetailExpress();
            $orderDetailExpress->mall_id = \Yii::$app->mall->id;
            $orderDetailExpress->mch_id = \Yii::$app->user->identity->mch_id;
            $orderDetailExpress->order_id = $this->order_id;
        }

        if ($this->is_express == 1) {
            $orderDetailExpress->express = $this->express;
            $orderDetailExpress->express_no = $this->express_no;
            $orderDetailExpress->send_type = 1;
            $orderDetailExpress->merchant_remark = $this->merchant_remark;
            $orderDetailExpress->customer_name = $this->customer_name;
            // 物流单号对应电子面单ID
            $expressSingle = OrderExpressSingle::findOne($this->express_single_id);
            if ($expressSingle) {
                $expressSingleOrder = \Yii::$app->serializer->decode($expressSingle->order);
                if ($expressSingleOrder->LogisticCode == $this->express_no) {
                    $orderDetailExpress->express_single_id = $this->express_single_id;
                }
            }
        } else {
            if (!$this->express_content) {
                throw new \Exception('请填写物流内容');
            }
            $orderDetailExpress->send_type = 2;
            $orderDetailExpress->express_content = $this->express_content;
        }

        $res = $orderDetailExpress->save();
        if (!$res) {
            throw new \Exception($this->getErrorMsg($orderDetailExpress));
        }

        foreach ($this->order_detail_id as $detailId) {
            $relation = OrderDetailExpressRelation::find()->where(['is_delete' => 0, 'order_detail_id' => $detailId])->one();
            if (!$relation) {
                $model = new OrderDetailExpressRelation();
                $model->mall_id = \Yii::$app->mall->id;
                $model->mch_id = \Yii::$app->user->identity->mch_id;
                $model->order_id = $this->order_id;
                $model->order_detail_id = $detailId;
                $model->order_detail_express_id = $orderDetailExpress->id;
                $res = $model->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($model));
                }
            }
        }
        /** @var Order $order */
        $order = Order::find()->where(['mall_id' => \Yii::$app->mall->id, 'id' => $this->order_id])
            ->with('detail', 'detailExpressRelation')
            ->one();

        // 所有商品已发货，发货状态更新
        if (count($order->detail) == count($order->detailExpressRelation)  && $order->is_send == 0) {
            $order->is_send = 1;
            $order->send_time = mysql_timestamp();
            $res = $order->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($order));
            }

            //触发
            try {
                \Yii::$app->trigger(Order::EVENT_SENT, new OrderSendEvent(['order' => $order]));
            } catch (\Exception $exception) {
                \Yii::error($exception);
            }
        }

    }

    private function checkData()
    {
        if (!$this->order_detail_id) {
            try {
                /** @var Order $order */
                $order = Order::find()->where([
                    'id' => \Yii::$app->request->post('order_id')
                ])->with('detail', 'detailExpressRelation')->one();

                if (!$order) {
                    throw new \Exception('订单不存在');
                }
                $orderDetailId = [];
                /** @var OrderDetailExpressRelation $item */
                foreach ($order->detailExpressRelation as $item) {
                    $orderDetailId[] = $item->order_detail_id;
                }
                $sendOrderDetailId = [];
                /** @var OrderDetail $item */
                foreach ($order->detail as $item) {
                    if (!in_array($item->id, $orderDetailId)) {
                        $sendOrderDetailId[] = $item->id;
                    }
                }
                $this->order_detail_id = $sendOrderDetailId;
                $this->express_content = '手机端无需物流发货';
            } catch (\Exception $exception) {
                throw new \Exception($exception->getMessage());
            }
        }
    }
}
