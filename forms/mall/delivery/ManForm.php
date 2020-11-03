<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/9/27
 * Time: 10:44
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\forms\mall\delivery;


use app\core\response\ApiCode;
use app\forms\common\CommonDelivery;
use app\models\CityDeliveryman;
use app\models\Model;

class ManForm extends Model
{
    public $id;
    public $name;
    public $mobile;

    public function rules()
    {
        return [
            [['name', 'mobile'], 'required'],
            [['name', 'mobile'], 'trim'],
            [['name', 'mobile'], 'string'],
            [['id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '配送员姓名',
            'mobile' => '配送员联系电话',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $commonDelivery = CommonDelivery::getInstance();
            $model = $commonDelivery->getManOne($this->id);
            if (!$model) {
                $model = new CityDeliveryman();
                $model->mall_id = \Yii::$app->mall->id;
                $model->mch_id = 0;
            }
            $model->is_delete = 0;
            $model->name = $this->name;
            $model->mobile = $this->mobile;
            if (!$model->save()) {
                throw new \Exception($this->getErrorMsg($model));
            } else {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '保存成功',
                    'data' => [
                        'model' => $model
                    ]
                ];
            }
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }

    public function search()
    {
        $list = CommonDelivery::getInstance()->getManList();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $list
            ]
        ];
    }

    public function destroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $model = CommonDelivery::getInstance()->getManOne($this->id);
            if (!$model) {
                throw new \Exception('提交数据错误');
            }
            $model->is_delete = 1;
            if (!$model->save()) {
                throw new \Exception($this->getErrorMsg($model));
            } else {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '删除成功'
                ];
            }
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
