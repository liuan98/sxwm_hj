<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */
namespace app\plugins\step\forms\mall;

use app\models\Model;
use app\core\response\ApiCode;
use app\plugins\step\models\StepAd;

class AdEditForm extends Model
{
    public $unit_id;
    public $site;
    public $status;
    public $id;

    public function rules()
    {
        return [
            [['status', 'site', 'unit_id'], 'required'],
            [['unit_id'], 'string'],
            [['site', 'status', 'id'], 'integer'],
        ];
    }

    //GET
    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        
        $model = StepAd::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id,
        ]);
        if (!$model) {
            $model = new StepAd();
        }

        foreach ($this->attributes as $index => $item) {
            if ($item) {
                $model->$index = $item;
            }
        }
        $model->attributes = $this->attributes;
        $model->mall_id = \Yii::$app->mall->id;
        if ($model->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->getErrorResponse($model);
        }
    }
}
