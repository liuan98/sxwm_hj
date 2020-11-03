<?php

namespace app\plugins\booking\controllers\api;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\plugins\booking\forms\api\BookingOrderSubmitForm;
use app\plugins\booking\forms\common\CommonBooking;

class OrderController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    public function actionOrderPreview()
    {
        $form = new BookingOrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));

        $setting = CommonBooking::getSetting();
        return $this->asJson($form->setEnableCoupon(false)
            ->setEnableIntegral(true)
            ->setEnableOrderForm(true)
            ->setEnableMemberPrice(true)
            ->setSupportPayTypes($setting['payment_type'])
            ->setSign('booking')
            ->preview());
    }

    public function actionOrderSubmit()
    {
        $form = new BookingOrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        $setting = CommonBooking::getSetting();
        return $this->asJson($form->setEnableCoupon(false)
            ->setSupportPayTypes($setting['payment_type'])
            ->setEnableIntegral(true)
            ->setEnableOrderForm(false)
            ->setEnableMemberPrice(true)
            ->setSign('booking')
            ->submit());
    }
}
