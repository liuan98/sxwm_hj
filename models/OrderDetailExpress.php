<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%order_detail_express}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property int $send_type;
 * @property string $express
 * @property string $express_no
 * @property string $merchant_remark 商家留言
 * @property string $express_content 物流内容
 * @property string $customer_name 京东物流编码
 * @property int $is_delete
 * @property int $order_id
 * @property int $express_single_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property OrderDetailExpressRelation $expressRelation
 */
class OrderDetailExpress extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_detail_express}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'mch_id', 'created_at', 'updated_at', 'deleted_at', 'send_type'], 'required'],
            [['mall_id', 'mch_id', 'is_delete', 'send_type', 'order_id', 'express_single_id'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['express'], 'string', 'max' => 65],
            [['express_no', 'merchant_remark', 'express_content', 'customer_name'], 'string', 'max' => 255],
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
            'mch_id' => 'Mch ID',
            'express' => 'Express',
            'send_type' => '1.快递|2.其它方式',
            'express_no' => 'Express No',
            'merchant_remark' => '商家留言',
            'express_content' => '物流内容',
            'customer_name' => '京东物流编码',
            'is_delete' => 'Is Delete',
            'order_id' => 'Order Id',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'express_single_id' => '电子面单ID',
        ];
    }


    public function getExpressRelation()
    {
        return $this->hasMany(OrderDetailExpressRelation::className(), ['order_detail_express_id' => 'id'])->andWhere(['is_delete' => 0]);
    }

    public function getExpressSingle() {
        return $this->hasOne(OrderExpressSingle::className(), ['id' => 'express_single_id'])->andWhere(['is_delete' => 0]);
    }
}
