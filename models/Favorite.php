<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%favorite}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $goods_id
 * @property string $created_at
 * @property int $is_delete
 * @property string $deleted_at
 * @property string $updated_at
 */
class Favorite extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%favorite}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'goods_id', 'is_delete'], 'integer'],
            [['created_at', 'deleted_at', 'updated_at'], 'safe'],
            [['updated_at'], 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => 'Mall ID',
            'user_id' => 'User ID',
            'goods_id' => 'Goods ID',
            'created_at' => 'Created At',
            'is_delete' => 'Is Delete',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @param $mall_id
     * @param $user_id
     * @param $goods_id
     * @return bool
     * @throws \Exception
     */
    public static function createModel($mall_id, $user_id, $goods_id)
    {
        $exists = Goods::find()->where(['mall_id' => $mall_id, 'id' => $goods_id, 'is_delete' => 0])->exists();

        if (!$exists) {
            throw new \Exception('商品不存在');
        }
        $model = static::findOne([
            'mall_id' => $mall_id, 'user_id' => $user_id, 'goods_id' => $goods_id
        ]);

        if (!$model) {
            $model = new static();
            $model->mall_id = $mall_id;
            $model->user_id = $user_id;
            $model->goods_id = $goods_id;
        } else {
            if ($model->is_delete == 0) {
                throw new \Exception('已经收藏过啦！');
            }
        }

        $model->is_delete = 0;
        return $model->save();
    }

    public static function deleteModel($mall_id, $user_id, $goods_id)
    {
        $model = static::findOne([
            'mall_id' => $mall_id, 'user_id' => $user_id, 'goods_id' => $goods_id
        ]);

        if ($model) {
            $model->is_delete = 1;
            return $model->save();
        } else {
            return true;
        }
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }
}
