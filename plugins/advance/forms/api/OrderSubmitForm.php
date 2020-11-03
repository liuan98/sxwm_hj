<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: zbj
 */

namespace app\plugins\advance\forms\api;


use app\forms\api\order\OrderException;
use app\forms\api\order\OrderGoodsAttr;
use app\models\Coupon;
use app\models\CouponCatRelation;
use app\models\CouponGoodsRelation;
use app\models\Goods;
use app\models\GoodsCatRelation;
use app\models\GoodsCats;
use app\models\MallMembers;
use app\models\Order;
use app\models\User;
use app\models\UserCoupon;
use app\models\UserIdentity;
use app\plugins\advance\forms\common\SettingForm;
use app\plugins\advance\models\AdvanceGoods;
use app\plugins\advance\models\AdvanceOrder;
use app\plugins\advance\Plugin;

class OrderSubmitForm extends \app\forms\api\order\OrderSubmitForm
{
    public function setPluginData()
    {
        $setting = (new SettingForm())->search();
        $this->setSign((new Plugin())->getName())->setEnablePriceEnable(false)
            ->setSupportPayTypes($setting['payment_type'])
            ->setEnableAddressEnable($setting['is_territorial_limitation'] ? true : false);
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
            $deposit = 0;
            $swell_deposit = 0;
            $preferential_price = 0;
            foreach ($mchItem['goods_list'] as $goodsItem) {
                $totalGoodsPrice += $goodsItem['total_price'];
                $totalGoodsOriginalPrice += $goodsItem['total_original_price'];
                $deposit += bcmul($goodsItem['deposit'], $goodsItem['num']);
                $swell_deposit += bcmul($goodsItem['swell_deposit'], $goodsItem['num']);
                $preferential_price += bcmul($goodsItem['preferential_price'], $goodsItem['num']);
            }
            $mchItem['total_goods_price'] = price_format($totalGoodsPrice);
            $mchItem['total_goods_original_price'] = price_format($totalGoodsOriginalPrice);

            $mchItem = $this->setMemberDiscountData($mchItem);
            $mchItem = $this->setCouponDiscountData($mchItem, $formMchItem);
            $mchItem = $this->setIntegralDiscountData($mchItem, $formMchItem);

            $mchItem = $this->setDeliveryData($mchItem, $formMchItem);
            $mchItem = $this->setStoreData($mchItem, $formMchItem, $this->form_data);
            $mchItem = $this->setExpressData($mchItem);

            if ($mchItem['mch']['id'] == 0) {
                $mchItem = $this->setVipDiscountData($mchItem);
            }

            $totalPrice = price_format($mchItem['total_goods_price'] + $mchItem['express_price']);
            $mchItem['total_price'] = $this->setTotalPrice($totalPrice);

            if ($preferential_price > 0) {
                $mchItem['insert_rows'][] = [
                    'title' => '活动优惠',
                    'value' => '-￥' . $preferential_price
                ];
            }
//            $mchItem['insert_rows'][] = [
//                'title' => '定金',
//                'value' => '￥' . $deposit
//            ];
//            $mchItem['insert_rows'][] = [
//                'title' => '膨胀金',
//                'value' => '-￥' . $swell_deposit
//            ];
            $mchItem['insert_rows'][] = [
                'title' => '定金抵扣',
                'value' => '-￥' . $swell_deposit
            ];
            // $mchItem = $this->setOrderForm($mchItem);
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
        if ($allZiti) {
            $priceEnable = true;
        } else {
            foreach ($listData as &$mchItem) { // 检查是否达到起送规则
                $priceEnable = $this->getPriceEnable(price_format($total_price), $address, $mchItem);
                if ($priceEnable == false) {
                    break;
                }
            }
        }
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

    //商品尾款实付价
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
        //判断是否在付尾款时间内
        $advance_goods = AdvanceGoods::findOne(['goods_id' => $item['id'], 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
        if (!$advance_goods) {
            throw new OrderException('预售商品已下架。');
        }
        if ($advance_goods->pay_limit != -1) {
            $time = strtotime($advance_goods->end_prepayment_at) + (60 * 60 * 24 * $advance_goods->pay_limit);
            if ($time < time() || strtotime($advance_goods->end_prepayment_at) > time()) {
                throw new OrderException('现在不在付尾款时间内。');
            }
        }
        //尾款计算
        $order_model = AdvanceOrder::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'goods_id' => $item['id'],
//            'goods_attr_id' => $item['goods_attr_id'],
            'is_pay' => 1,
            'is_cancel' => 0,
            'is_refund' => 0,
            'is_delete' => 0
        ]);
        $advance_order_count = $order_model->sum('goods_num');
        /* @var AdvanceOrder $order_info */
        $order_info = $order_model->andWhere(['id' => $item['advance_id']])->one();
        if (!$order_info) {
            throw new OrderException('定金订单不存在。');
        }
        if ($order_info->order_id != 0) {
            throw new OrderException('该预售，您有未支付的尾款订单，请去个人中心-待付款订单支付。');
        }
        $discount = 10;//初始10折，等于没有优惠折扣
        $couponBool = bcmul(bcsub($goods->price, $order_info->swell_deposit), $order_info->goods_num);//用于优惠券比对
        if (!is_array($advance_goods->ladder_rules)) {
            $advance_goods->ladder_rules = json_decode($advance_goods->ladder_rules, true);
        }
        foreach ($advance_goods->ladder_rules as $value) {
            if ($advance_order_count >= $value['num']) {
                $discount = $value['discount'];
            }
        }
        /* @var AdvanceOrder $order_info */
        $goods_info = json_decode($order_info->goods_info, true);
        $price = $goods_info['goods_attr']['member_price'];
        $preferential_price = $goods_info['goods_attr']['member_price'] - bcdiv(bcmul($price, $discount), 10);
        $order_info->preferential_price = $preferential_price;
        if (!$order_info->save()) {
            throw new OrderException('活动优惠金额保存失败。' . $order_info->errors[0]);
        }
        $price = bcmul(bcsub(bcdiv(bcmul($price, $discount), 10), $order_info->swell_deposit), $order_info->goods_num);//先阶梯折扣，后膨胀金优惠，再乘以数量
//        $preferential_price = $goods_info['goods_attr']['price'] - $price;
        $price = ($price < 0) ? 0 : $price;
        // 其他商品特有判断
        $this->checkGoods($goods, $item);

        try {
            /** @var OrderGoodsAttr $goodsAttr */
            $goodsAttr = $this->getGoodsAttr($item['goods_attr_id'], $goods);
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
            'total_price' => $price,
            'coupon_bool' => $couponBool,//用于优惠券门槛比对，售价-膨胀金
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
            'preferential_price' => $preferential_price,
            'deposit' => $order_info->deposit,
            'swell_deposit' => $order_info->swell_deposit,
            'form_id' => $goods->form_id,
        ];
        return $itemData;
    }

