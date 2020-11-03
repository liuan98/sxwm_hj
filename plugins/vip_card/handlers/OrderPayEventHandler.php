<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/8/21
 * Time: 15:49
 */

namespace app\plugins\vip_card\handlers;

use app\events\OrderEvent;
use app\forms\common\card\CommonSend;
use app\forms\common\template\TemplateSend;
use app\forms\common\template\tplmsg\Tplmsg;
use app\handlers\orderHandler\BaseOrderPayedHandler;
use app\jobs\UserCardCreatedJob;
use app\models\Coupon;
use app\models\GoodsCards;
use app\models\Order;
use app\models\OrderPayResult;
use app\models\UserCard;
use app\models\UserCoupon;
use app\plugins\vip_card\forms\common\AddShareOrder;
use app\plugins\vip_card\forms\common\CommonVipCardSetting;
use app\plugins\vip_card\models\VipCard;
use app\plugins\vip_card\models\VipCardDetail;
use app\plugins\vip_card\models\VipCardOrder;
use app\plugins\vip_card\models\VipCardUser;
use app\forms\common\share\CommonShare;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

class OrderPayEventHandler extends BaseOrderPayedHandler
{
    public function handle()
    {
        self::execute();
    }

    protected function execute()
    {
        $this->user = $this->event->order->user;
        self::notice();
        self::pay();
        self::addShareOrder();
    }

    /**
     * @return $this
     * 保存支付完成处理结果
     */
    protected function saveResult()
    {
        $userCouponList = $this->sendCoupon();
        $userCardList = $this->sendCard();
        $data = [
            'card_list' => $userCardList,
            'user_coupon_list' => $userCouponList,
            'send_data' => $this->getSendData(),
        ];
        $orderPayResult = new OrderPayResult();
        $orderPayResult->order_id = $this->event->order->id;
        $orderPayResult->data = $orderPayResult->encodeData($data);
        $orderPayResult->save();
        return $this;
    }

    protected function notice()
    {
        \Yii::error('--vip_card notice--');
        $this->sendTemplate();
        $this->sendBuyPrompt();
        $this->setGoods();
        return $this;
    }

    /**
     * 有点特殊，只能重写了
     */
    protected function sendTemplate()
    {
        try {
            $detail = $this->event->order->detail[0];
            $goodsInfo = \Yii::$app->serializer->decode($detail['goods_info']);
            $data = [
                'keyword1' => [
                    'value' => $this->event->order->order_no,
                    'color' => '#333333',
                ],
                'keyword2' => [
                    'value' => $this->event->order->pay_time,
                    'color' => '#333333',
                ],
                'keyword3' => [
                    'value' => $this->event->order->total_pay_price,
                    'color' => '#333333',
                ],
                'keyword4' => [
                    'value' => $goodsInfo['goods_attr']['name'] ?? '超级会员卡',
                    'color' => '#333333',
                ]
            ];

            $template = new TemplateSend();
            $template->user = $this->event->order->user;
            $template->page = 'pages/order/index/index';
            $template->data = $data;
            $template->templateTpl = 'order_pay_tpl';
            $template->sendTemplate();
        } catch (\Exception $exception) {
            \Yii::error('模板消息发送: ' . $exception->getMessage());
        }
        return $this;
    }

    protected function pay()
    {
        \Yii::error('--vip_card pay--');
        $this->saveResult();
        $this->sendMail();
        $this->sendSms();
        $this->becomeJuniorByFirstPay();
        $this->becomeShare();
        $this->updateOrderStatus();
        $this->updateVipOrderStatus();
        $this->updateVipCardNum();
        return $this;
    }

    public function becomeShare()
    {
        try {
            $setting = (new CommonVipCardSetting())->getSetting();
            if (!$setting['is_share'] || !$setting['is_buy_become_share']) {
                return $this;
            }
            $commonShare = new CommonShare();
            $commonShare->mall = $this->mall;
            $commonShare->becomeShare($this->event->order->user,  [
                'status' => 1,
                'reason' => "购买超级会员卡自动成为分销商"
            ]);
        } catch (\Exception $exception) {
            \Yii::error('下单成为分销商(购买超级会员卡): ' . $exception->getMessage());
        }
        return $this;
    }

