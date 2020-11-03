<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/13
 * Time: 17:55
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\handlers\orderHandler;

use app\events\OrderEvent;
use app\forms\common\CommonMallMember;
use app\models\MallMembers;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\models\ShareOrder;
use app\models\User;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchAccountLog;
use app\plugins\mch\models\MchOrder;
use yii\db\Exception;

/**
 * @property User $user
 */
abstract class BaseOrderSalesHandler extends BaseOrderHandler
{
    /* @var Order $order */
    public $order;
    /* @var User $user */
    public $user;
    /* @var OrderDetail[] $orderDetailList */
    public $orderDetailList;

    public function handle()
    {
        $this->sales();
    }

    protected function sales()
    {
        /* @var OrderEvent $event */
        $event = $this->event;
        \Yii::$app->setMchId($event->order->mch_id);
        \Yii::warning('=============订单售后事件开始执行===========');

        try {
            $this->order = $event->order;
            $this->user = User::find()->where(['id' => $this->order->user_id])->with(['userInfo', 'identity'])->one();

            $orderRefundList = OrderRefund::find()->where([
                'order_id' => $this->order->id,
                'is_delete' => 0
            ])->all();
            // 已退款的订单详情id列表
            $notOrderDetailIdList = [];
            if ($orderRefundList) {
                /* @var OrderRefund[] $orderRefundList */
                foreach ($orderRefundList as $orderRefund) {
                    if ($orderRefund->is_confirm == 0) {
                        return false;
                    } else {
                        if ($orderRefund->type == 1 && $orderRefund->status == 2) {
                            $notOrderDetailIdList[] = $orderRefund->order_detail_id;
                        }
                    }
                }
            }
            $this->orderDetailList = OrderDetail::find()->where(['order_id' => $this->order->id, 'is_delete' => 0])
                ->with('goods')
                ->keyword(!empty($notOrderDetailIdList), ['not in', 'id', $notOrderDetailIdList])->all();

            $this->action();
        } catch (\Exception $e) {
            \Yii::error($e);
        }
    }

    protected function action()
    {
        // 发放佣金
        $res = $this->giveShareMoney();
        // 发放积分
        $this->giveIntegral();
        // 入驻商订单金额转到商户余额
        $this->transferToMch($res);
        // 消费升级会员等级
        $this->level();
    }

    // 分销佣金的发放已下单时的分销设置为准
    protected function giveShareMoney()
    {
        try {
            \Yii::warning('发放佣金');
            $shareOrderList = ShareOrder::findAll([
                'mall_id' => $this->order->mall_id, 'order_id' => $this->order->id, 'is_delete' => 0,
                'is_transfer' => 0, 'is_refund' => 0
            ]);
            $list = [
                'first_parent_id' => 0,
                'first_price' => 0,
                'second_parent_id' => 0,
                'second_price' => 0,
                'third_parent_id' => 0,
                'third_price' => 0,
            ];
            foreach ($shareOrderList as $shareOrder) {
                foreach ($this->orderDetailList as $orderDetail) {
                    if ($orderDetail->id == $shareOrder->order_detail_id) {
                        $list['first_parent_id'] = $shareOrder->first_parent_id;
                        $list['second_parent_id'] = $shareOrder->second_parent_id;
                        $list['third_parent_id'] = $shareOrder->third_parent_id;
                        $list['first_price'] += $shareOrder->first_price;
                        $list['second_price'] += $shareOrder->second_price;
                        $list['third_price'] += $shareOrder->third_price;
                    }
                }
            }
            if ($list['first_parent_id'] > 0) {
                $first = User::findOne($list['first_parent_id']);
                \Yii::$app->currency->setUser($first)->brokerage
                    ->add(floatval($list['first_price']), "由订单{$this->order->order_no}提供的分销佣金");
            }
            if ($list['second_parent_id'] > 0) {
                $second = User::findOne($list['second_parent_id']);
                \Yii::$app->currency->setUser($second)->brokerage
                    ->add(floatval($list['second_price']), "由订单{$this->order->order_no}提供的分销佣金");
            }
            if ($list['third_parent_id'] > 0) {
                $third = User::findOne($list['third_parent_id']);
                \Yii::$app->currency->setUser($third)->brokerage
                    ->add(floatval($list['third_price']), "由订单{$this->order->order_no}提供的分销佣金");
            }
            ShareOrder::updateAll(['is_transfer' => 1], [
                'mall_id' => $this->order->mall_id, 'order_id' => $this->order->id, 'is_delete' => 0
            ]);
            return $list;
        } catch (Exception $e) {
            return false;
        }
    }

