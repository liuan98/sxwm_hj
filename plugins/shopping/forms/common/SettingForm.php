<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\shopping\forms\common;


use app\models\Mall;
use app\models\Model;
use app\plugins\shopping\models\ShoppingSetting;

/**
 * @property Mall $mall
 */
class SettingForm extends Model
{
    public function search()
    {
        $setting = ShoppingSetting::find()->where(['mall_id' => \Yii::$app->mall->id])->asArray()->one();

        if (!$setting) {
            $setting = $this->getDefault();
        }

        $setting['is_open'] = (int)$setting['is_open'];

        return $setting;
    }

    private function getDefault()
    {
        return [
            'is_open' => 0
        ];
    }
}
