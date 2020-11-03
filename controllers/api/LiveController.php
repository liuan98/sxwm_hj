<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\controllers\api;


use app\forms\mall\live\LiveForm;

class LiveController extends ApiController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new LiveForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        } else {
            return $this->render('index');
        }
    }

    public function actionPlayBack()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new LiveForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getPlayBack());
            }
        }
    }

    public function actionClearQuota()
    {
        if (\Yii::$app->request->isGet) {
            if (\Yii::$app->request->get('is_true')) {
                $form = new LiveForm();
                $appid = \Yii::$app->request->get('app_id');

                dd($form->clearQuota($appid));
            }
        }

        dd('error');
    }
}