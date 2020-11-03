<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\forms\mall\service;


use app\core\response\ApiCode;
use app\models\GoodsServices;
use app\models\Model;

class ServiceEditForm extends Model
{
    public $name;
    public $remark;
    public $is_default;
    public $sort;
    public $id;

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['is_default', 'id', 'sort'], 'integer'],
            [['remark'], 'string'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if ($this->id) {
                $service = GoodsServices::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);

                if (!$service) {
                    return [
                        'code' => ApiCode::CODE_ERROR,
                        'msg' => '数据异常,该条数据不存在',
                    ];
                }
            } else {
                $service = new GoodsServices();
                $service->mall_id = \Yii::$app->mall->id;
                $service->mch_id = \Yii::$app->user->identity->mch_id;
            }

            $service->is_default = $this->is_default;
            $service->name = $this->name;
            $service->sort = $this->sort;
            $service->remark = $this->remark;
            $res = $service->save();

            if ($res) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '保存成功',
                ];
            }

            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '保存失败',
            ];


        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
