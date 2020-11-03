<?php

namespace app\plugins\stock\handlers;


use app\models\Model;
use app\plugins\stock\events\OrderEvent;
use app\models\Order;
use app\handlers\HandlerBase;
use app\plugins\stock\models\StockOrder;
use app\plugins\stock\models\StockSetting;
use app\plugins\stock\models\StockUser;


class OrderSalesHandler extends HandlerBase
{

    public function register()
    {
        \Yii::$app->on(Order::EVENT_SALES, function ($event) {
            /* @var OrderEvent $event */
            \Yii::$app->setMchId($event->order->mch_id);
            $t = \Yii::$app->db->beginTransaction();
            try {
                $setting = StockSetting::getList($event->order->mall_id);
                if ($setting['is_stock'] != 1) {
                    \Yii::error('股东分红未开启');
                } else {
                    \Yii::error('股东分红订单记录事件开始：');
                    if (StockUser::find()->where(['status' => 1, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])->count() <= 0) {
                        throw new \Exception('还未拥有股东，不记录分红订单池');
                    }
                    //记录订单分红金额
                    $model = new StockOrder();
                    $model->mall_id = $event->order->mall_id;
                    $model->order_id = $event->order->id;
                    $model->total_pay_price = $event->order->total_pay_price;
                    if (!$model->save()) {
                        throw new \Exception((new Model())->getErrorMsg($model));
                    }
                    \Yii::error('股东分红订单记录事件结束：ID-' . $model->id);
                }
                $t->commit();
            } catch (\Exception $exception) {
                $t->rollBack();
                \Yii::error('订单过售后股东分红事件：');
                \Yii::error($exception);
                throw $exception;
            }
        });
    }
}