    /**
     * @return array
     * 向用户发送商品卡券
     */
    protected function sendCard()
    {
        try {
            $cardList = $this->sendCardAction();
        } catch (\Exception $exception) {
            \Yii::error('卡券发放失败: ' . $exception->getMessage());
            $cardList = [];
        }
        return $cardList;
    }

    /**
     * @return array
     * 向用户发送优惠券（自动发送方案--订单支付成功发送优惠券）
     */
    protected function sendCoupon()
    {
        try {
            $userCouponList = $this->sendCouponAction();
        } catch (\Exception $exception) {
            \Yii::error('优惠券发放失败: ' . $exception->getMessage());
            $userCouponList = [];
        }
        return $userCouponList;
    }

    private function sendCardAction()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        $cardList = [];
        try {
            $dataArr = $this->getVipCardSend();
            foreach ($dataArr['cards'] as $card) {
                $res = (new GoodsCards())->updateCount('sub', $card['send_num'], $card['card_id']);
                /** @var GoodsCards $cards */
                $cards = GoodsCards::findOne(['id' => $card['card_id'], 'is_delete' => 0, 'mall_id' => $this->event->order->mall_id]);
                if (!$cards) {
                    continue;
                }
                if ($cards->expire_type == 1) {
                    $endTime = date('Y-m-d H:i:s', time() + $cards->expire_day * 86400);
                } else {
                    $endTime = $cards->end_time;
                }
                // 卡券有多张
                for ($i = 1; $i <= $card['send_num']; $i++) {
                    $userCard = new UserCard();
                    $userCard->mall_id = $this->event->order->mall_id;
                    $userCard->user_id = $this->event->order->user_id;
                    $userCard->card_id = $cards->id;
                    $userCard->name = $cards->name;
                    $userCard->pic_url = $cards->pic_url;
                    $userCard->content = $cards->description;
                    $userCard->created_at = mysql_timestamp();
                    $userCard->is_use = 0;
                    $userCard->clerk_id = 0;
                    $userCard->store_id = 0;
                    $userCard->clerked_at = '0000-00-00 00:00:00';
                    $userCard->order_id = $this->event->order->id;
                    $userCard->order_detail_id = $this->event->order->detail[0]->id;
                    $userCard->data = '';
                    $userCard->start_time = $cards->expire_type == 1 ? mysql_timestamp() : $cards->begin_time;
                    $userCard->end_time = $endTime;
                    $userCard->save();
                    $cardList[] = ArrayHelper::toArray($userCard);

                    $interval = CommonSend::hour * 3600;
                    $diff = strtotime($endTime) - time();
                    $diff = $diff > $interval ? $diff - $interval : 0;

                    \Yii::$app->queue->delay($diff)->push(new UserCardCreatedJob([
                        'mall' => \Yii::$app->mall,
                        'id' => $userCard->id,
                        'user_id' => $this->event->order->user_id,
                    ]));
                }
            }

            $transaction->commit();
        } catch (\Exception $exception) {
            $transaction->rollBack();
            \Yii::error('卡券发放失败');
            \Yii::error($exception);
        }

