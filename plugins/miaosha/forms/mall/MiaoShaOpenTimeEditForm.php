<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\miaosha\forms\mall;


use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\miaosha\models\MiaoshaSetting;

class MiaoShaOpenTimeEditForm extends Model
{
    public $data;

    public function rules()
    {
        return [
            [['data'], 'safe'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $model = MiaoshaSetting::find()->where(['mall_id' => \Yii::$app->mall->id])->one();

            if (!$model) {
                $model = new MiaoshaSetting();
                $model->mall_id = \Yii::$app->mall->id;
                $model->payment_type = \Yii::$app->serializer->encode([]);
                $model->goods_poster = \Yii::$app->serializer->encode([]);
            }

            $model->open_time = \Yii::$app->serializer->encode($this->data ?: []);
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
