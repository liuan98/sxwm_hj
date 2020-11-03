<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%order_refund}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property int $user_id
 * @property int $order_id
 * @property int $order_detail_id 关联订单详情
 * @property string $order_no 退款单号
 * @property int $type 售后类型：1=退货退款，2=换货
 * @property string $refund_price 退款金额
 * @property string $reality_refund_price 商家实际退款金额
 * @property string $remark 用户退款备注、说明
 * @property string $pic_list 用户上传图片凭证
 * @property int $status 1.待商家处理 2.同意 3.拒绝
 * @property string $status_time 商家处理时间
 * @property string $merchant_remark 商家同意|拒绝备注、理由
 * @property int $is_send 用户是否发货 0.未发货1.已发货
 * @property string $send_time 发货时间
 * @property string $express 快递公司
 * @property string $express_no 快递单号
 * @property int $address_id 退换货地址ID
 * @property int $is_confirm 商家确认操作
 * @property string $confirm_time 确认时间
 * @property string $merchant_express 商家发货快递公司
 * @property string $merchant_express_no 商家发货快递单号
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $refund_time
 * @property string $merchant_express_content
 * @property int $is_delete
 * @property int $is_refund
 * @property Order $order
 * @property User $user
 * @property OrderDetail $detail
 * @property string $customer_name
 * @property string $merchant_customer_name
 * @property RefundAddress $refundAddress
 */
