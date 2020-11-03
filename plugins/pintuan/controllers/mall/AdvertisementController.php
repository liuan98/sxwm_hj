<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\pintuan\controllers\mall;

use app\core\response\ApiCode;
use app\plugins\Controller;
use app\plugins\pintuan\forms\common\SettingForm;
use app\plugins\pintuan\forms\mall\PinTuanAdvertisementEditForm;

class AdvertisementController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new PinTuanAdvertisementEditForm();
                $data = \Yii::$app->request->post('form');
                $form->advertisement = isset($data['advertisement']) ? $data['advertisement'] : [];
                $form->is_advertisement = isset($data['is_advertisement']) ? $data['is_advertisement'] : 0;
                return $this->asJson($form->save());
            } else {
                $setting = (new SettingForm())->search();
                return $this->asJson([
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '请求成功',
                    'data' => [
                        'is_advertisement' => $setting['is_advertisement'],
                        'advertisement' => $setting['advertisement'],
                    ]
                ]);
            }
        } else {
            return $this->render('index');
        }
    }
}
