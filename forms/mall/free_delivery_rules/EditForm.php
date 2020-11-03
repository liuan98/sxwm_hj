<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/12
 * Time: 11:07
 */

namespace app\forms\mall\free_delivery_rules;


use app\core\response\ApiCode;
use app\models\FreeDeliveryRules;
use app\models\Model;

/**
 * @property FreeDeliveryRules $model
 */
class EditForm extends Model
{
    public $model;

    public $price;
    public $detail;
    public $name;

    public function rules()
    {
        return [
            ['price', 'default', 'value' => 0],
            ['price', 'number', 'min' => 0],
            ['detail', 'safe'],
            ['name', 'required'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if ($this->model->isNewRecord) {
            $this->model->is_delete = 0;
        }
        $this->model->detail = \Yii::$app->serializer->encode($this->detail);
        $this->model->price = $this->price;
        $this->model->name = $this->name;

        if ($this->model->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->getErrorResponse($this->model);
        }
    }
}