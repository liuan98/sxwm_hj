<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\booking\forms\common;

use app\models\Model;
use app\plugins\booking\models\BookingSetting;

class CommonBooking extends Model
{
    public static function getSetting()
    {
        $setting = BookingSetting::find()->where([
            'mall_id' => \Yii::$app->mall->id,
        ])->one();
        $default = [
            'is_share' => 0,
            'is_sms' => 0,
            'is_mail' => 0,
            'is_print' => 0,
            'is_cat' => 1,
            'is_form' => 0,
            'form_data' => [],
            'payment_type' => ['online_pay'],
            'goods_poster' => CommonOption::getPosterDefault(),
        ];
        if ($setting) {
            $setting['form_data'] = \yii\helpers\Json::decode($setting['form_data']) ?: $default['form_data'];
            $setting['payment_type'] = \yii\helpers\Json::decode($setting['payment_type']) ?: $default['payment_type'];
            $setting['goods_poster'] = \yii\helpers\Json::decode($setting['goods_poster']) ?: $default['goods_poster'];
        } else {
            $setting = $default;
        }
        return $setting;
    }
}
