<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\integral_mall\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\CommonOptionP;
use app\models\Model;
use app\plugins\integral_mall\models\IntegralMallSetting;

class IntegralMallEditForm extends Model
{
    public $id;
    public $desc;
    public $is_share;
    public $is_sms;
    public $is_mail;
    public $is_print;
    public $is_territorial_limitation;
    public $send_type;
    public $payment_type;
    public $goods_poster;

    public function rules()
    {
        return [
            [['id', 'is_share', 'is_sms', 'is_mail', 'is_print', 'is_territorial_limitation'], 'integer'],
            [['is_share', 'is_sms', 'is_mail', 'is_print', 'is_territorial_limitation',
                'payment_type'], 'required'],
            [['payment_type', 'desc', 'goods_poster', 'send_type'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'desc' => "积分说明"
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $this->checkData();
            $setting = IntegralMallSetting::find()->where(['mall_id' => \Yii::$app->mall->id])->one();

            if (!$setting) {
                $setting = new IntegralMallSetting();
                $setting->mall_id = \Yii::$app->mall->id;
            }

            $setting->is_share = $this->is_share;
            $setting->is_sms = $this->is_sms;
            $setting->is_mail = $this->is_mail;
            $setting->is_print = $this->is_print;
            $setting->is_territorial_limitation = $this->is_territorial_limitation;
            $setting->desc = $this->desc ? \Yii::$app->serializer->encode($this->desc) : \Yii::$app->serializer->encode([]);
            $setting->send_type = \Yii::$app->serializer->encode($this->send_type);
            $setting->goods_poster = \Yii::$app->serializer->encode((new CommonOptionP())->saveEnd($this->goods_poster));
            $setting->payment_type = \Yii::$app->serializer->encode($this->payment_type);
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
        if ($this->desc) {
            foreach ($this->desc as $key => $item) {
                if (!$item['title']) {
                    throw new \Exception('请完善积分说明标题');
                }
                if (!$item['content']) {
                    throw new \Exception('请完善积分说明内容');
                }
            }
        }
    }
}
