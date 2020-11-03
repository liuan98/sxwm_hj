<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%delivery}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property int $express_id 快递公司id
 * @property string $customer_account 电子面单客户账号
 * @property string $customer_pwd 电子面单密码
 * @property string $month_code 月结编码
 * @property string $outlets_name 网点名称
 * @property string $outlets_code 网点编码
 * @property string $company 发件人公司
 * @property string $name 发件人名称
 * @property string $tel 发件人电话
 * @property string $mobile 发件人手机
 * @property string $zip_code 发件人邮政编码
 * @property string $province 发件人省
 * @property string $city 发件人市
 * @property string $district 发件人区
 * @property string $address 发件人详细地址
 * @property string $template_size 快递鸟电子面单模板规格
 * @property string $is_sms 是否订阅短信
 * @property string $is_goods 是否商城
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class Delivery extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%delivery}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'name', 'province', 'city', 'district', 'address', 'created_at', 'updated_at',
                'deleted_at'], 'required'],
            [['mall_id', 'express_id', 'is_delete', 'mch_id', 'is_sms', 'is_goods'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['customer_account', 'customer_pwd', 'month_code', 'outlets_name', 'outlets_code', 'company', 'name',
                'tel', 'mobile', 'zip_code', 'province', 'city', 'district', 'address', 'template_size'], 'string', 'max' => 255],
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
            'express_id' => '快递公司id',
            'customer_account' => '电子面单客户账号',
            'customer_pwd' => '电子面单密码',
            'month_code' => '月结编码',
            'outlets_name' => '网点名称',
            'outlets_code' => '网点编码',
            'company' => '发件人公司',
            'name' => '发件人名称',
            'tel' => '发件人电话',
            'mobile' => '发件人手机',
            'zip_code' => '发件人邮政编码',
            'province' => '发件人省',
            'city' => '发件人市',
            'district' => '发件人区',
            'address' => '发件人详细地址',
            'template_size' => '快递鸟电子面单模板规格',
            'is_sms' => '是否订阅短信',
            'is_delete' => '删除',
            'is_goods' => '是否商品信息',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }
}
