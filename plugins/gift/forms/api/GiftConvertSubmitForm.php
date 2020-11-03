<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: jack_guo
 */

namespace app\plugins\gift\forms\api;

use app\forms\api\order\OrderException;
use app\forms\api\order\OrderGoodsAttr;
use app\models\Goods;
use app\models\Order;
use app\plugins\gift\forms\common\CommonGift;
use app\plugins\gift\models\GiftOrder;
use app\plugins\gift\models\GiftSetting;
use app\plugins\gift\models\GiftUserOrder;

class GiftConvertSubmitForm extends \app\forms\api\order\OrderSubmitForm
{
    public function setPluginData()
    {
        $setting = CommonGift::getSetting();
        $this->setEnablePriceEnable(false)
            ->setSupportPayTypes($setting['payment_type']);
        return $this;
    }

    /**
     * 获取1个或多个订单的数据，按商户划分
     * @return array ['mch_list'=>'商户列表', 'total_price' => '多个订单的总金额（含运费）']
     * @throws OrderException
     * @throws \yii\db\Exception
     */
    public function getAllData()
    {
        $listData = $this->getMchListData($this->form_data['list']);
        foreach ($listData as &$mchItem) {
            $this->checkGoodsBuyLimit($mchItem['goods_list']);
            $formMchItem = $mchItem['form_data'];

            $mchItem['express_price'] = price_format(0);
            $mchItem['remark'] = isset($mchItem['form_data']['remark'])
                ? $mchItem['form_data']['remark'] : null;
            $mchItem['order_form_data'] = isset($mchItem['form_data']['order_form'])
                ? $mchItem['form_data']['order_form'] : null;

            $totalGoodsPrice = 0;
            $totalGoodsOriginalPrice = 0;
            foreach ($mchItem['goods_list'] as $goodsItem) {
                $totalGoodsPrice += $goodsItem['total_price'];
                $totalGoodsOriginalPrice += $goodsItem['total_original_price'];
            }
            $mchItem['total_goods_price'] = price_format($totalGoodsPrice);
            $mchItem['total_goods_original_price'] = price_format($totalGoodsOriginalPrice);

            $mchItem = $this->setMemberDiscountData($mchItem);
            $mchItem = $this->setCouponDiscountData($mchItem, $formMchItem);
            $mchItem = $this->setIntegralDiscountData($mchItem, $formMchItem);

            $mchItem = $this->setDeliveryData($mchItem, $formMchItem);
            $mchItem = $this->setStoreData($mchItem, $formMchItem, $this->form_data);
            $mchItem = $this->setExpressData($mchItem);

            $totalPrice = price_format($mchItem['total_goods_price'] + $mchItem['express_price']);
            $mchItem['total_price'] = $this->setTotalPrice($totalPrice);

            $mchItem = $this->setGoodsForm($mchItem);
        }


        $total_price = 0;
        foreach ($listData as &$item) {
            $total_price += $item['total_price'];
        }

        $hasZiti = false;
        foreach ($listData as &$mchItem) {
            if (isset($mchItem['delivery']) && $mchItem['delivery']['send_type'] == 'offline') {
                $hasZiti = true;
                break;
            }
        }

        $allZiti = true;
        foreach ($listData as &$mchItem) {
            if (isset($mchItem['delivery']) && $mchItem['delivery']['send_type'] != 'offline') {
                $allZiti = false;
                break;
            }
        }
        $address = $this->getAddress();

        $addressEnable = true;
        foreach ($listData as &$mchItem) { // 检查区域允许购买
            $addressEnable = $this->getAddressEnable($address, $mchItem);
            if ($addressEnable == false) {
                break;
            }
        }
        $priceEnable = true;

        // 获取上一次的自提订单
        if ($allZiti) {
            if (!$address) {
                /** @var Order $order */
                $order = Order::find()->where(['user_id' => \Yii::$app->user->id, 'send_type' => 1, 'is_delete' => 0])->orderBy(['created_at' => SORT_DESC])->one();
                if ($order) {
                    $address = [
                        'name' => $order->name,
                        'mobile' => $order->mobile
                    ];
                }
            }
            // 下单预览记住用户更改的自提信息
            if (isset($this->form_data['address'])) {
                $address = $this->form_data['address'];
            }
        }
        $hasCity = false;
        foreach ($listData as &$mchItem) {
            if (isset($mchItem['delivery']) && $mchItem['delivery']['send_type'] == 'city') {
                $hasCity = true;
                break;
            }
        }

        return [
            'mch_list' => $listData,
            'total_price' => price_format($total_price),
            'price_enable' => $priceEnable,
            'address' => $hasCity ? ($address->longitude && $address->latitude ? $address : null) : $address,
            'address_enable' => $addressEnable,
            'has_ziti' => $hasZiti,
            'custom_currency_all' => $this->getcustomCurrencyAll($listData),
            'allZiti' => $allZiti,
            'hasCity' => $hasCity,
        ];
    }


