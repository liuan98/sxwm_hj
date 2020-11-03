<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */


namespace app\controllers\api\admin;

use app\forms\api\DeliveryForm;
use app\forms\common\order\OrderCancelForm;
use app\forms\common\order\OrderPriceForm;
use app\forms\common\order\OrderRefundForm;
use app\forms\common\order\OrderSendForm;
use app\forms\common\order\PrintForm;
use app\forms\mall\order\OrderForm;
use app\forms\mall\order\OrderDetailForm;
use app\forms\mall\order\OrderClerkForm;
use app\forms\mall\order\OrderUpdateAddressForm;
use app\forms\mall\order\OrderRefundListForm;

class OrderController extends AdminController
{
    public function actionIndex()
    {
        $form = new OrderForm();
        $form->attributes = \Yii::$app->request->get();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->search());
    }

    //订单详情
    public function actionDetail()
    {
        $form = new OrderDetailForm();
        // $form->order_id = 236;
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->search());
    }

    //订单取消
    public function actionCancel()
    {
        $form = new OrderCancelForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    //订单发货
    public function actionSend()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderSendForm();
            $form->attributes = \Yii::$app->request->post();

            return $this->asJson($form->save());
        }
    }

    //获取面单
    public function actionPrint()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new PrintForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->save());
        }
    }

    // 修改总价格
    public function actionUpdateTotalPrice()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderPriceForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->updateTotalPrice());
        }
    }

    //货到付款，确认收货
    public function actionConfirm()
    {
        $form = new OrderForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->confirm());
    }

    // 更新订单地址
    public function actionUpdateAddress()
    {
        $form = new OrderUpdateAddressForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    //售后订单列表
    public function actionRefund()
    {
        $form = new OrderRefundListForm();
        $form->attributes = \Yii::$app->request->get();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->search());
    }

    // 处理售后订单
    public function actionRefundHandle()
    {
        $form = new OrderRefundForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    public function actionAddressList()
    {
        $form = new OrderForm();
        return $this->asJson($form->addressList());
    }

    /**
     * 订单核销确认收款
     */
    public function actionClerkAffirmPay()
    {
        $form = new OrderClerkForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->affirmPay());
    }

    /**
     * 订单核销
     */
    public function actionOrderClerk()
    {
        $form = new OrderClerkForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->OrderClerk());
    }

    public function actionDelivery()
    {
        $form = new DeliveryForm();
        return $this->asJson($form->getDeliveryman());
    }

    // 售后退款订单，商家确认收货
    public function actionShouHuo() {
        if (\Yii::$app->request->isPost) {
            $form = new \app\forms\mall\order\OrderRefundForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->shouHuo());
        }
    }
}
