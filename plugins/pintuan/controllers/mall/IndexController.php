<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */


namespace app\plugins\pintuan\controllers\mall;


use app\plugins\Controller;
use app\plugins\pintuan\forms\mall\PinTuanSettingEditForm;
use app\plugins\pintuan\forms\mall\PinTuanSettingForm;
use app\plugins\pintuan\forms\mall\TemplateForm;
use app\plugins\pintuan\jobs\PintuanCreatedOrderJob;
use app\plugins\pintuan\models\Order;
use app\plugins\wxapp\Plugin;

class IndexController extends Controller
{
    public function actionIndex()
    {
        $this->repairPintuanOrder();
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new PinTuanSettingEditForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            } else {
                $form = new PinTuanSettingForm();
                return $this->asJson($form->getSetting());
            }
        } else {
            return $this->render('index');
        }
    }

    public function actionTemplate()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new TemplateForm();
                $form->mall = \Yii::$app->mall;
                $add = \Yii::$app->request->get('add');
                $platform = \Yii::$app->request->get('platform');
                return $this->asJson($form->getDetail($add,$platform));
            }
            if (\Yii::$app->request->isPost) {
                $form = new TemplateForm();
                $form->attributes = \Yii::$app->request->post();
                $form->mall = \Yii::$app->mall;
                return $this->asJson($form->save());
            }
        }
        return $this->render('template');
    }

    /**
     * 2019-07-24
     * TODO 此代码是为了修复 拼团订单数据错误,之后需删除
     */
    private function repairPintuanOrder()
    {
        $orders = Order::find()->alias('o')->where([
            'o.mall_id' => \Yii::$app->mall->id,
            'o.status' => 0,
            'o.is_pay' => 1,
            'o.sign' => 'pintuan',
            'o.is_delete' => 0,
            'o.cancel_status' => 0,
        ])->joinWith(['orderRelation AS or' => function ($query) {
            $query->andWhere(['or.is_parent' => 1, 'or.is_groups' => 1])
                ->joinWith(['pintuanOrder AS po' => function ($query2) {
                    $query2->andWhere([
                        'or',
                        ['po.status' => 0],
                        ['po.status' => 1],
                    ]);
                }]);
        }])->all();

        /** @var Order $order */
        foreach ($orders as $order) {
            if ((strtotime($order->pay_time) + $order->orderRelation->pintuanOrder->pintuan_time * 60 * 60) < time()) {
                $order->orderRelation->pintuanOrder->status = 1;
                $res = $order->orderRelation->pintuanOrder->save();
                if ($res) {
                    // 支付完成 再开始执行拼团订单创建任务
                    \Yii::$app->queue->delay(0)
                        ->push(new PintuanCreatedOrderJob([
                            'pintuan_order_id' => $order->orderRelation->pintuanOrder->id,
                        ]));
                }
            }
        }
    }
}
