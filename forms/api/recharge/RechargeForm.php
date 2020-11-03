<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\forms\api\recharge;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\Recharge;

class RechargeForm extends Model
{
    public $pay_price;
    public $send_price;

    public function rules()
    {
        return [
            [['pay_price', 'send_price'], 'double']
        ];
    }

    public function getIndex()
    {
        $list = Recharge::find()
            ->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0
            ])->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list
            ]
        ];
    }
}
