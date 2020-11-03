<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\wxapp\forms\wx_app_config;


use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\wxapp\models\WxappConfig;

class WxAppConfigForm extends Model
{
    public $id;
    public $page;


    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['page'], 'default', 'value' => 1],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '微信配置ID',
        ];
    }

    public function getDetail()
    {
        $detail = WxappConfig::find()->where(['mall_id' => \Yii::$app->mall->id])->asArray()->one();

        if ($detail) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $detail,
                ]
            ];
        }

        return [
            'code' => ApiCode::CODE_ERROR,
            'msg' => '信息未配置',
        ];
    }
}
