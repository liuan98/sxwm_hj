<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\scan_code_pay\controllers\mall;

use app\plugins\Controller;
use app\plugins\scan_code_pay\forms\mall\PosterForm;
use app\plugins\scan_code_pay\forms\mall\ScanCodePaySettingEditForm;
use app\plugins\scan_code_pay\forms\mall\ScanCodePaySettingForm;

class IndexController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new ScanCodePaySettingEditForm();
                $form->attributes= \Yii::$app->request->post();
                $res = $form->save();
                return $this->asJson($res);
            } else {
                $form = new ScanCodePaySettingForm();
                return $this->asJson($form->search());
            }
        } else {
            return $this->render('index');
        }
    }

    public function actionDownloadPoster()
    {
        $form = new PosterForm();
        return $this->asJson($form->get());
    }
}