<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\pintuan\forms\mall;


use app\core\response\ApiCode;
use app\forms\common\CommonOptionP;
use app\models\Model;
use app\plugins\pintuan\models\PintuanSetting;

class PinTuanSettingEditForm extends Model
{
    public $id;
    public $is_share;
    public $is_sms;
    public $is_mail;
    public $is_print;
    public $is_territorial_limitation;
    public $rules;
    public $send_type;
    public $payment_type;
    public $goods_poster;

    public function rules()
    {
        return [
            [['id', 'is_share', 'is_sms', 'is_mail', 'is_print', 'is_territorial_limitation'], 'integer'],
            [['is_share', 'is_sms', 'is_mail', 'is_print', 'is_territorial_limitation'], 'required'],
            [['rules', 'payment_type', 'goods_poster', 'send_type'], 'safe']
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $this->checkData();
            if ($this->id) {
                $model = PintuanSetting::findOne($this->id);

                if (!$model) {
                    throw new \Exception('拼团设置异常');
                }
            } else {
                $model = new PintuanSetting();
                $model->mall_id = \Yii::$app->mall->id;
                $model->advertisement = \Yii::$app->serializer->encode([]);
            }

            if (!$this->payment_type || empty($this->payment_type)) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '请先填写支付方式'
                ];
            }

            if (!$this->send_type || empty($this->send_type)) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '请先填写发货方式'
                ];
            }

            $model->is_mail = $this->is_mail;
            $model->is_print = $this->is_print;
            $model->is_share = $this->is_share;
            $model->is_sms = $this->is_sms;
            $model->is_territorial_limitation = $this->is_territorial_limitation;
            $model->rules = $this->rules ? \Yii::$app->serializer->encode($this->rules) : \Yii::$app->serializer->encode([]);
            $model->payment_type = \Yii::$app->serializer->encode($this->payment_type);
            $model->send_type = \Yii::$app->serializer->encode($this->send_type);
            $model->goods_poster = \Yii::$app->serializer->encode((new CommonOptionP())->saveEnd($this->goods_poster));
            $res = $model->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($model));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
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

    private function checkData()
    {
        if (!$this->payment_type || empty($this->payment_type)) {
            throw new \Exception('请填写支付方式');
        }
        if ($this->rules) {
            foreach ($this->rules as $rule) {
                if (isset($rule['title']) && !$rule['title']) {
                    throw new \Exception('请填写规则标题');
                }

                if (isset($rule['content']) && !$rule['content']) {
                    throw new \Exception('请填写规则内容');
                }
            }
        }
    }
}
