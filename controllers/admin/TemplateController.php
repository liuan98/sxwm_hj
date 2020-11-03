<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/11/18
 * Time: 14:49
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\controllers\admin;


use app\forms\admin\template\ListForm;

class TemplateController extends AdminController
{
    public function actionList()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        }
    }
}
