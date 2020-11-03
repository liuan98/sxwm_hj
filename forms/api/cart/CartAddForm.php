<?php

namespace app\forms\api\cart;

use app\core\response\ApiCode;
use app\events\CartEvent;
use app\models\Cart;
use app\models\GoodsAttr;
use app\models\Goods;
use app\models\Model;
use app\models\Order;
use yii\helpers\ArrayHelper;

class CartAddForm extends Model
{
    public $goods_id;
    public $attr;
    public $num;
    public $mch_id;

    public function rules()
    {
        return [
            [['goods_id', 'attr', 'num'], 'required'],
            [['goods_id', 'num', 'attr', 'mch_id'], 'integer'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }

        try {
            Cart::cacheStatusSet(true);
            $goods = Goods::findOne($this->goods_id);
            if (!$goods) {
                throw new \Exception('商品不存在');
            }

            $attr = GoodsAttr::find()->alias('g')->where([
                'g.id' => $this->attr,
                'g.goods_id' => $this->goods_id,
                'g.is_delete' => 0,
            ])->innerJoinwith(['goods o' => function ($query) {
                $query->where([
                    'o.id' => $this->goods_id,
                    'o.mall_id' => \Yii::$app->mall->id,
                    'o.is_delete' => 0,
                    'o.status' => 1,
                ]);
            }])->one();

            if (!$attr) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '商品异常',
                ];
            }

            $this->num = $this->num > $attr->stock ? $attr->stock : $this->num;
            if ($this->num <= 0) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '数量为空或库存为空'
                ];
            }

            $cart = Cart::findOne([
                'user_id' => \Yii::$app->user->id,
                'goods_id' => $this->goods_id,
                'attr_id' => $this->attr,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ]);

            if (!$cart) {
                $cart = new Cart();
                $cart->mall_id = \Yii::$app->mall->id;
                $cart->user_id = \Yii::$app->user->id;
                $cart->goods_id = $this->goods_id;
                $cart->attr_id = $this->attr;
                $cart->num = 0;
                $cart->mch_id = $goods->mch_id;
                $cart->attr_info = \Yii::$app->serializer->encode(ArrayHelper::toArray($attr));
            };
            $cart->num += $this->num;
            if ($cart->save()) {
                Cart::cacheStatusSet(false);
                \Yii::$app->trigger(Cart::EVENT_CART_ADD, new CartEvent(['cartIds' => [$cart->id]]));
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '添加购物车成功',
                ];
            } else {
                return $this->getErrorResponse($cart);
            }
        } catch (\Exception $e) {
            Cart::cacheStatusSet(false);
        }
    }
}
