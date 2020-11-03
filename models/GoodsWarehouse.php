<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%goods_warehouse}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $name 商品名称
 * @property string $original_price 原价
 * @property string $cost_price 成本价
 * @property string $detail 商品详情，图文
 * @property string $cover_pic 商品缩略图
 * @property string $pic_url 商品轮播图
 * @property string $video_url 商品视频
 * @property string $unit 单位
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 * @property GoodsCatRelation[] $goodsCatRelation
 * @property GoodsCats[] $cats
 * @property Goods[] $goods
 * @property Goods $goodsInfo
 * @property $mchCats
 */
class GoodsWarehouse extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_warehouse}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'name', 'detail', 'pic_url', 'created_at'], 'required'],
            [['mall_id', 'is_delete'], 'integer'],
            [['original_price', 'cost_price'], 'number'],
            [['detail', 'pic_url'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name', 'cover_pic', 'video_url', 'unit'], 'string', 'max' => 255],
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
            'name' => '商品名称',
            'original_price' => '原价',
            'cost_price' => '成本价',
            'detail' => '商品详情，图文',
            'cover_pic' => '商品缩略图',
            'pic_url' => '商品轮播图',
            'video_url' => '商品视频',
            'unit' => '单位',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getGoodsCatRelation()
    {
        return $this->hasMany(GoodsCatRelation::className(), ['goods_warehouse_id' => 'id'])->where(['is_delete' => 0]);
    }

    public function getCats()
    {
        return $this->hasMany(GoodsCats::className(), ['id' => 'cat_id'])
            ->where(['mch_id' => 0])
            ->via('goodsCatRelation');
    }

    public function getMchCats()
    {
        return $this->hasMany(GoodsCats::className(), ['id' => 'cat_id'])
            ->where(['>', 'mch_id', 0])
            ->via('goodsCatRelation');
    }

    public function getGoods()
    {
        return $this->hasMany(Goods::className(), ['goods_warehouse_id' => 'id']);
    }

    public function getGoodsInfo()
    {
        return $this->hasOne(Goods::className(), ['goods_warehouse_id' => 'id'])->where(['sign' => ['', 'mch']]);
    }
}
