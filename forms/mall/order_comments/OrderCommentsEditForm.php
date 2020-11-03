<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */
namespace app\forms\mall\order_comments;

use app\core\response\ApiCode;
use app\models\Goods;
use app\models\OrderComments;
use app\models\Model;
 
class OrderCommentsEditForm extends Model
{
    public $id;
    public $order_id;
    public $order_detail_id;
    public $user_id;
    public $score;
    public $content;
    public $pic_url;
    public $is_show;
    public $virtual_user;
    public $virtual_avatar;
    public $virtual_time;
    public $goods_id;
    public $reply_content;
    public $is_anonymous;
    public $sign;

    public function rules()
    {
        return [
            [['score', 'virtual_time'], 'required'],
            [['score', 'is_show', 'id', 'goods_id', 'is_anonymous'], 'integer'],
            [['content', 'sign'], 'string'],
            [['pic_url', 'content', 'reply_content', 'sign', 'virtual_user'], 'default', 'value' => ''],
            [['virtual_time'], 'safe'],
            [['virtual_user', 'virtual_avatar'], 'string', 'max' => 255],
            [['order_id', 'order_detail_id', 'user_id'], 'default', 'value' => 0],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'order_detail_id' => 'Order Detail ID',
            'user_id' => 'User ID',
            'score' => '评分：1=差评，2=中评，3=好',
            'content' => '评价内容',
            'pic_url' => '评价图片',
            'is_show' => '是否显示：0.不显示|1.显示',
            'virtual_user' => '虚拟用户名',
            'virtual_avatar' => '虚拟头像',
            'virtual_time' => '虚拟评价时间',
            'reply_content' => '商家回复内容',
            'is_anonymous' => '是否虚拟评价',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        if ($this->pic_url && count($this->pic_url) > 6) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '图片最多为6张',
            ];
        }

        /** @var Goods $goods */
        $goods = Goods::find()->where(['id' => $this->attributes['goods_id']])->with('goodsWarehouse')->one();
        if (!$goods) {
            throw new \Exception('商品不存在');
        }

        $model = OrderComments::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id,
            'is_virtual' => 1,
            'is_delete' => 0,
        ]);

        if (!$model) {
            $model = new OrderComments();
        }
        $model->attributes = $this->attributes;
        $model->goods_warehouse_id = $goods->goodsWarehouse->id;
        $model->mall_id = \Yii::$app->mall->id;
        $model->mch_id = \Yii::$app->user->identity->mch_id;
        $model->pic_url = json_encode($this->pic_url);
        $model->is_virtual = 1;
        if ($model->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->getErrorResponse($model);
        }
    }
}
