<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\pintuan\controllers\mall;

use app\plugins\Controller;
use app\plugins\pintuan\forms\mall\PinTuanOrderGroupsForm;

class OrderGroupsController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new PinTuanOrderGroupsForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    //订单详情
    public function actionDetail()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new PinTuanOrderGroupsForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->detail());
        } else {
            return $this->render('detail');
        }
    }
}
