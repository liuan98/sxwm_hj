<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */


namespace app\plugins\wxapp\controllers;


use app\plugins\Controller;
use app\plugins\wxapp\forms\wx_app_config\WxAppConfigEditForm;
use app\plugins\wxapp\forms\wx_app_config\WxAppConfigForm;

class WxAppConfigController extends Controller
{
    public function actionSetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new WxAppConfigForm();
                $res = $form->getDetail();

                return $this->asJson($res);
            } else {
                $form = new WxAppConfigEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                return $form->save();
            }
        } else {
            return $this->render('setting');
        }
    }
}
