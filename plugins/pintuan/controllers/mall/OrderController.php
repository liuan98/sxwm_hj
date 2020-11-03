<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\pintuan\controllers\mall;

use app\forms\mall\order\OrderRefundListForm;
use app\forms\mall\order\OrderSendForm;
use app\plugins\Controller;
use app\forms\mall\order\OrderDestroyForm;
use app\forms\mall\order\OrderDetailForm;
use app\plugins\pintuan\forms\mall\OrderForm;
use app\plugins\pintuan\forms\mall\OrderRobotForm;
use app\plugins\pintuan\forms\mall\PintuanOrderRefundListForm;
use app\plugins\pintuan\jobs\PintuanCreatedOrderJob;
use app\plugins\pintuan\models\PintuanOrderRelation;
use app\plugins\pintuan\models\PintuanOrders;

class OrderController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->setSign(\Yii::$app->plugin->getCurrentPlugin()->getName())->search());
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new OrderForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fields = $fields;
                $form->setSign(\Yii::$app->plugin->getCurrentPlugin()->getName())->search();
                return false;
            } else {
                return $this->render('index');
            }
        }
    }

    //订单详情
    public function actionDetail()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderDetailForm();
            $form->attributes = \Yii::$app->request->get();
            $res = $form->setSign(\Yii::$app->plugin->getCurrentPlugin()->getName())->search();
            $order = $res['data']['order'];
            if ($order['orderRelation']['pintuanOrder']['status'] == 1 ||
                $order['orderRelation']['pintuanOrder']['status'] == 3) {
                $order['is_send_show'] = 0;
                $order['is_cancel_show'] = 0;
                $order['is_clerk_show'] = 0;
            }
            $res['data']['order'] = $order;
            return $this->asJson($res);
        } else {
            return $this->render('detail');
        }
    }

    //清空回收站
    public function actionDestroyAll()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderDestroyForm();
            return $this->asJson($form->setSign(\Yii::$app->plugin->getCurrentPlugin()->getName())->destroyAll());
        }
    }

    //售后订单列表
    public function actionRefund()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderRefundListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->setSign(\Yii::$app->plugin->getCurrentPlugin()->getName())->search());
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new PintuanOrderRefundListForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fields = $fields;
                $form->setSign(\Yii::$app->plugin->getCurrentPlugin()->getName());
                $form->search();
                return false;
            } else {
                return $this->render('refund');
            }
        }
    }

    //批量发货
    public function actionBatchSend()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderSendForm();
            if (\Yii::$app->request->isPost) {
                $form->is_express = 1;
                $form->attributes = \Yii::$app->request->post();
                return $form->batchSave();
            } else {
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->batchDetail());
            }
        } else {
            return $this->render('batch-send');
        }
    }

    public function actionAddRobot()
    {
        $form = new OrderRobotForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->save();

        return $this->asJson($res);
    }

    /**
     * 用于手动生成拼团失败待退款订单队列任务，
     * @throws \Exception
     */
    public function actionCreateJob()
    {
        $pintuanOrders = PintuanOrders::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'status' => 4
        ])
            ->with('orderRelation.order')
            ->all();
        $orderNoList = [];
        $pintuanOrderId = [];
        /** @var PintuanOrders $item */
        foreach ($pintuanOrders as $item) {
            /** @var PintuanOrderRelation $orItem */
            foreach ($item->orderRelation as $orItem) {
                if ($orItem->robot_id == 0 && $orItem->order->cancel_status != 1) {
                    $orderNoList[] = $orItem->order->order_no;
                }
            }
            $pintuanOrderId[] = $item->id;
        }

        $isOk = \Yii::$app->request->get('isOk') ?: false;
        if ($isOk == true) {
            foreach ($pintuanOrderId as $id) {
                \Yii::$app->queue->delay(0)
                    ->push(new PintuanCreatedOrderJob([
                        'pintuan_order_id' => $id,
                    ]));
            }
            $orderNoList['isOk'] = $isOk;
        }
        dd($orderNoList);
    }
}
