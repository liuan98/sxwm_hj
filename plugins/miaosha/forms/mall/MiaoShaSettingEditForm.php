<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\miaosha\forms\mall;


use app\core\response\ApiCode;
use app\forms\common\CommonOptionP;
use app\models\Model;
use app\plugins\miaosha\models\MiaoshaSetting;

class MiaoShaSettingEditForm extends Model
{
    public $id;
    public $over_time;
    public $is_share;
    public $is_sms;
    public $is_mail;
    public $is_print;
    public $is_territorial_limitation;
    public $send_type;
    public $goods_poster;
    public $open_time;

    public function rules()
    {
        return [
            [['id', 'is_share', 'is_sms', 'is_mail', 'is_print', 'is_territorial_limitation', 'over_time'], 'integer'],
            [['is_share', 'is_sms', 'is_mail', 'is_print', 'is_territorial_limitation'], 'required'],
            [['goods_poster'], 'trim'],
            [['open_time', 'send_type'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'over_time' => '未支付订单取消时间',
            'trim' => '自定义海报',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if ($this->id) {
                $model = MiaoshaSetting::findOne($this->id);

                if (!$model) {
                    throw new \Exception('设置异常');
                }
            } else {
                $model = new MiaoshaSetting();
                $model->mall_id = \Yii::$app->mall->id;
                $model->open_time = \Yii::$app->serializer->encode([]);
            }

            if ($this->over_time < 0) {
                throw new \Exception('未支付订单时间不能小于0');
            }

            if (!$this->send_type || empty($this->send_type)) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '请先填写发货方式'
                ];
            }

            $model->over_time = $this->over_time;
            $model->is_mail = $this->is_mail;
            $model->is_print = $this->is_print;
            $model->is_share = $this->is_share;
            $model->is_sms = $this->is_sms;
            $model->is_territorial_limitation = $this->is_territorial_limitation;
            $model->send_type = \Yii::$app->serializer->encode($this->send_type);
            $model->goods_poster = \Yii::$app->serializer->encode((new CommonOptionP())->saveEnd($this->goods_poster));
            $model->payment_type = \Yii::$app->serializer->encode([]);
            $model->open_time = \Yii::$app->serializer->encode($this->open_time ?: []);
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
}
