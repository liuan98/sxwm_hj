<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\integral_mall\forms\common;


use app\forms\common\CommonOptionP;
use app\forms\common\version\Compatible;
use app\models\Mall;
use app\models\Model;
use app\plugins\integral_mall\models\IntegralMallSetting;

/**
 * @property Mall $mall
 */
class SettingForm extends Model
{
    public static $setting;

    public function search()
    {
        if (self::$setting) {
            return self::$setting;
        }
        $setting = IntegralMallSetting::find()->where(['mall_id' => \Yii::$app->mall->id])->one();

        if ($setting) {
            $default = $this->getDefault();
            $setting['desc'] = $setting['desc'] ? \Yii::$app->serializer->decode($setting['desc']) : $default['desc'];
            $setting['payment_type'] = $setting['payment_type'] ?
                \Yii::$app->serializer->decode($setting['payment_type']) :
                $default['payment_type'];
            $setting['send_type'] = Compatible::getInstance()->sendType($setting['send_type']);
            $setting['goods_poster'] = $setting['goods_poster'] ?
                \Yii::$app->serializer->decode($setting['goods_poster']) :
                CommonOption::getPosterDefault();

        } else {
            $setting = $this->getDefault();
        }
        self::$setting = $setting;
        return $setting;
    }

    private function getDefault()
    {
        return [
            'is_share' => 0,
            'is_sms' => 0,
            'is_mail' => 0,
            'is_print' => 0,
            'is_territorial_limitation' => 0,
            'desc' => [],
            'send_type' => ['express', 'offline'],
            'goods_poster' => CommonOption::getPosterDefault(),
            'payment_type' => ['online_pay']
        ];
    }
}
