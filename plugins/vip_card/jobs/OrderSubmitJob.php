<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/12/2
 * Time: 13:36
 */

namespace app\plugins\vip_card\jobs;

use app\core\payment\PaymentOrder;
use app\forms\api\order\OrderPayNotify;
use app\models\Mall;
use app\models\Model;
use app\models\OrderDetail;
use app\models\OrderSubmitResult;
use app\models\User;
use app\plugins\vip_card\forms\api\IndexForm;
use app\plugins\vip_card\models\VipCardOrder;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;
use app\models\Order;
use app\plugins\vip_card\models\VipCardDetail;
use app\plugins\vip_card\forms\common\CommonVipCardSetting;
use app\events\OrderEvent;
use app\jobs\OrderCancelJob;


class OrderSubmitJob extends BaseObject implements JobInterface
{

    /** @var Mall $mall */
    public $mall;

    /** @var User $user */
    public $user;

    public $token;
    public $id;

    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     */
    public function execute($queue)
    {
        \Yii::$app->user->setIdentity($this->user);
        \Yii::$app->setMall($this->mall);
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $data = $this->getVipCardDetailInfo();
            if ($data['status'] == 1) {
                throw new \Exception('该会员卡已停售');
            }
            if ($data['num'] <= 0) {
                throw new \Exception('库存不足');
            }
            $vipCardSetting = (new CommonVipCardSetting())->getSetting();

            $order = new Order();
            $order->mall_id = \Yii::$app->mall->id;
            $order->user_id = \Yii::$app->user->id;
            $order->order_no = date('YmdHis') . rand(100000, 999999);
            $order->total_price = $data['price'];
            $order->total_pay_price = $data['price'];
            $order->express_original_price = 0;
            $order->express_price = 0;
            $order->total_goods_price = $data['price'];
            $order->total_goods_original_price = $data['price'];

            $order->member_discount_price = 0;
            $order->use_user_coupon_id = 0;
            $order->coupon_discount_price = 0;
            $order->use_integral_num = 0;
            $order->integral_deduction_price = 0;
            $order->remark = '';
            $order->order_form = \Yii::$app->serializer->encode([]);
            $order->words = '';

            $order->is_pay = 0;
            $order->pay_type = 0;
            $order->is_send = 0;
            $order->is_confirm = 0;
            $order->is_sale = 0;
            $order->support_pay_types = \Yii::$app->serializer->encode($vipCardSetting['payment_type']);

            $order->sign = 'vip_card';
            $order->token = $this->token;
            $order->status = 1;
            if (!$order->save()) {
                throw new \Exception($this->getErrorMsg($order));
            }

            $this->saveOrderDetail($order, $data);
            $this->saveVipOrder($order, $data);

            $event = new OrderEvent();
            $event->order = $order;
            $event->sender = $this;
            \Yii::$app->trigger(Order::EVENT_CREATED, $event);

            // 5分钟后取消订单
            $queueId = \Yii::$app->queue->delay(5 * 60)->push(new OrderCancelJob([
                'orderId' => $order->id
            ]));

            $transaction->commit();
        } catch (\Exception $e) {
            \Yii::error($e);
            $transaction->rollBack();
            $orderSubmitResult = new OrderSubmitResult();
            $orderSubmitResult->token = $this->token;
            $orderSubmitResult->data = $e->getMessage();
            $orderSubmitResult->save();
        }
    }

    private function getVipCardDetailInfo()
    {
        $data = VipCardDetail::find()
            ->where(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id])
            ->with('vipCards')
            ->with('vipCoupons')
            ->with('main')
            ->asArray()
            ->one();
        if (!$data) {
            throw new \Exception('该会员卡不存在');
        }

        return $data;
    }


    /**
     * @param $order
     * @param $data
     * @return bool
     * @throws \Exception
     */
    private function saveOrderDetail($order, $data)
    {
        $goods = (new IndexForm())->goods();
        $orderDetail = new OrderDetail();
        $orderDetail->order_id = $order->id;
        $orderDetail->goods_id = $goods->id;
        $orderDetail->num = 1;
        $orderDetail->unit_price = $data['price'];
        $orderDetail->total_original_price = $data['price'];
        $orderDetail->total_price = $data['price'];
        $orderDetail->member_discount_price = 0;
        $orderDetail->sign = 'vip_card';

        $attrGroups = \Yii::$app->serializer->decode($goods->attr_groups);
        $attrList = [];
        foreach ($attrGroups as $attrGroup) {
            $arr['attr_group_id'] = $attrGroup['attr_group_id'];
            $arr['attr_group_name'] = $attrGroup['attr_group_name'];
            $arr['attr_id'] = $attrGroup['attr_list'][0]['attr_id'];
            $arr['attr_name'] = $attrGroup['attr_list'][0]['attr_name'];
            $attrList[] = $arr;
        }

        $shareData = $this->getShareMoney($orderDetail);

        $goodsInfo = [
            'attr_list' => $attrList,
            'goods_attr' => [
                'id' => $goods->attr[0]['id'],
                'goods_id' => $goods->id,
                'sign_id' => $goods->attr[0]['sign_id'],
                'stock' => $goods->attr[0]['stock'],
                'price' => $data['price'],
                'original_price' => $data['price'],
                'no' => $goods->attr[0]['no'],
                'weight' => $goods->attr[0]['weight'],
                'pic_url' => $data['main']['cover'],
                'share_commission_first' => $shareData['first'],
                'share_commission_second' => $shareData['second'],
                'share_commission_third' => $shareData['third'],
                'member_price' => 0,
                'integral_price' => 0,
                'use_integral' => 0,
                'discount' => [],// TODO 折扣为什么是数组
                'extra' => [],
                'goods_warehouse_id' => $goods->goods_warehouse_id,
                'name' => $data['main']['name'],
                'cover_pic' => $data['main']['cover'],
            ],
            'rules_data' => $data
        ];
        $orderDetail->goods_info = $orderDetail->encodeGoodsInfo($goodsInfo);

        if (!$orderDetail->save()) {
            throw new \Exception((new Model())->getErrorMsg($orderDetail));
        }

        return true;
    }

    /**
     * @param OrderDetail $orderDetail
     * @return array
     */
    private function getShareMoney($orderDetail)
    {
        $first = 0;
        $second = 0;
        $third = 0;

        $vipCardSetting = (new CommonVipCardSetting())->getSetting();
        if ($vipCardSetting['is_share']) {
            $firstValue = $vipCardSetting['share_commission_first'];
            if (!empty($firstValue) && is_numeric($firstValue)) {
                $first = $firstValue;
            }

            $secondValue = $vipCardSetting['share_commission_second'];
            if (!empty($secondValue) && is_numeric($secondValue)) {
                $second = $secondValue;
            }

            $thirdValue = $vipCardSetting['share_commission_third'];
            if (!empty($thirdValue) && is_numeric($thirdValue)) {
                $third = $thirdValue;
            }

            if ($vipCardSetting['share_type'] == 1) {
                $first = $first * $orderDetail->total_price / 100;
                $second = $second * $orderDetail->total_price / 100;
                $third = $third * $orderDetail->total_price / 100;
            } else {
                $first = $first * $orderDetail->num;
                $second = $second * $orderDetail->num;
                $third = $third * $orderDetail->num;
            }
        }

        return [
            'first' => $first,
            'second' => $second,
            'third' => $third
        ];
    }

    private function saveVipOrder($order, $data)
    {
        $cardOrder = new VipCardOrder();
        $cardOrder->mall_id = \Yii::$app->mall->id;
        $cardOrder->order_id = $order->id;
        $cardOrder->status = 0;
        $cardOrder->main_id = $data['main']['id'];
        $cardOrder->main_name = $data['main']['name'];
        $cardOrder->price = $data['price'];
        $cardOrder->detail_id = $data['id'];
        $cardOrder->user_id = $order->user_id;
        $cardOrder->detail_name = $data['name'];
        $cardOrder->expire = $data['expire_day'];
        $allSend['send_integral_num'] = $data['send_integral_num'];
        $allSend['send_balance'] = $data['send_balance'];
        $allSend['cards'] = $data['cards'];
        $allSend['coupons'] = $data['coupons'];
        $cardOrder->all_send = json_encode($allSend);
        if (!$cardOrder->save()) {
            throw new \Exception((new Model())->getErrorMsg($cardOrder));
        }
        return true;
    }
}