    protected function getGoodsItemData($item)
    {
        /** @var Goods $goods */
        $goods = Goods::find()->with('goodsWarehouse')->where([
            'id' => $item['id'],
            'mall_id' => \Yii::$app->mall->id,
            'status' => 1,
            'is_delete' => 0,
        ])->one();

        if (!$goods) {
            throw new OrderException('商品不存在或已下架。');
        }

        // 其他商品特有判断
        $this->checkGoods($goods, $item);

        //判断是否有资格兑换，goods_list中传入gift_order_id
        $user_gift_info = GiftOrder::find()->alias('go')->leftJoin(['guo' => GiftUserOrder::tableName()], 'guo.id = go.user_order_id')
            ->andWhere(['go.user_order_id' => $item['user_order_id'], 'go.order_id' => '', 'go.is_delete' => 0,
                'guo.user_id' => \Yii::$app->user->id, 'guo.is_receive' => 0, 'guo.is_delete' => 0])
            ->andWhere(['go.goods_id' => $item['id'], 'go.goods_attr_id' => $item['goods_attr_id']])
            ->select(['go.goods_id', 'go.goods_attr_id', 'go.num', 'guo.is_turn', 'guo.is_receive', 'guo.token'])->asArray()->one();
        if (empty($user_gift_info)) {
            throw new OrderException('非法兑奖，请核对是否有中奖。');
        }
        $order = Order::find()->where(['token' => $user_gift_info['token']])->andWhere(['<>', 'cancel_status', '1'])->asArray()->one();
//        var_dump($order);die;
        if (isset($order)) {
//            return [
//                'code' => 11,
//                'msg' => '地址已填写，请去待支付订单支付后领取。',
//                'order_id' => $order['id'],
//            ];
            throw new OrderException('地址已填写，请去待支付订单支付后领取。');
        }
        if ($user_gift_info['goods_id'] != $item['id'] || $user_gift_info['goods_attr_id'] != $item['goods_attr_id']
            || $user_gift_info['num'] != $item['num']) {
            throw new OrderException('非法兑奖，兑奖信息有误。');
        }
        if ($user_gift_info['is_receive'] == 1) {
            throw new OrderException('该奖品已兑换。');
        }
        if ($user_gift_info['is_turn'] == 1) {
            throw new OrderException('该奖品已转赠。');
        }
        if ($item['num'] > $user_gift_info['num']) {
            throw new OrderException('兑换数量不可超过领取数量。');
        }

        try {
            /** @var OrderGoodsAttr $goodsAttr */
            $goodsAttr = $this->getGoodsAttr($item['goods_attr_id'], $goods);
            $goodsAttr->number = $item['num'];
        } catch (\Exception $exception) {
            throw new OrderException('无法查询商品`' . $goods->name . '`的规格信息。');
        }

        $attrList = $goods->signToAttr($goodsAttr->sign_id);
        $itemData = [
            'id' => $goods->id,
            'name' => $goods->goodsWarehouse->name,
            'num' => $item['num'],
            'forehead_integral' => $goods->forehead_integral,
            'forehead_integral_type' => $goods->forehead_integral_type,
            'accumulative' => $goods->accumulative,
            'pieces' => $goods->pieces,
            'forehead' => $goods->forehead,
            'freight_id' => $goods->freight_id,
            'unit_price' => price_format($goodsAttr->original_price),
            'total_original_price' => price_format($goodsAttr->original_price * $item['num']),
            'total_price' => price_format(0),//兑奖无需付款
            'goods_attr' => $goodsAttr,
            'attr_list' => $attrList,
            'discounts' => $goodsAttr->discount,
            'member_discount' => price_format(0),
            'cover_pic' => $goods->goodsWarehouse->cover_pic,
            'is_level_alone' => $goods->is_level_alone,
            // 规格自定义货币 例如：步数宝的步数币
            'custom_currency' => $this->getCustomCurrency($goods, $goodsAttr),
            'is_level' => $goods->is_level,
            'goods_warehouse_id' => $goods->goods_warehouse_id,
            'sign' => $goods->sign,
            'form_id' => $goods->form_id,
        ];
        return $itemData;
    }


    /**
     * 检查购买的商品数量是否超出限制及库存（购买数量含以往的订单）
     * @param array $goodsList [ ['id','name',''] ]
     * @throws OrderException
     */
    private function checkGoodsBuyLimit($goodsList)
    {
        foreach ($goodsList as $goods) {
            if ($goods['num'] <= 0) {
                throw new OrderException('兑换礼物' . $goods['name'] . '数量不能小于1');
            }
        }
    }

    /**
     * 商品库存操作
     * @param OrderGoodsAttr $goodsAttr
     * @param int $subNum
     * @param array $goodsItem
     */
    public function subGoodsNum($goodsAttr, $subNum, $goodsItem)
    {
        return;
    }

    protected function getToken()
    {
        $user_order_info = GiftUserOrder::findOne(['id' => $this->form_data['list'][0]['goods_list'][0]['user_order_id']]);
        $user_order_info->token = parent::getToken(); // TODO: Change the autogenerated stub
        if (!$user_order_info->save()) {
            throw new OrderException($user_order_info->errors[0]);
        }
        return $user_order_info->token;
    }

    protected function getSendType($mchItem)
    {
        $setting = GiftSetting::find()->where([
            'mall_id' => \Yii::$app->mall->id,
        ])->one();
        return $setting['send_type'] ? \Yii::$app->serializer->decode($setting['send_type']) : ['express'];
    }

}
