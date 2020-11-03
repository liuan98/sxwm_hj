<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */


namespace app\plugins\miaosha\controllers\mall;


use app\plugins\Controller;
use app\plugins\miaosha\forms\mall\MiaoShaOpenTimeEditForm;
use app\plugins\miaosha\forms\mall\MiaoShaSettingEditForm;
use app\plugins\miaosha\forms\mall\MiaoShaSettingForm;

class IndexController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new MiaoShaSettingEditForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            } else {
                $form = new MiaoShaSettingForm();
                return $this->asJson($form->getSetting());
            }
        } else {
            return $this->render('index');
        }
    }

    public function actionOpenTime()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new MiaoShaOpenTimeEditForm();
                $form->data = \Yii::$app->request->post('open_time');
                return $this->asJson($form->save());
            } else {
            }
        } else {
            return $this->render('open-time');
        }
    }
}
