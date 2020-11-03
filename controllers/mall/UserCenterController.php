<?php
/**
 * @copyright (c)天幕网络
 * @author Lu Wei
 * @link http://www.67930603.top/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/8 18:11
 */


namespace app\controllers\mall;


use app\forms\mall\copyright\CopyrightEditForm;
use app\forms\mall\copyright\CopyrightForm;
use app\forms\mall\user_center\UserCenterEditForm;
use app\forms\mall\user_center\UserCenterForm;

class UserCenterController extends MallController
{
    public function actionSetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new UserCenterForm();
                $res = $form->getDetail();

                return $this->asJson($res);
            } else {
                $form = new UserCenterEditForm();
                $form->data = \Yii::$app->request->post('form');
                return $form->save();
            }
        } else {
            return $this->render('setting');
        }
    }

    public function actionResetDefault()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new UserCenterEditForm();
            return $form->reset();
        }
    }
}