    /**
     * 优惠券优惠
     * @param $mchItem
     * @param $formMchItem
     * @return mixed
     * @throws OrderException
     */
    protected function setCouponDiscountData($mchItem, $formMchItem)
    {
        $mchItem['coupon'] = [
            'enabled' => true,
            'use' => false,
            'coupon_discount' => price_format(0),
            'user_coupon_id' => 0,
            'coupon_error' => null,
        ];
        if (!$this->getEnableCoupon() || $mchItem['mch']['id'] != 0) { // 入住商不可使用优惠券
            $mchItem['coupon']['enabled'] = false;
            return $mchItem;
        }
        if (empty($formMchItem['user_coupon_id'])) {
            return $mchItem;
        }
        $nowDateTime = date('Y-m-d H:i:s');
        /** @var UserCoupon $userCoupon */
        $userCoupon = UserCoupon::find()->where([
            'AND',
            ['id' => $formMchItem['user_coupon_id']],
            ['user_id' => \Yii::$app->user->identity->getId()],
            ['is_delete' => 0],
            ['is_use' => 0],
            ['<=', 'start_time', $nowDateTime],
            ['>=', 'end_time', $nowDateTime],
        ])->one();
        if (!$userCoupon) {
            $mchItem['coupon']['coupon_error'] = '优惠券不存在';
            return $mchItem;
        }
        $coupon = Coupon::findOne([
            'id' => $userCoupon->coupon_id,
        ]);
        if (!$coupon) {
            $mchItem['coupon']['coupon_error'] = '优惠券不存在';
            return $mchItem;
        }
        if ($coupon->appoint_type == 1 || $coupon->appoint_type == 2) {
            if ($coupon->appoint_type == 1) { // 指定分类可用
                $couponCatRelations = CouponCatRelation::findAll([
                    'coupon_id' => $coupon->id,
                    'is_delete' => 0,
                ]);
                $catIdList = [];
                foreach ($couponCatRelations as $couponCatRelation) {
                    $catIdList[] = $couponCatRelation->cat_id;
                }
                /** @var GoodsCatRelation[] $goodsCatRelations */
                $goodsCatRelations = GoodsCatRelation::find()
                    ->select('gcr.goods_warehouse_id')
                    ->alias('gcr')
                    ->leftJoin(['gc' => GoodsCats::tableName()], 'gcr.cat_id=gc.id')
                    ->where(['gc.is_delete' => 0])
                    ->all();
                $couponGoodsIdList = [];
                foreach ($goodsCatRelations as $goodsCatRelation) {
                    $couponGoodsIdList[] = $goodsCatRelation->goods_warehouse_id;
                }
            } else { // 指定商品可用
                $couponGoodsRelations = CouponGoodsRelation::findAll([
                    'coupon_id' => $coupon->id,
                    'is_delete' => 0,
                ]);
                $couponGoodsIdList = [];
                foreach ($couponGoodsRelations as $couponGoodsRelation) {
                    $couponGoodsIdList[] = $couponGoodsRelation->goods_warehouse_id;
                }
            }
            $totalGoodsPrice = 0;
            $coupon_bool = 0;
            foreach ($mchItem['goods_list'] as $goodsItem) {
                if (!in_array($goodsItem['goods_warehouse_id'], $couponGoodsIdList)) {
                    continue;
                }
                $totalGoodsPrice += $goodsItem['total_price'];
                $coupon_bool += $goodsItem['coupon_bool'];
            }
            if ($userCoupon->coupon_min_price > $coupon_bool) { // 减膨胀金后未达到优惠券使用条件
                $mchItem['coupon']['coupon_error'] = '所选优惠券未满足使用条件';
                return $mchItem;
            }
            $subPrice = min($totalGoodsPrice, $userCoupon->sub_price, $mchItem['total_goods_price']);
            if ($subPrice > 0) {
                $mchItem['total_goods_price'] = price_format($mchItem['total_goods_price'] - $subPrice);
                $mchItem['coupon']['use'] = true;
                $mchItem['coupon']['user_coupon_id'] = $userCoupon->id;
                $mchItem['coupon']['coupon_discount'] = price_format($subPrice);
            }
            $mchItem = $this->setCoupon($mchItem, $totalGoodsPrice, $subPrice, $couponGoodsIdList);
        } elseif ($coupon->appoint_type == 3) { // 全商品通用
            if ($mchItem['total_goods_price'] <= 0) { // 价格已优惠到0不再使用优惠券
                $mchItem['coupon']['coupon_error'] = '商品价格已为0无法使用优惠券';
                return $mchItem;
            }
            $coupon_bool = 0;
            foreach ($mchItem['goods_list'] as $goodsItem) {
                $coupon_bool += $goodsItem['coupon_bool'];
            }
            if ($coupon_bool < $userCoupon->coupon_min_price) { // 减膨胀金后未达到优惠券使用条件
                $mchItem['coupon']['coupon_error'] = '所选优惠券未满足使用条件';
                return $mchItem;
            }
            $subPrice = 0;
            if ($userCoupon->type == 1) { // 折扣券
                if ($userCoupon->discount <= 0 || $userCoupon->discount >= 10) {
                    throw new OrderException('优惠券折扣信息错误，折扣范围必须是`0 < 折扣 < 10`。');
                }
                $subPrice = $mchItem['total_goods_original_price'] * (1 - $userCoupon->discount / 10);
            } elseif ($userCoupon->type == 2) { // 满减券
                if ($userCoupon->sub_price <= 0) {
                    throw new OrderException('优惠券优惠信息错误，优惠金额必须大于0元。');
                }
                $subPrice = $userCoupon->sub_price;
            }
            if ($subPrice > $mchItem['total_goods_price']) {
                $subPrice = $mchItem['total_goods_price'];
            }
            $totalGoodsPrice = $mchItem['total_goods_price'];
            $mchItem['total_goods_price'] = price_format($mchItem['total_goods_price'] - $subPrice);
            $mchItem['coupon']['use'] = true;
            $mchItem['coupon']['user_coupon_id'] = $userCoupon->id;
            $mchItem['coupon']['coupon_discount'] = price_format($subPrice);
            $mchItem = $this->setCoupon($mchItem, $totalGoodsPrice, $subPrice);
        }
        return $mchItem;
    }

//    protected function getCustomCurrency($goods, $goodsAttr)
//    {
//        $iAttr = AdvanceGoodsAttr::findOne(['goods_attr_id' => $goodsAttr->id, 'is_delete' => 0]);
//        return [
//            $iAttr->deposit . '定金抵扣' . $iAttr->swell_deposit . '膨胀金',
//        ];
//    }

