<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\advance\forms\common;


use app\forms\common\CommonOptionP;
use app\models\Mall;
use app\models\Model;
use app\plugins\advance\models\AdvanceSetting;

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
        $setting = AdvanceSetting::find()->where(['mall_id' => \Yii::$app->mall->id])->one();

        if ($setting) {
            $default = $this->getDefault();
            $setting['payment_type'] = $setting['payment_type'] ?
                \Yii::$app->serializer->decode($setting['payment_type']) :
                $default['payment_type'];
            $setting['send_type'] = $setting['send_type'] ?
                \Yii::$app->serializer->decode($setting['send_type']) :
                $default['send_type'];
            $setting['deposit_payment_type'] = $setting['deposit_payment_type'] ?
                \Yii::$app->serializer->decode($setting['deposit_payment_type']) :
                $default['deposit_payment_type'];
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
            'is_advance' => 1,
            'is_share' => 0,
            'is_sms' => 0,
            'is_mail' => 0,
            'is_print' => 0,
            'is_territorial_limitation' => 0,
            'send_type' => ["express"],
            'goods_poster' => CommonOption::getPosterDefault(),
            'payment_type' => ['online_pay'],
            'deposit_payment_type' => ['online_pay'],
            'over_time' => 0,
        ];
    }
}
