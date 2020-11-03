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

class AdForm extends Model
{
    public $id;
    public $status;

    public function rules()
    {
        return [
            [['id', 'status'], 'integer']
        ];
    }

    //GET
    public function getList()
    {
        $query = StepAd::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ]);
        $query->keyword($this->id, ['id' => $this->id]);
        $list = $query->orderBy('id desc')->page($pagination)->asArray()->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'pagination' => $pagination,
                'list' => $list,
                'select' => StepAd::TYPE,
            ],
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };
        $list = StepAd::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'id' => $this->id,
            ])->asArray()->one();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'select' => StepAd::TYPE,
            ]
        ];
    }
    public function destroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = StepAd::findOne([
            'id' => $this->id,
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据不存在或已经删除',
            ];
        }
        $model->is_delete = 1;
        $model->deleted_at = date('Y-m-d H:i:s');
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '删除成功'
        ];
    }

    public function editStatus()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = StepAd::findOne([
            'id' => $this->id,
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
        ]);

        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据不存在或已经删除',
            ];
        }
        $model->status = $this->status;
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '切换成功'
        ];
    }
}