        return $cardList;
    }

    private function sendCouponAction()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        $couponList = [];
        try {
            $dataArr = $this->getVipCardSend();
            foreach ($dataArr['coupons'] as $coupon) {
                $res = (new Coupon())->updateCount($coupon['send_num'], 'sub', $coupon['coupon_id']);
                /** @var Coupon $newCoupon */
                $newCoupon = Coupon::find()->where(['id' => $coupon['coupon_id'], 'is_delete' => 0, 'mall_id' => $this->event->order->mall_id])
                    ->with('goods', 'cat')->one();
                if (!$newCoupon) {
                    continue;
                }
                for ($i = 1; $i <= $coupon['send_num']; $i++) {
                    $userCoupon = new UserCoupon();
                    $userCoupon->mall_id = $this->event->order->mall_id;
                    $userCoupon->user_id = $this->event->order->user_id;
                    $userCoupon->coupon_id = $newCoupon->id;
                    $userCoupon->coupon_min_price = $newCoupon->min_price;
                    $userCoupon->sub_price = $newCoupon->sub_price;
                    $userCoupon->discount = $newCoupon->discount;
                    $userCoupon->type = $newCoupon->type;
                    $userCoupon->is_use = 0;
                    $userCoupon->receive_type = ($dataArr['main']['name'] ?? '超级会员卡') . '赠送优惠券';
                    if ($newCoupon->expire_type == 1) {
                        $time = time();
                        $userCoupon->start_time = date('Y-m-d H:i:s', $time);
                        $userCoupon->end_time = date('Y-m-d H:i:s', $time + $newCoupon->expire_day * 86400);
                    } else {
                        $userCoupon->start_time = $newCoupon->begin_time;
                        $userCoupon->end_time = $newCoupon->end_time;
                    }
                    $cat = $newCoupon->cat;
                    $goods = $newCoupon->goods;
                    $arr = ArrayHelper::toArray($newCoupon);
                    $arr['cat'] = ArrayHelper::toArray($cat);
                    $arr['goods'] = ArrayHelper::toArray($goods);
                    $userCoupon->coupon_data = json_encode($arr, JSON_UNESCAPED_UNICODE);
                    if (!$userCoupon->save()) {
                        throw new \Exception($this->getErrorMsg($userCoupon));
                    }

                    // 记录
                    $couponData = ArrayHelper::toArray($newCoupon);
                    if ($couponData['expire_type'] == 1) {
                        $couponData['desc'] = "本券有效期为发放后{$couponData['expire_day']}天内";
                    } else {
                        $couponData['desc'] = "本券有效期" . $couponData['begin_time'] . "至" . $couponData['end_time'];
                    }
                    $couponData['content'] = ($dataArr['main']['name'] ?? '超级会员卡') . '赠送优惠券';

                    $couponList[] = $couponData;
                }
            }

            $transaction->commit();
        } catch (\Exception $exception) {
            $transaction->rollBack();
            \Yii::error('优惠券发放失败');
            \Yii::error($exception);
        }

        return $couponList;
    }

    private function getVipCardSend()
    {
        $detail = $this->event->order->detail[0];
        $goodsInfo = \Yii::$app->serializer->decode($detail['goods_info']);


        return $goodsInfo['rules_data'];
    }

    public function updateOrderStatus()
    {
        \Yii::warning('超级会员卡订单状态更新开始');
        $order = $this->event->order;
        $order->is_sale = 1;
        $order->auto_sales_time = mysql_timestamp();
        $order->is_confirm = 1;
        $order->confirm_time = mysql_timestamp();
        $order->is_send = 1;
        $order->send_time = mysql_timestamp();
        $order->comment_time = mysql_timestamp();
        $res = $order->save();
        if (!$res) {
            \Yii::error('超级会员卡下单状态更新失败' . $this->getErrorMsg($order));
        }

        $event = new OrderEvent();
        $event->order = $order;
        \Yii::$app->trigger(Order::EVENT_SALES, $event);
    }

    public function updateVipOrderStatus()
    {
        $order = VipCardOrder::findOne(['order_id' => $this->event->order->id]);
        $order->status = 1;
        $res = $order->save();
        if (!$res) {
            \Yii::error('超级会员卡订单更新失败' . $this->getErrorMsg($order));
        }
    }

    public function getSendData()
    {
        $dataArr = $this->getVipCardSend();

        return [
            'send_balance' => $dataArr['send_balance'],
            'send_integral_num' => $dataArr['send_integral_num'],
        ];
    }

    public function updateVipCardNum()
    {
        $res = VipCardDetail::updateAllCounters(['num' => -1],['AND',['id' => $this->getVipCardSend()['id']],['>', 'num', 0]]);
        if (!$res) {
            throw new Exception('超级会员卡减少库存失败');
        }
    }

    public function addShareOrder()
    {
        try {
            (new AddShareOrder())->save($this->event->order);
        } catch (\Exception $exception) {
            \Yii::error('超级会员卡分销佣金记录失败：' . $exception->getMessage());
            \Yii::error($exception);
        }
        return $this;
    }
}
