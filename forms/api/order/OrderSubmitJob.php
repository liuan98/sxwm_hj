<?php
/**
 * @copyright (c)天幕网络
 * @author Lu Wei
 * @link http://www.67930603.top/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/17 10:56
 */


namespace app\forms\api\order;


use app\events\OrderEvent;
use app\models\Mall;
use app\models\Model;
use app\models\Order;
use app\models\OrderSubmitResult;
use app\models\User;
use app\models\UserCoupon;
use app\plugins\mch\models\MchOrder;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

class OrderSubmitJob extends BaseObject implements JobInterface
{
    /** @var Mall $mall */
    public $mall;

    /** @var User $user */
    public $user;

    /** @var array $data */
    public $form_data;

    /** @var string $token */
    public $token;

    public $sign;
    public $supportPayTypes;
    public $enableMemberPrice;
    public $enableCoupon;
    public $enableIntegral;
    public $enableOrderForm;
    public $enablePriceEnable;
    public $enableAddressEnable;
    public $status;
    public $appVersion;

    /** @var string $OrderSubmitFormClass */
    public $OrderSubmitFormClass;

    /**
     * @param Queue $queue which pushed and is handling the job
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function execute($queue)
    {
        \Yii::$app->user->setIdentity($this->user);
        \Yii::$app->setMall($this->mall);
        \Yii::$app->setAppVersion($this->appVersion);
        \Yii::$app->setAppPlatform($this->user->userInfo->platform);

        $t = \Yii::$app->db->beginTransaction();
        try {
            $oldOrder = Order::findOne(['token' => $this->token, 'sign' => $this->sign, 'is_delete' => 0]);
            if ($oldOrder) {
                throw new \Exception('重复下单。');
            }
            /** @var OrderSubmitForm $form */
            $form = new $this->OrderSubmitFormClass();
            $form->form_data = $this->form_data;
            $form->setSign($this->sign)
                ->setEnableMemberPrice($this->enableMemberPrice)
                ->setEnableCoupon($this->enableCoupon)
                ->setEnableIntegral($this->enableIntegral)
                ->setEnablePriceEnable($this->enablePriceEnable)
                ->setEnableAddressEnable($this->enableAddressEnable)
                ->setEnableOrderForm($this->enableOrderForm);

            $data = $form->getAllData();
            if (!$data['address_enable']) {
                throw new \Exception('当前收货地址不允许购买。');
            }
            if (!$data['price_enable']) {
                throw new \Exception('订单总价未达到起送要求。');
            }
            foreach ($data['mch_list'] as $mchItem) {
                $order = new Order();

                $order->mall_id = \Yii::$app->mall->id;
                $order->user_id = \Yii::$app->user->identity->getId();
                $order->mch_id = $mchItem['mch']['id'];

                $order->order_no = date('YmdHis') . rand(100000, 999999);

                $order->total_price = $mchItem['total_price'];
                $order->total_pay_price = $mchItem['total_price'];
                $order->express_original_price = $mchItem['express_price'];
                $order->express_price = $mchItem['express_price'];
                $order->total_goods_price = $mchItem['total_goods_price'];
                $order->total_goods_original_price = $mchItem['total_goods_original_price'];

                $order->member_discount_price = $mchItem['member_discount'];
                $order->use_user_coupon_id = $mchItem['coupon']['use'] ? $mchItem['coupon']['user_coupon_id'] : 0;
                $order->coupon_discount_price = $mchItem['coupon']['coupon_discount'];
                $order->use_integral_num = $mchItem['integral']['use'] ? $mchItem['integral']['use_num'] : 0;
                $order->integral_deduction_price = $mchItem['integral']['use'] ?
                    $mchItem['integral']['deduction_price'] : 0;

                $order->name = $data['address']['name'];
                $order->mobile = $data['address']['mobile'];
                if ($mchItem['delivery']['send_type'] !== 'offline') {
                    $order->address = $data['address']['province']
                        . ' '
                        . $data['address']['city']
                        . ' '
                        . $data['address']['district']
                        . ' '
                        . $data['address']['detail'];
                }
                $order->remark = $mchItem['remark'];
                $order->order_form = $order->encodeOrderForm($mchItem['order_form_data']);
                $order->distance = isset($mchItem['form_data']['distance']) ? $mchItem['form_data']['distance'] : 0;//同城距离
                $order->words = '';

                $order->is_pay = 0;
                $order->pay_type = 0;
                $order->is_send = 0;
                $order->is_confirm = 0;
                $order->is_sale = 0;
                $order->support_pay_types = $order->encodeSupportPayTypes($this->supportPayTypes);

                if ($mchItem['delivery']['send_type'] === 'offline') {
                    if (empty($mchItem['store'])) {
                        throw new \Exception('请选择自提门店。');
                    }
                    $order->store_id = $mchItem['store']['id'];
                    $order->send_type = 1;
                } elseif ($mchItem['delivery']['send_type'] === 'city') {
                    $order->distance = $mchItem['distance'];
                    $order->location = $data['address']['longitude'] . ',' . $data['address']['latitude'];
                    $order->send_type = 2;
                    $order->store_id = 0;
                } else {
                    $order->send_type = 0;
                    $order->store_id = 0;
                }

                $order->sign = $this->sign !== null ? $this->sign : '';
                $order->token = $this->token;
                $order->status = $this->status;

                if (!$order->save()) {
                    throw new \Exception((new Model())->getErrorMsg($order));
                }

                if ($mchItem['mch']['id'] > 0) {
                    $mchOrder = new MchOrder();
                    $mchOrder->order_id = $order->id;
                    $res = $mchOrder->save();
                    if (!$res) {
                        throw new \Exception('多商户订单创建失败');
                    }
                }

                foreach ($mchItem['goods_list'] as $goodsItem) {
                    $form->subGoodsNum($goodsItem['goods_attr'], $goodsItem['num'], $goodsItem);
                    $form->extraGoodsDetail($order, $goodsItem);
                }

                // 优惠券标记已使用
                if ($order->use_user_coupon_id) {
                    $userCoupon = UserCoupon::findOne($order->use_user_coupon_id);
                    $userCoupon->is_use = 1;
                    if ($userCoupon->update(true, ['is_use']) === false) {
                        throw new \Exception('优惠券状态更新失败。');
                    }
                }

                // 扣除积分
                if ($order->use_integral_num) {
                    if (!\Yii::$app->currency->integral->sub($order->use_integral_num, '下单积分抵扣')) {
                        throw new \Exception('积分操作失败。');
                    }
                }

                /**
                 * 开放额外的订单处理接口
                 */
                $form->extraOrder($order, $mchItem);

                // 购物车ID
                $cartIds = [];
                foreach ($mchItem['form_data']['goods_list'] as $goodsItem) {
                    $cartIds[] = $goodsItem['cart_id'];
                }


                $event = new OrderEvent();
                $event->order = $order;
                $event->sender = $this;
                $event->cartIds = $cartIds;
                $event->pluginData = ['sign' => 'vip_card','vip_discount' => $mchItem['vip_discount'] ?? null ];
                \Yii::$app->trigger(Order::EVENT_CREATED, $event);
            }

            $t->commit();
        } catch (\Exception $e) {
            $t->rollBack();
            \Yii::error($e->getMessage());
            \Yii::error($e);
            $orderSubmitResult = new OrderSubmitResult();
            $orderSubmitResult->token = $this->token;
            $orderSubmitResult->data = $e->getMessage();
            $orderSubmitResult->save();
            throw $e;
        }
    }
}
