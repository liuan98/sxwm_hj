<?php

namespace app\plugins\pintuan\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\CommonCats;
use app\models\GoodsCats;
use app\models\Model;
use app\plugins\pintuan\models\PintuanCats;

class CatsForm extends Model
{
    public $id;
    public $sort;
    public $cat_id;
    public $keyword;

    public function rules()
    {
        return [
            [['id', 'sort', 'cat_id'], 'integer'],
            [['sort'], 'default', 'value' => 0],
            [['keyword'], 'string']
        ];
    }

    public function attributeLabels()
    {
        return [
            'sort' => '排序',
            'cat_id' => '分类ID'
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $query = PintuanCats::find()->alias('pc')->where([
            'pc.mall_id' => \Yii::$app->mall->id,
            'pc.is_delete' => 0,
        ])->joinWith(['cats c' => function ($query) {
            $query->andWhere(['c.is_delete' => 0]);
        }]);

        if ($this->keyword) {
            $goodsIds = GoodsCats::find()->where(['like', 'name', $this->keyword])->select('id');
            $query->andWhere(['pc.cat_id' => $goodsIds]);
        }

        $list = $query->orderBy('pc.sort ASC, pc.id DESC')->page($pagination)->asArray()->all();
        $cats = CommonCats::getAllCats();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '获取成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
                'cats' => $cats,
            ]
        ];
    }

    //SAVE
    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $model = PintuanCats::findOne([
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
            'cat_id' => $this->cat_id
        ]);
        if ($model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '分类已存在',
            ];
        }

        $model = new PintuanCats();
        $model->mall_id = \Yii::$app->mall->id;
        $model->attributes = $this->attributes;
        $model->is_delete = 0;
        if ($model->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功'
            ];
        } else {
            return $this->getErrorResponse($model);
        }
    }

    //editSort
    public function editSort()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $model = PintuanCats::findOne([
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
        $model->sort = $this->sort;
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '更新成功'
        ];
    }

    //DELETE
    public function destroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $model = PintuanCats::findOne([
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
}