    // 商品规格类
    public function getGoodsAttrClass()
    {
        return new AdvanceOrderGoodsAttr();
    }

    public function getSendType($mchItem)
    {
        $setting = (new SettingForm())->search();
        return $setting['send_type'];
    }

    public function subGoodsNum($goodsAttr, $subNum, $goodsItem)
    {
        return;//预售在定金阶段已扣除库存
    }

    public function checkGoodsStock($goodsList)
    {
        return true;
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
                throw new OrderException('商品' . $goods['name'] . '数量不能小于0');
            }
        }
    }

    public function getToken()
    {
        //与定金token共用，唯一值，且尾款订单只有单商品，故取[0]
        $advance_info = AdvanceOrder::findOne($this->form_data['list'][0]['goods_list'][0]['advance_id']);
        $advance_info->order_token = parent::getToken();
        if (!$advance_info->save()) {
            throw new OrderException('order_token保存失败');
        }
        return $advance_info->order_token;
    }

    /**
     * 会员优惠（会员价和会员折扣）
     * @param $mchItem
     * @return mixed
     * @throws OrderException
     */
    protected function setMemberDiscountData($mchItem)
    {
        $mchItem['member_discount'] = price_format(0);

        if (!$this->getMemberPrice()) {
            return $mchItem;
        }

        /** @var User $user */
        $user = \Yii::$app->user->identity;
        /** @var UserIdentity $identity */
        $identity = $user->getIdentity()->andWhere(['is_delete' => 0,])->one();
        if (!$identity) {
            return $mchItem;
        }
        $member = MallMembers::findOne([
            'level' => $identity->member_level,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);
        if (!$member) {
            return $mchItem;
        }
        $totalSubPrice = 0; // 会员总计优惠金额
        foreach ($mchItem['goods_list'] as &$goodsItem) {
            if ($goodsItem['is_level'] != 1) {
                continue;
            }
            $memberUnitPrice = null;
            $discountName = null;

            $goodsItem['member_discount'] = price_format(0);

            /* @var OrderGoodsAttr $goodsAttr */
            $goodsAttr = $goodsItem['goods_attr'];
            try {
                $goodsMemberPrice = $this->getGoodsAttrMemberPrice($goodsAttr, $member->level);
            } catch (\Exception $e) {
                throw new OrderException($e->getMessage());
            }
            if ($goodsMemberPrice && $goodsItem['is_level_alone'] == 1) {
                $memberUnitPrice = $goodsMemberPrice;
                if (!is_numeric($memberUnitPrice) || $memberUnitPrice < 0) {
                    throw new OrderException('商品会员价`' . $memberUnitPrice . '`不合法，会员价必须是数字且大于等于0元。');
                }
                $discountName = '会员价优惠';
            } elseif ($member->discount) {
                if (!($member->discount >= 0.1 && $member->discount <= 10)) {
                    throw new OrderException('会员折扣率不合法，会员折扣率必须在1折~10折。');
                }
                $goodsPrice = $goodsAttr->original_price;
                if (!is_numeric($goodsPrice) || $goodsPrice < 0) {
                    throw new OrderException('商品金额不合法，商品金额必须是数字且大于等于0元。');
                }
                $memberUnitPrice = $goodsPrice * $member->discount / 10;
                $discountName = '会员折扣优惠';
            }

            if ($memberUnitPrice && is_numeric($memberUnitPrice) && $memberUnitPrice >= 0) {
                $goodsAttr->member_price = $memberUnitPrice;
                // 商品单件价格（会员优惠后）
                $goodsAttr->price = $memberUnitPrice - ($goodsAttr->original_price - $goodsAttr->price);
                $memberTotalPrice = price_format($memberUnitPrice * $goodsItem['num']);
                $memberSubPrice = $goodsItem['total_original_price'] - $memberTotalPrice;
                if ($memberSubPrice != 0) {
                    // 减去会员优惠金额
//                    $memberSubPrice = min($goodsItem['total_price'], $memberSubPrice);
//                    $goodsItem['total_price'] = price_format($goodsItem['total_price'] - $memberSubPrice);
                    $totalSubPrice += $memberSubPrice;
                    $goodsItem['discounts'][] = [
                        'name' => $discountName,
                        'value' => $memberSubPrice > 0 ?
                            ('-' . price_format($memberSubPrice))
                            : ('+' . price_format(0 - $memberSubPrice))
                    ];
//                    $mchItem['total_goods_price'] = price_format($mchItem['total_goods_price'] - $memberSubPrice);
                    $goodsItem['member_discount'] = price_format($memberSubPrice);
                }
            }
        }
        if ($totalSubPrice) {
            $mchItem['member_discount'] = price_format($totalSubPrice);
        }
        return $mchItem;
    }

    /**
     * @param $mchItem
     * @param float $totalGoodsPrice 商品总额
     * @param float $subPrice 总优惠金额
     * @param array $couponGoodsIdList 优惠的商品id列表
     * @return array
     */
    private function setCoupon($mchItem, $totalGoodsPrice, $subPrice, $couponGoodsIdList = null)
    {
        if ($totalGoodsPrice <= 0) {
            return $mchItem;
        }
        /* @var $resetPrice float 优惠券剩余可优惠金额 */
        $resetPrice = $subPrice;
        uasort($mchItem['goods_list'], function ($a, $b) {
            if ($a['total_price'] == $b['total_price']) {
                return 0;
            }
            return ($a['total_price'] < $b['total_price']) ? -1 : 1;
        });
        $mchItem['goods_list'] = array_values($mchItem['goods_list']);
        foreach ($mchItem['goods_list'] as $index => &$goods) {
            if ($couponGoodsIdList && !empty($couponGoodsIdList)
                && !in_array($goods['goods_warehouse_id'], $couponGoodsIdList)) {
                continue;
            }
            /* @var float $goodsPrice 商品可优惠的金额 */
            $goodsPrice = price_format($goods['total_price'] * $subPrice / $totalGoodsPrice);
            if ($resetPrice < $goodsPrice || ($index == count($mchItem['goods_list']) - 1 && $resetPrice > 0)) {
                $goodsPrice = $resetPrice;
            }
            $resetPrice -= $goodsPrice;
            $goods['total_price'] -= min($goodsPrice, $goods['total_price']);
        }
        unset($goods);
        return $mchItem;
    }
}
