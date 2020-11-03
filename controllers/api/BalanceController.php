<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\controllers\api;


use app\controllers\api\filters\LoginFilter;
use app\forms\api\BalanceForm;

class BalanceController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
                'ignore' => ['index', 'logs']
            ],
        ]);
    }

    public function actionIndex()
    {
        $form = new BalanceForm();
        $res = $form->getIndex();

        return $res;
    }

    public function actionLogs()
    {
        $form = new BalanceForm();
        $form->attributes = \Yii::$app->request->get();
        $res = $form->getLogs();

        return $res;
    }

    public function actionLogDetail()
    {
        $form = new BalanceForm();
        $form->attributes = \Yii::$app->request->get();
        $res = $form->getLogDetail();

        return $res;
    }
}
