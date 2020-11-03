<?php

namespace app\plugins\pintuan\models;

use Yii;

/**
 * This is the model class for table "{{%pintuan_goods}}".
 *
 * @property int $id
 * @property int $is_alone_buy 是否允许单独购买
 * @property int $mall_id
 * @property int $goods_id
 * @property string $end_time 拼团结束时间
 * @property int $groups_restrictions 拼团次数限制
 * @property int $is_delete
 * @property string $updated_at
 * @property int $is_sell_well 是否热销
 * @property PintuanGoodsGroups[] $groups
 * @property Goods $goods
 */
class PintuanGoods extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%pintuan_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_alone_buy', 'mall_id', 'goods_id', 'groups_restrictions', 'is_delete', 'is_sell_well'], 'integer'],
            [['mall_id', 'goods_id'], 'required'],
            [['end_time', 'updated_at'], 'safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'is_alone_buy' => '是否允许单独购买',
            'mall_id' => 'Mall ID',
            'goods_id' => 'Goods ID',
            'end_time' => '拼团结束时间',
            'groups_restrictions' => '拼团次数限制',
            'is_delete' => 'Is Delete',
            'updated_at' => 'Updated At',
            'is_sell_well' => '是否热销',
        ];
    }

    public function getGroups()
    {
        return $this->hasMany(PintuanGoodsGroups::className(), ['goods_id' => 'goods_id'])
            ->andWhere(['is_delete' => 0]);
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }

    public function getPtGoodsAttr()
    {
        return $this->hasMany(PintuanGoodsAttr::className(), ['goods_id' => 'goods_id']);
    }
}