class OrderRefund extends ModelActiveRecord
{
    /**
     *  订单售后处理完成事件
     */
    const EVENT_REFUND = 'orderRefund';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_refund}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'order_detail_id', 'type', 'pic_list'
                , 'created_at', 'updated_at', 'deleted_at', 'order_id'], 'required'],
            [['mall_id', 'user_id', 'order_detail_id', 'type', 'status',
                'is_send', 'address_id', 'is_confirm', 'is_delete', 'order_id', 'mch_id', 'is_refund'], 'integer'],
            [['refund_price', 'reality_refund_price'], 'number'],
            [['pic_list', 'customer_name', 'merchant_customer_name'], 'string'],
            [['status_time', 'send_time', 'confirm_time', 'created_at', 'updated_at', 'deleted_at', 'refund_time'], 'safe'],
            [['order_no', 'remark', 'merchant_remark', 'express_no', 'merchant_express_no', 'merchant_express_content'], 'string', 'max' => 255],
            [['express', 'merchant_express', 'customer_name', 'merchant_customer_name'], 'string', 'max' => 65],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => 'Mall ID',
            'mch_id' => 'Mch ID',
            'user_id' => 'User ID',
            'order_id' => '订单id',
            'order_detail_id' => '关联订单详情',
            'order_no' => '退款单号',
            'type' => '售后类型：1=退货退款，2=换货',
            'refund_price' => '退款金额',
            'reality_refund_price' => '商家实际退款金额',
            'remark' => '用户退款备注、说明',
            'pic_list' => '用户上传图片凭证',
            'status' => '1.待商家处理 2.同意 3.拒绝',
            'status_time' => '商家处理时间',
            'merchant_remark' => '商家备注',
            'is_send' => '用户是否发货 0.未发货1.已发货',
            'send_time' => '发货时间',
            'express' => '快递公司',
            'customer_name' => '京东商家编码',
            'express_no' => '快递单号',
            'address_id' => '退换货地址ID',
            'is_confirm' => '商家确认操作',
            'confirm_time' => '确认时间',
            'merchant_customer_name' => '京东商家编码',
            'merchant_express' => '商家发货快递公司',
            'merchant_express_no' => '商家发货快递单号',
            'merchant_express_content' => '其它物流备注',
            'is_refund' => '是否已打款',
            'refund_time' => '打款时间',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getDetail()
    {
        return $this->hasOne(OrderDetail::className(), ['id' => 'order_detail_id']);
    }

    public function getRefundAddress()
    {
        return $this->hasOne(RefundAddress::className(), ['id' => 'address_id']);
    }

    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function statusText($orderRefund)
    {
        if (!$orderRefund) {
            throw new \Exception('orderRefund不能为空');
        }

        try {
            if ($orderRefund['status'] == 1) {
                $statusText = '待商家审核';
            } elseif ($orderRefund['status'] == 2 && $orderRefund['is_send'] == 0) {
                $statusText = '待买家发货';
            } elseif ($orderRefund['status'] == 3) {
                $statusText = '商家已拒绝';
            } elseif ($orderRefund['is_send'] == 1 && $orderRefund['is_confirm'] == 0) {
                $statusText = '已发货,待商家处理';
            } elseif ($orderRefund['is_confirm'] == 1 && $orderRefund['type'] == 1) {
                $statusText = $orderRefund['is_refund'] == 1 ? '已退款' : '待退款';
                try {
                    // 兼容 更新之前的订单 is_refund 是2 但是有可能没有退款
                    if ($orderRefund->is_refund == 2) {
                        /** @var PaymentOrder $paymentOrder */
                        $paymentOrder = PaymentOrder::find()->where(['order_no' => $orderRefund['order']['order_no']])->with('paymentOrderUnion')->one();
                        $paymentRefund = PaymentRefund::find()->where(['out_trade_no' => $paymentOrder->paymentOrderUnion->order_no])->one();
                        if (!$paymentRefund) {
                            $statusText = '待退款';
                        }
                    }
                } catch (\Exception $exception) {
                }
            } elseif ($orderRefund['is_confirm'] == 1 && $orderRefund['type'] == 2) {
                $statusText = '已换货';
            } else {
                $statusText = '订单状态未知';
            }
        } catch (\Exception $exception) {
            $statusText = '订单状态未知';
        }

        return $statusText;
    }

    //商家版状态
    public function statusText_business($orderRefund)
    {
        if (!$orderRefund) {
            throw new \Exception('orderRefund不能为空');
        }

        if ($orderRefund['status'] == 1 && $orderRefund['type'] == 1) {
            $statusText = '退货退款 待审核';
        } elseif ($orderRefund['status'] == 1 && $orderRefund['type'] == 2) {
            $statusText = '换货 待审核';
        } elseif ($orderRefund['status'] == 2 && $orderRefund['is_send'] == 0 && $orderRefund['type'] == 1) {
            $statusText = ' 退货退款 待买家发货';
        } elseif ($orderRefund['status'] == 2 && $orderRefund['is_send'] == 0 && $orderRefund['type'] == 2) {
            $statusText = ' 换货 待买家发货';
        } elseif ($orderRefund['status'] == 3 && $orderRefund['type'] == 1) {
            $statusText = '退货退款 已拒绝售后';
        } elseif ($orderRefund['status'] == 3 && $orderRefund['type'] == 2) {
            $statusText = '换货 已拒绝售后';
        } elseif ($orderRefund['is_send'] == 1 && $orderRefund['is_confirm'] == 0 && $orderRefund['type'] == 1) {
            $statusText = '退货退款 买家已发货';
        } elseif ($orderRefund['is_send'] == 1 && $orderRefund['is_confirm'] == 0 && $orderRefund['type'] == 2) {
            $statusText = '换货 买家已发货';
        } elseif ($orderRefund['is_confirm'] == 1 && $orderRefund['type'] == 1) {
            $statusText = $orderRefund['is_refund'] == 1 ? '退货退款 卖家已退款' : '退货退款 待卖家退款';
            try {
                // 兼容 更新之前的订单 is_refund 是2 但是有可能没有退款
                if ($orderRefund->is_refund == 2) {
                    /** @var PaymentOrder $paymentOrder */
                    $paymentOrder = PaymentOrder::find()->where(['order_no' => $orderRefund['order']['order_no']])->with('paymentOrderUnion')->one();
                    $paymentRefund = PaymentRefund::find()->where(['out_trade_no' => $paymentOrder->paymentOrderUnion->order_no])->one();
                    if (!$paymentRefund) {
                        $statusText = '退货退款 待卖家退款';
                    }
                }
            } catch (\Exception $exception) {
            }
        } elseif ($orderRefund['is_confirm'] == 1 && $orderRefund['type'] == 2) {
            $statusText = '换货 卖家已发货';
        } else {
            $statusText = '订单状态未知';
        }

        return $statusText;
    }

    /**
     * 兼容 更新之前的订单 is_refund 是2 但是有可能没有退款
     * 兼容 实际退款金额 之前的订单没有存实际退款金额，需查询退款订单
     * @param $item
     * @return array
     */
    public function checkAfterRefund($item)
    {
        $newItem = [];
        try {
            /** @var PaymentOrder $paymentOrder */
            $paymentOrder = PaymentOrder::find()->where(['order_no' => $item->order->order_no])->with('paymentOrderUnion')->one();
            /** @var PaymentRefund $paymentRefund */
            $paymentRefund = PaymentRefund::find()->where(['out_trade_no' => $paymentOrder->paymentOrderUnion->order_no])->one();
            if ($item->is_refund == 2) {
                if (!$paymentRefund) {
                    $newItem['is_refund'] = 0;
                } else {
                    $newItem['is_refund'] = 1;
                }
            }
            if ($item->reality_refund_price <= 0 && $paymentRefund) {
                $newItem['reality_refund_price'] = $paymentRefund->amount;
            }
        } catch (\Exception $exception) {
        }

        return $newItem;
    }

    public function getActionStatus($order)
    {
        $data['is_show_edit_address'] = 0;
        $data['is_show_apply'] = 0;
        $data['is_show_confirm'] = 0;
        $data['is_show_refund'] = 0;

        // 修改地址
        if (isset($order['order']['send_type']) && $order['order']['send_type'] != 2 && $order['is_confirm'] == 0) {
            $data['is_show_edit_address'] = 1;
        }
        // 申请售后
        if ($order['status'] == 1) {
            $data['is_show_apply'] = 1;
        }

        // 确认收货
        if ($order['status'] == 2 && $order['is_send'] == 1 && $order['is_confirm'] == 0) {
            $data['is_show_confirm'] = 1;
        }

        // 打款
        if ($order['type'] == 1 && $order['status'] == 2 && $order['is_send'] == 1 && $order['is_confirm'] == 1 && $order['is_refund'] == 0) {
            $data['is_show_refund'] = 1;
        }

        return $data;
    }
}
