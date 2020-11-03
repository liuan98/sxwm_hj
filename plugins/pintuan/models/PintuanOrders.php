<?php

namespace app\plugins\pintuan\models;

use Yii;

/**
 * This is the model class for table "{{%pintuan_orders}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $preferential_price 团长优惠
 * @property string $success_time 成团时间
 * @property int $status 0.待付款|1.拼团中|2.拼团成功|3.拼团失败|4.未退款
 * @property int $people_num 成团所需人数
 * @property int $pintuan_time 拼团限时(小时)
 * @property int $pintuan_goods_groups_id 阶梯团ID
 * @property int $goods_id 阶梯团ID
 * @property string $created_at
 * @property string $updated_at
 * @property PintuanOrderRelation $orderRelation
 * @property Goods $goods
 */
class PintuanOrders extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%pintuan_orders}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['preferential_price', 'people_num', 'pintuan_time', 'pintuan_goods_groups_id',
                'created_at', 'updated_at', 'goods_id', 'mall_id'], 'required'],
            [['preferential_price'], 'number'],
            [['success_time', 'created_at', 'updated_at'], 'safe'],
            [['status', 'people_num', 'pintuan_time', 'pintuan_goods_groups_id', 'goods_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => '商城 ID',
            'preferential_price' => '团长优惠',
            'success_time' => '成团时间',
            'status' => '0.待付款|1.拼团中|2.拼团成功|3.拼团失败|4.未退款',
            'people_num' => '成团所需人数',
            'pintuan_time' => '拼团限时(小时)',
            'pintuan_goods_groups_id' => '阶梯团ID',
            'goods_id' => '商品 ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }

    public function getOrderRelation()
    {
        return $this->hasMany(PintuanOrderRelation::className(), ['pintuan_order_id' => 'id']);
    }
}
