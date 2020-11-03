<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\pintuan\forms\mall;


use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\pintuan\jobs\PintuanOrderAddRobotJob;

class OrderRobotForm extends Model
{
    public $robots;
    public $pintuan_order_id;

    public function rules()
    {
        return [
            [['pintuan_order_id'], 'required'],
            [['pintuan_order_id'], 'integer'],
            [['robots'], 'safe']
        ];
    }

    public function save()
    {
        if (is_array($this->robots)) {
            foreach ($this->robots as $robotId) {
                \Yii::$app->queue->delay(0)->push(new PintuanOrderAddRobotJob([
                    'pintuan_order_id' => $this->pintuan_order_id,
                    'robot_id' => $robotId,
                ]));
            }
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '添加成功，添加会有一定延迟，请稍后刷新页面查看'
        ];
    }
}