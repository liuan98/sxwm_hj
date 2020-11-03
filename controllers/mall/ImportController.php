<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/11
 * Time: 10:47
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\controllers\mall;


use app\forms\mall\import\ImportApiForm;
use app\forms\mall\import\ImportForm;

class ImportController extends MallController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new ImportApiForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->import());
            }
        }
        return $this->render('index');
    }
}
