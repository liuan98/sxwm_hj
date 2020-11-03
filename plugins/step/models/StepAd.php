<?php

namespace app\plugins\step\models;

use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%step_ad}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $unit_id 广告id
 * @property int $site 位置
 * @property int $status 0关闭 1开启
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $deleted_at
 */
class StepAd extends ModelActiveRecord
{
    const TYPE = [[
        'value' => '1',
        'name' => '步数宝首页',
    ],[
        'value' => '2',
        'name' => '挑战底部',
    ],[
        'value' => '3',
        'name' => '排行榜底部',
    ]];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%step_ad}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'created_at', 'deleted_at'], 'required'],
            [['mall_id', 'site', 'status', 'is_delete'], 'integer'],
            [['created_at', 'deleted_at'], 'safe'],
            [['unit_id'], 'string', 'max' => 255],
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
            'unit_id' => '广告id',
            'site' => '位置',
            'status' => '0关闭 1开启',
            'is_delete' => '删除',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
        ];
    }
}
