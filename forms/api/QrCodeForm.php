<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\forms\api;


use app\core\response\ApiCode;
use app\models\Model;
use app\models\QrCodeParameter;

class QrCodeForm extends Model
{
    public $token;

    public function rules()
    {
        return [
            [['token'], 'required'],
            [['token'], 'string'],
        ];
    }

    public function getParameter()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $detail = QrCodeParameter::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'token' => $this->token
        ])->one();

        if ($detail) {
            $detail['data'] = $detail['data'] ? \Yii::$app->serializer->decode($detail['data']) : '';
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $detail
            ]
        ];
    }
}