    // 积分发放
    protected function giveIntegral()
    {
        try {
            $integral = 0;
            foreach ($this->orderDetailList as $orderDetail) {
                if ($orderDetail->goods->give_integral_type == 1) {
                    $integral += ($orderDetail->goods->give_integral * $orderDetail->num);
                } else {
                    $integral += (intval($orderDetail->goods->give_integral * $orderDetail->total_price / 100));
                }
            }
            if ($integral > 0) {
                \Yii::$app->currency->setUser($this->user)->integral->add($integral, '订单购买赠送积分');
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    protected function transferToMch($res)
    {
        if (!$this->order->mch_id > 0) {
            return false;
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $mch = Mch::findOne($this->order->mch_id);
            if (!$mch) {
                throw new \Exception('多端户结算:商户不存在');
            }
            /** @var OrderRefund[] $orderRefund */
            $orderRefund = OrderRefund::find()->where([
                'mall_id' => $this->order->mall_id,
                'mch_id' => $this->order->mch_id,
                'order_id' => $this->order->id
            ])->all();
            $totalPayPrice = $this->order->total_pay_price;
            if ($orderRefund) {
                foreach ($orderRefund as $rItem) {
                    if ($rItem->is_refund > 0) {
                        $totalPayPrice = $totalPayPrice - $rItem->reality_refund_price;
                    }
                }
            }
            $totalPayPrice = $totalPayPrice * (1 - $mch->transfer_rate / 1000);
            $totalPayPrice = $totalPayPrice - $res['first_price'] - $res['second_price'] - $res['third_price'];

            $mch->account_money += $totalPayPrice;
            $res = $mch->save();
            if (!$res) {
                throw new \Exception((new Model())->getErrorMsg($mch));
            }

            $mchOrder = MchOrder::findOne(['order_id' => $this->order->id]);
            if (!$mchOrder) {
                throw new \Exception('多端户结算:多商户订单不存在');
            }
            $mchOrder->is_transfer = 1;
            $res = $mchOrder->save();
            if (!$res) {
                throw new \Exception((new Model())->getErrorMsg($mch));
            }

            $mchAccountLog = new MchAccountLog();
            $mchAccountLog->mall_id = $this->order->mall_id;
            $mchAccountLog->mch_id = $this->order->mch_id;
            $mchAccountLog->money = $totalPayPrice;
            $mchAccountLog->desc = '订单号:' . $this->order->order_no . '结算';
            $mchAccountLog->type = 1;
            $res = $mchAccountLog->save();
            if (!$res) {
                throw new \Exception((new Model())->getErrorMsg($mch));
            }

            $transaction->commit();
            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            \Yii::error('商户结算异常:' . $e->getMessage());
            return false;
        }
    }

    // 消费升级会员等级
    protected function level()
    {
        try {
            // 订单总额
            $commonMallMember = new CommonMallMember();
            $mallId = $this->order->mall_id;
            $userId = $this->order->user_id;
            $orderMoneyCount = $commonMallMember->getOrderMoneyCount($mallId, $userId);

            \Yii::warning('会员升级，当前用户消费总额' . $orderMoneyCount);

            $nowLevel = $this->user->identity->member_level;
            /* @var MallMembers $nextLevel */
            $nextLevel = MallMembers::find()
                ->where(['mall_id' => $this->order->mall_id, 'is_delete' => 0, 'auto_update' => 1, 'status' => 1])
                ->andWhere(['>', 'level', $nowLevel])->andWhere(['<=', 'money', $orderMoneyCount])
                ->orderBy(['level' => SORT_DESC])
                ->one();
            if ($nextLevel) {
                $this->user->identity->member_level = $nextLevel->level;
                if (!$this->user->identity->save()) {
                    throw new Exception($this->user->identity->errors[0]);
                }
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
