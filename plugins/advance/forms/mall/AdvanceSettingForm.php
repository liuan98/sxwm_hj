<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/4
 * Time: 9:44
 */

namespace app\plugins\advance\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\CommonOptionP;
use app\models\Model;
use app\plugins\advance\forms\common\CommonOption;
use app\plugins\advance\forms\common\SettingForm;
use app\plugins\advance\models\AdvanceSetting;

class AdvanceSettingForm extends Model
{
    public $id;
    public $is_share;
    public $is_sms;
    public $is_mail;
    public $is_print;
    public $is_territorial_limitation;
    public $send_type;
    public $payment_type;
    public $deposit_payment_type;
    public $goods_poster;
    public $over_time;
    public $is_advance;

    public function rules()
    {
        return [
            [['id', 'is_share', 'is_sms', 'is_mail', 'is_print', 'is_territorial_limitation', 'over_time', 'is_advance'], 'integer'],
            [['is_share', 'is_sms', 'is_mail', 'is_print', 'is_territorial_limitation',
                'payment_type', 'deposit_payment_type'], 'required'],
            [['payment_type', 'goods_poster', 'deposit_payment_type', 'send_type',], 'safe'],
            [['is_advance',], 'default', 'value' => 1]
        ];
    }

    public function attributeLabels()
    {
        return [
            'payment_type' => '尾款支付方式',
            'deposit_payment_type' => '定金支付方式',
            'send_type' => '发货方式',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $this->checkData();
            $setting = AdvanceSetting::find()->where(['mall_id' => \Yii::$app->mall->id])->one();

            if (!$setting) {
                $setting = new AdvanceSetting();
                $setting->mall_id = \Yii::$app->mall->id;
            }

            $setting->is_share = $this->is_share;
            $setting->is_sms = $this->is_sms;
            $setting->is_mail = $this->is_mail;
            $setting->is_print = $this->is_print;
            $setting->is_territorial_limitation = $this->is_territorial_limitation;
            $setting->send_type = \Yii::$app->serializer->encode($this->send_type);
            $setting->goods_poster = \Yii::$app->serializer->encode((new CommonOptionP())->saveEnd($this->goods_poster));
            $setting->payment_type = \Yii::$app->serializer->encode($this->payment_type);
            $setting->deposit_payment_type = \Yii::$app->serializer->encode($this->deposit_payment_type);
            $setting->over_time = $this->over_time;
            $setting->is_advance = $this->is_advance;
            $res = $setting->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($setting));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    public function checkData()
    {
        if (!$this->payment_type || empty($this->payment_type)) {
            throw new \Exception('请填写支付方式');
        }
    }

    public function getSetting()
    {
        $setting = (new SettingForm())->search();
        $setting['goods_poster'] = (new CommonOptionP())->poster($setting['goods_poster'], CommonOption::getPosterDefault());
        $setting['goods_poster']['price']['text'] = CommonOption::getPosterDefault()['price']['text'];

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'setting' => $setting
            ]
        ];
    }
}