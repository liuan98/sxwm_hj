<?php
/**
 * @copyright (c)天幕网络
 * @author Lu Wei
 * @link http://www.67930603.top/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/14 16:02
 */


namespace app\forms\api\order;


use app\forms\common\CommonDelivery;
use app\forms\common\CommonMallMember;
use app\forms\common\CommonOption;
use app\forms\common\template\TemplateList;
use app\models\Address;
use app\models\Coupon;
use app\models\CouponCatRelation;
use app\models\CouponGoodsRelation;
use app\models\Form;
use app\models\FreeDeliveryRules;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\GoodsCatRelation;
use app\models\GoodsCats;
use app\models\GoodsMemberPrice;
use app\models\GoodsWarehouse;
use app\models\Mall;
use app\models\MallMembers;
use app\models\Model;
use app\models\Option;
use app\models\Order;
use app\models\OrderDetail;
use app\models\PostageRules;
use app\models\Store;
use app\models\User;
use app\models\UserCoupon;
use app\models\UserIdentity;
use app\plugins\mch\models\Mch;
use app\validators\PhoneNumberValidator;
use yii\db\Query;
use yii\helpers\VarDumper;
use app\forms\common\mch\SettingForm;

class OrderSubmitForm extends Model
{
    private $xAddress;

    protected $sign;

    protected $supportPayTypes;

    /**
     * 是否开启会员价会员折扣功能
     * @var bool
     */
    private $enableMemberPrice = true;

    /**
     * 是否开启优惠券功能
     * @var bool
     */
    private $enableCoupon = true;

    /**
     * 是否开启积分功能
     * @var bool
     */
    private $enableIntegral = true;

    /**
     * 是否开启自定义表单功能
     * @var bool
     */
    private $enableOrderForm = true;

    /**
     * 是否开启区域允许购买
     * @var bool
     */
    private $enableAddressEnable = true;

    /**
     * 是否开启起送规则
     * @var bool
     */
    private $enablePriceEnable = true;

    /**
     * 订单状态|1.已完成|0.进行中 不能对订单进行任何操作
     * @var int
     */
    public $status = 1;

    public $form_data;

    public function rules()
    {
        return [
            [['form_data'], 'required'],
        ];
    }

    public function getEnableCoupon()
    {
        return $this->enableCoupon;
    }

    public function getMemberPrice()
    {
        return $this->enableMemberPrice;
    }

    /**
     * 设置订单的标识，主要用于区分插件
     * @param string $sign
     * @return $this
     */
    public function setSign($sign)
    {
        $this->sign = $sign;
        return $this;
    }

    public function setXAddress($val)
    {
        $this->xAddress = $val;
        return $this;
    }

    /**
     * 设置支持的支付方式,支付方式见readme->支付
     * @param null|array $supportPayTypes
     * @return $this
     */
    public function setSupportPayTypes($supportPayTypes = null)
    {
        $this->supportPayTypes = $supportPayTypes;
        return $this;
    }

    public function setEnableMemberPrice($val)
    {
        $this->enableMemberPrice = $val;
        return $this;
    }

    public function setEnableCoupon($val)
    {
        $this->enableCoupon = $val;
        return $this;
    }

    public function setEnableIntegral($val)
    {
        $this->enableIntegral = $val;
        return $this;
    }

    public function setEnableOrderForm($val)
    {
        $this->enableOrderForm = $val;
        return $this;
    }

    public function setEnableAddressEnable($val)
    {
        $this->enableAddressEnable = $val;
        return $this;
    }

    public function setEnablePriceEnable($val)
    {
        $this->enablePriceEnable = $val;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return array
     * @throws \yii\db\Exception
     */
    public function preview()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse($this);
        }
        try {
            $this->changeParam();
            $data = $this->getAllData();

            // jambalaya 判断物流配送-s
            // $minordermoney = \Yii::$app->mall->getMallSettingOne('minordermoney');

            // if($data['total_price'] < $minordermoney && $minordermoney >0 && !empty($minordermoney)){

            //     foreach ($data['mch_list'] as $key => $value) {

            //         $newsends = array();
            //         foreach ($value['delivery']['send_type_list'] as $k1 => $v1) {
                        
            //             if($v1['value'] =='express'){
            //                 // 总金额小于后台设置金额，去掉物流配送
            //                 // unset$value['delivery']['send_type_list'][$k1]);
            //                 // break;
            //             }else{
            //                 $newsends[]=$v1;
            //             }
            //         }

            //         $data['mch_list'][$key]['delivery']['send_type']='offline';
            //         $data['mch_list'][$key]['delivery']['send_type_list'] = $newsends;
                    
            //     }
               
            //     $data['allZiti'] = true;
            //     $data['has_ziti'] = true;
               
            // }
            // jambalaya 判断物流配送-e
            $data = $this->changeData($data);
        } catch (OrderException $orderException) {
            return [
                'code' => 1,
                'msg' => $orderException->getMessage(),
                'error' => [
                    'line' => $orderException->getLine()
                ]
            ];
        }
        return [
            'code' => 0,
            'data' => $data,
        ];
    }

    /**
     * @return array
     * @throws \yii\base\Exception
     */
    public function submit()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse($this);
        }
        try {
            $this->changeParam();
            $data = $this->getAllData();
        } catch (OrderException $orderException) {
            return [
                'code' => 1,
                'msg' => $orderException->getMessage(),
                'error' => [
                    'line' => $orderException->getLine()
                ]
            ];
        }
        if (!$this->getAddress() && !$data['allZiti']) {
            return [
                'code' => 1,
                'msg' => '请先选择收货地址。',
            ];
        }
        if ($data['allZiti']) {
            if (!$data['address']['name']) {
                return [
                    'code' => 1,
                    'msg' => '请填写联系人'
                ];
            }
            if (!$data['address']['mobile']) {
                return [
                    'code' => 1,
                    'msg' => '请填写手机号'
                ];
            }
            $mall = Mall::findOne(['id' => \Yii::$app->mall->id]);
            $status = $mall->getMallSettingOne('mobile_verify');
            if ($status) {
                $value = $data['address']['mobile'];
                $pattern = (new PhoneNumberValidator())->pattern;
                if ($value && !preg_match($pattern, $value)) {
                    return [
                        'code' => 1,
                        'msg' => '手机号格式不正确'
                    ];
                }
            }
        }
        foreach ($data['mch_list'] as $mchItem) {
            if (isset($mchItem['city']) && isset($mchItem['city']['error'])) {
                return [
                    'code' => 1,
                    'msg' => $mchItem['city']['error']
                ];
            }
        }

        $token = $this->getToken();
        $dataArr = [
            'mall' => \Yii::$app->mall,
            'user' => \Yii::$app->user->identity,
            'form_data' => $this->form_data,
            'token' => $token,
            'sign' => $this->sign,
            'supportPayTypes' => $this->supportPayTypes,
            'enableMemberPrice' => $this->enableMemberPrice,
            'enableCoupon' => $this->enableCoupon,
            'enableIntegral' => $this->enableIntegral,
            'enableOrderForm' => $this->enableOrderForm,
            'enablePriceEnable' => $this->enablePriceEnable,
            'enableAddressEnable' => $this->enableAddressEnable,
            'OrderSubmitFormClass' => static::class,
            'status' => $this->status,
            'appVersion' => \Yii::$app->appVersion,
        ];
        $class = new OrderSubmitJob($dataArr);
        $queueId = \Yii::$app->queue->delay(0)->push($class);

        return [
            'code' => 0,
            'data' => [
                'token' => $token,
                'queue_id' => $queueId,
            ],
        ];
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
        $mchItem = array();
        foreach ($listData as &$mchItem) {
            if($mchItem){
            $this->checkGoodsStock($mchItem['goods_list']);
            $this->checkGoodsOrderLimit($mchItem['goods_list']);
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
            $mchItem['total_goods_price'] = price_format($totalGoodsPrice)?price_format($totalGoodsPrice):0;
            $mchItem['total_goods_original_price'] = price_format($totalGoodsOriginalPrice);

            $mchItem = $this->setMemberDiscountData($mchItem);
            $mchItem = $this->setCouponDiscountData($mchItem, $formMchItem);
            $mchItem = $this->setIntegralDiscountData($mchItem, $formMchItem);

            $mchItem = $this->setDeliveryData($mchItem, $formMchItem);
            $mchItem = $this->setStoreData($mchItem, $formMchItem, $this->form_data);
            $mchItem = $this->setExpressData($mchItem);

            if (isset($mchItem['mch']['id']) && $mchItem['mch']['id'] == 0) {
                $mchItem = $this->setVipDiscountData($mchItem);
            }
            
            $totalPrice = price_format($mchItem['total_goods_price'] + $mchItem['express_price']);
            $mchItem['total_price'] = $this->setTotalPrice($totalPrice);
            // $mchItem = $this->setOrderForm($mchItem);
            $mchItem = $this->setGoodsForm($mchItem);
        }
        }


        $total_price = 0;
        $totalOriginalPrice = 0;
        foreach ($listData as &$item) {
            $total_price += $item['total_price'];
            $totalOriginalPrice += $item['total_goods_original_price'];
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
                $priceEnable = $this->getPriceEnable(price_format($item['total_goods_original_price']), $address, $mchItem);
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

        foreach ($listData as &$mchItem) {
            $this->afterGetMchItem($mchItem);
        }

        return [
            'mch_list' => $listData,
            'total_price' => price_format($total_price),
            'price_enable' => $priceEnable,
            'address' => $hasCity ? (($address && $address->longitude && $address->latitude) ? $address : null) : $address,
            'address_enable' => $addressEnable,
            'has_ziti' => $hasZiti,
            'custom_currency_all' => $this->getcustomCurrencyAll($listData),
            'allZiti' => $allZiti,
            'hasCity' => $hasCity,
            'template_message_list' => $this->getTemplateMessage(),
        ];
    }

    /**
     *
     * @param $mchItem
     * @return mixed
     */
    public function afterGetMchItem(&$mchItem)
    {
        return $mchItem;
    }

    /**
     * 获取1个或多上商户的订单数据
     * @param $formMchList
     * @return array [ ['mch' => '商户信息', 'goods_list' => 'array 订单的商品信息和金额列表'] ]
     * @throws OrderException
     */
    protected function getMchListData($formMchList)
    {
        $listData = [];
        foreach ($formMchList as $i => $formMchItem) {
            $mchItem = [
                'mch' => $this->getMchInfo($formMchItem['mch_id']),
                'goods_list' => $this->getGoodsListData($formMchItem['goods_list']),
                'form_data' => $formMchItem,
            ];
            $listData[] = $mchItem;
        }
        return $listData;
    }

    protected function getMchInfo($id)
    {
        if ($id == 0) {
            return [
                'id' => 0,
                'name' => \Yii::$app->mall->name,
            ];
        } else {
            $mch = Mch::findOne($id);
            \Yii::$app->setMchId($mch->id);
            return [
                'id' => $id,
                'name' => $mch ? $mch->store->name : '未知商户',
            ];
        }
    }

    /**
     * @param $goodsList
     * @return array
     * @throws OrderException
     */
    protected function getGoodsListData($goodsList)
    {
        $list = [];
        foreach ($goodsList as $i => $item) {
            $result = $this->getGoodsItemData($item);
            $result['form_data'] = isset($item['form_data']) ? $item['form_data'] : null;
            $list[] = $result;
        }
        return $list;
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
            'total_price' => price_format($goodsAttr->price * $item['num']),
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
            'confine_order_count' => $goods->confine_order_count,
            'form_id' => $goods->form_id,
        ];
        return $itemData;
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

        if (!$this->enableMemberPrice) {
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
                    $memberSubPrice = min($goodsItem['total_price'], $memberSubPrice);
                    $goodsItem['total_price'] = price_format($goodsItem['total_price'] - $memberSubPrice);
                    $totalSubPrice += $memberSubPrice;
                    $goodsItem['discounts'][] = [
                        'name' => $discountName,
                        'value' => $memberSubPrice > 0 ?
                            ('-' . price_format($memberSubPrice))
                            : ('+' . price_format(0 - $memberSubPrice))
                    ];
                    $mchItem['total_goods_price'] = price_format($mchItem['total_goods_price'] - $memberSubPrice);
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
     * 获取指定规格指定会员等级的会员价
     * @param $goodsAttr
     * @param $memberLevel
     * @return GoodsMemberPrice|null
     * @throws \Exception
     */
    protected function getGoodsAttrMemberPrice($goodsAttr, $memberLevel)
    {
        $goodsMemberPrice = CommonMallMember::getGoodsAttrMemberPrice($goodsAttr->goodsAttr, $memberLevel);
        // $goodsMemberPrice 有可能为空
        return $goodsMemberPrice ? $goodsMemberPrice->price : null;
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
        if (!$this->enableCoupon || $mchItem['mch']['id'] != 0) { // 入住商不可使用优惠券
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
                    ->where(['gc.is_delete' => 0, 'gcr.cat_id' => $catIdList, 'gcr.is_delete' => 0])
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
            $totalGoodsOriginalPrice = 0;
            foreach ($mchItem['goods_list'] as $goodsItem) {
                if (!in_array($goodsItem['goods_warehouse_id'], $couponGoodsIdList)) {
                    continue;
                }
                $totalGoodsPrice += $goodsItem['total_price'];
                $totalGoodsOriginalPrice += $goodsItem['total_original_price'];
            }
            if ($userCoupon->coupon_min_price > $totalGoodsOriginalPrice) { // 可用的商品原总价未达到优惠券使用条件
                $mchItem['coupon']['coupon_error'] = '所选优惠券未满足使用条件';
                return $mchItem;
            }
            $sub = 0;
            if ($userCoupon->type == 1) { // 折扣券
                if ($userCoupon->discount <= 0 || $userCoupon->discount >= 10) {
                    throw new OrderException('优惠券折扣信息错误，折扣范围必须是`0 < 折扣 < 10`。');
                }
                $discount = $totalGoodsOriginalPrice * (1 - $userCoupon->discount / 10);
                $sub = !empty($userCoupon->discount_limit) && ($userCoupon->discount_limit < $discount) ? $userCoupon->discount_limit : $discount;
            } elseif ($userCoupon->type == 2) { // 满减券
                if ($userCoupon->sub_price <= 0) {
                    throw new OrderException('优惠券优惠信息错误，优惠金额必须大于0元。');
                }
                $sub = $userCoupon->sub_price;
            }
            $subPrice = min($totalGoodsPrice, $sub, $mchItem['total_goods_price']);
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
            if ($mchItem['total_goods_original_price'] < $userCoupon->coupon_min_price) { // 商品原总价未达到优惠券使用条件
                $mchItem['coupon']['coupon_error'] = '所选优惠券未满足使用条件';
                return $mchItem;
            }
            $subPrice = 0;
            if ($userCoupon->type == 1) { // 折扣券
                if ($userCoupon->discount <= 0 || $userCoupon->discount >= 10) {
                    throw new OrderException('优惠券折扣信息错误，折扣范围必须是`0 < 折扣 < 10`。');
                }
                $discount = $mchItem['total_goods_original_price'] * (1 - $userCoupon->discount / 10);
                $subPrice = !empty($userCoupon->discount_limit) && ($userCoupon->discount_limit < $discount) ? $userCoupon->discount_limit : $discount;
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

    /**
     * 积分抵扣
     * @param $mchItem
     * @param $formMchItem
     * @return mixed
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    protected function setIntegralDiscountData($mchItem, $formMchItem)
    {
        $mchItem['integral'] = [
            'can_use' => false,
            'use' => false,
            'use_num' => 0,
            'deduction_price' => price_format(0),
        ];
        if (!$this->enableIntegral || $mchItem['mch']['id'] != 0) {
            return $mchItem;
        }
        /** @var User $user */
        $user = \Yii::$app->user->identity;

        $userIntegral = \Yii::$app->currency->setUser($user)->integral->select();

        if (!$userIntegral || $userIntegral < 0) {
            return $mchItem;
        }
        if ($mchItem['total_goods_price'] <= 0) {
            return $mchItem;
        }
        $memberIntegral = \Yii::$app->mall->getMallSettingOne('member_integral');
        if (!$memberIntegral || !is_numeric($memberIntegral) || $memberIntegral <= 0) {
            return $mchItem;
        }

        // 积分最多可抵扣的金额
        $maxDeductionIntegral = min(
            intval($mchItem['total_goods_price'] * $memberIntegral),
            $userIntegral
        );

        $totalDeductionPrice = 0; // 已抵扣的金额总和
        $totalDeductionIntegral = 0; // 抵扣积分总额
        foreach ($mchItem['goods_list'] as &$goodsItem) {
            if (is_nan($goodsItem['forehead_integral']) || $goodsItem['forehead_integral'] <= 0) {
                continue;
            }
            $unitGoodsMaxDeductionPrice = 0;
            if ($goodsItem['forehead_integral_type'] == 1) { // 固定方式抵扣
                $unitGoodsMaxDeductionPrice = $goodsItem['forehead_integral'];
            } elseif ($goodsItem['forehead_integral_type'] == 2) { // 最大百分比方式抵扣
                if ($goodsItem['forehead_integral'] > 100) {
                    continue;
                }
                $unitGoodsMaxDeductionPrice = $goodsItem['unit_price'] * $goodsItem['forehead_integral'] / 100;
            }
            if ($goodsItem['accumulative'] == 1) { // 允许多件累计抵扣
                $goodsMaxDeductionPrice = $unitGoodsMaxDeductionPrice * $goodsItem['num'];
            } else { // 只允许抵扣一件
                $goodsMaxDeductionPrice = $unitGoodsMaxDeductionPrice;
            }
            // 抵扣金额不能超过商品金额
            $goodsMaxDeductionPrice = min($goodsMaxDeductionPrice, $goodsItem['total_price']);
            $goodsMaxDeductionIntegral = intval($goodsMaxDeductionPrice * $memberIntegral);
            /* @var OrderGoodsAttr $orderGoodsAttr */
            $orderGoodsAttr = $goodsItem['goods_attr'];
            if (($totalDeductionIntegral + $goodsMaxDeductionIntegral) > $maxDeductionIntegral) { // 抵扣的金额超过最多可抵扣的
                $orderGoodsAttr->use_integral = intval($maxDeductionIntegral - $totalDeductionIntegral);
                $orderGoodsAttr->integral_price = price_format($orderGoodsAttr->use_integral / $memberIntegral);
                $totalDeductionPrice = price_format($maxDeductionIntegral / $memberIntegral);
                $totalDeductionIntegral = $maxDeductionIntegral;
                break;
            } else {
                $goodsMaxDeductionPrice = price_format($goodsMaxDeductionIntegral / $memberIntegral);
                $totalDeductionPrice += $goodsMaxDeductionPrice;
                $totalDeductionIntegral += $goodsMaxDeductionIntegral;
                $orderGoodsAttr->use_integral = $goodsMaxDeductionIntegral;
                $orderGoodsAttr->integral_price = $goodsMaxDeductionPrice;
            }
        }

        $mchItem['integral']['use_num'] = $totalDeductionIntegral;
        $mchItem['integral']['deduction_price'] = price_format($totalDeductionPrice);
        $mchItem['integral']['can_use'] = $mchItem['integral']['use_num'] > 0 ? true : false;
        $mchItem['integral']['use'] = $formMchItem['use_integral'] == 1 ? true : false;

        if ($mchItem['integral']['use']) {
            $mchItem['total_goods_price'] = price_format($mchItem['total_goods_price'] - $totalDeductionPrice);
        }

        $mchItem = $this->setGoodsListIntegralSub($mchItem);

        return $mchItem;
    }

    /**
     * 设置订单里每个商品被基本抵扣后的total_price字段
     * @param $mchItem
     * @return mixed
     */
    private function setGoodsListIntegralSub($mchItem)
    {
        if (
            empty($mchItem['integral'])
            || !$mchItem['integral']['use']
            || !isset($mchItem['integral']['deduction_price'])
            || $mchItem['integral']['deduction_price'] <= 0
        ) {
            return $mchItem;
        }

        // 排序
        uasort($mchItem['goods_list'], function ($a, $b) {
            if ($a['total_price'] == $b['total_price']) {
                return 0;
            }
            return ($a['total_price'] < $b['total_price']) ? -1 : 1;
        });

        $deductionPrice = $mchItem['integral']['deduction_price'];
        foreach ($mchItem['goods_list'] as &$goodsItem) {
            if ($deductionPrice <= 0) {
                break;
            }
            if ($goodsItem['total_price'] <= 0) {
                continue;
            }
            if ($goodsItem['forehead_integral_type'] == 1) { // 固定方式抵扣
                $goodsMaxSubPrice = $goodsItem['forehead_integral'] * $goodsItem['num'];
            } else { // 最大百分比方式抵扣
                if ($goodsItem['forehead_integral'] > 100) {
                    $goodsMaxSubPrice = 0;
                } else {
                    $goodsMaxSubPrice = $goodsItem['unit_price'] * $goodsItem['forehead_integral'] * $goodsItem['num'] / 100;
                }
            }
            $maxSubPrice = min($goodsItem['total_price'], $deductionPrice, $goodsMaxSubPrice);
            $deductionPrice = $deductionPrice - $maxSubPrice;
            $goodsItem['total_price'] = price_format($goodsItem['total_price'] - $maxSubPrice);
        }
        unset($goodsItem);
//        dd($deductionPrice, 0);
//        dd($mchItem['goods_list']);
        return $mchItem;
    }

    /**
     * @return array 数组形式['express','offline','city']；express--快递、offline--自提、city--同城配送
     * @throws \Exception
     * 获取配送方式
     */
    protected function getSendType($mchItem)
    {
        if (isset($mchItem['mch']['id']) && $mchItem['mch']['id'] > 0) {
            $form = new SettingForm();
            $form->mch_id = \Yii::$app->mchId;
            $setting = $form->search();

            return $setting['send_type'];
        }
        $sendType = \Yii::$app->mall->getMallSettingOne('send_type');
        return $sendType;
    }

    /**
     * 配送方式
     * @param $mchItem
     * @param $formMchItem
     * @return mixed
     * @throws OrderException
     * @throws \Exception
     */
    protected function setDeliveryData($mchItem, $formMchItem)
    {
        $sendType = $this->getSendType($mchItem);
        $sendType = $this->getNewSendType($sendType);

        $minordermoney = \Yii::$app->mall->getMallSettingOne('minordermoney');

        if($mchItem['total_goods_original_price'] < $minordermoney && $minordermoney >0 && !empty($minordermoney)){

            // foreach ($sendType as $key => $value) {

//                 // $newsends = array();
                foreach ($sendType as  $v1) {
                    
                    if($v1 =='express'){
                        // 总金额小于后台设置金额，去掉物流配送
                        // unset$value['delivery']['send_type_list'][$k1]);
                        // break;
                    }else{
                        $newsends[]=$v1;
                    }
                }

//                 // $data['mch_list'][$key]['delivery']['send_type']='offline';
//                 // $data['mch_list'][$key]['delivery']['send_type_list'] = $newsends;
                
            // }
           
//             // $data['allZiti'] = true;
//             // $data['has_ziti'] = true;
           $sendType = $newsends;
        }
       
 
        if (!isset($formMchItem['send_type'])
            || $formMchItem['send_type'] === ''
            || $formMchItem['send_type'] === null) {
            $formMchItem['send_type'] = $sendType[0];
        }
        if (!in_array($formMchItem['send_type'], $sendType)) {
            throw new OrderException('配送方式`' . $formMchItem['send_type'] . '`不正确。');
        }
        foreach ($sendType as $item) {
            $mchItem['delivery']['send_type'] = $formMchItem['send_type'];
            $mchItem['delivery']['disabled'] = false;
            $mchItem['delivery']['send_type_list'][] =
                [
                    'name' => ($item == 'express') ? '快递配送' : ($item == 'offline' ? '上门自提' : '同城配送'),
                    'value' => $item,
                ];
        }

         
        return $mchItem;
    }

    /**
     * 门店
     * @param $mchItem
     * @param $formMchItem
     * @param $formData
     * @return array
     */
    protected function setStoreData($mchItem, $formMchItem, $formData)
    {
        $mchItem['store'] = null;
        $mchItem['store_select_enable'] = true;
        if ($mchItem['delivery']['send_type'] != 'offline') {
            return $mchItem;
        }
        $storeExists = Store::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => $mchItem['mch']['id'],
            'is_delete' => 0,
        ])->exists();
        if (!$storeExists) {
            $mchItem['no_store'] = true;
        }
        if ($mchItem['mch']['id'] != 0) {
            $mchItem['store_select_enable'] = false;
        }
        if (!empty($formMchItem['store_id'])) {
            $store = Store::find()
                ->where([
                    'id' => $formMchItem['store_id'],
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => $mchItem['mch']['id'],
                    'is_delete' => 0,
                ])->asArray()->one();
        } else {
            $query = Store::find()
                ->where([
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => $mchItem['mch']['id'],
                    'is_delete' => 0,
                ]);
            if ($formMchItem['mch_id'] == 0) {
                $query->andWhere(['is_default' => 1]);
            }
            $store = $query->asArray()->one();
        }
        if (!$store) {
            return $mchItem;
        }
        if ($store['longitude']
            && $store['latitude']
            && !empty($formData['longitude'])
            && !empty($formData['latitude'])
            && is_numeric($formData['longitude'])
            && is_numeric($formData['latitude'])) {
            $store['distance'] = get_distance($store['longitude'], $store['latitude'], $formData['longitude'], $formData['latitude']);
        } else {
            $store['distance'] = '-m';
        }
        if (!empty($store['distance']) && is_numeric($store['distance'])) {
            // $store['distance'] 单位 m
            if ($store['distance'] > 1000) {
                $store['distance'] = number_format($store['distance'] / 1000, 2) . 'km';
            } else {
                $store['distance'] = number_format($store['distance'], 0) . 'm';
            }
        } else {
            $store['distance'] = '-m';
        }
        $mchItem['store'] = $store;
        return $mchItem;
    }

    /**
     * 包邮和运费
     * @param $mchItem
     * @return mixed
     * @throws OrderException
     */
    protected function setExpressData($mchItem)
    {
        $noZeroGoodsList = []; // 没有被包邮的商品列表（未单独设置包邮规则）
        $noZeroIndRuleGoodsList = []; // 没有被包邮的单独设置包邮规则的商品列表
        $zeroIndRuleGoodsList = []; // 被包邮的单独设置包邮规则的商品列表
        $mchItem['express_price'] = price_format(0);

        if ($mchItem['delivery']['send_type'] == 'offline') { // 上门自提无需运费
            return $mchItem;
        }

        $address = $this->getAddress();
        // var_dump($address,__LINE__);
        if (!$address) {
            $mchItem['city']['error'] = '未选择收货地址';
            return $mchItem;
        }

        if ($mchItem['delivery']['send_type'] == 'city') { // 同城配送
            if (!($address->longitude && $address->latitude)) {
                // 同城配送时，地址没有定位则将地址取消选中
                $this->xAddress = null;
                // 没有设置定位
                $mchItem['city']['error'] = '未选择收货地址';
                return $mchItem;
            }
            $point = [
                'lng' => $address->longitude,
                'lat' => $address->latitude
            ];
            $num = 0;
            foreach ($mchItem['goods_list'] as $goodsItem) { // 按商品ID小计件数和金额，看是否达到包邮条件
                $num += $goodsItem['num'];
            }
            try {
                $commonDelivery = CommonDelivery::getInstance();
                $cityConfig = $commonDelivery->getConfig();
                if (isset($cityConfig['price_enable']) && $cityConfig['price_enable'] && $mchItem['total_goods_original_price'] < $cityConfig['price_enable']) {
                    $mchItem['city']['error'] = '未达到起送价' . $cityConfig['price_enable'] . 'FCFA';
                    return $mchItem;
                }
                $distance = $commonDelivery->getDistance($point);//var_dump($distance);
                $totalSecondPrice = 0;
                if (!(isset($cityConfig['is_free_delivery']) && $cityConfig['is_free_delivery'] == 1
                    && $cityConfig['free_delivery'] <= $mchItem['total_goods_original_price'])) {
                    $totalSecondPrice = $commonDelivery->getPrice($distance, $num);
                }
                $mchItem['city'] = [
                    'address' => $cityConfig['address']['address'],
                    'explain' => $cityConfig['explain']
                ];
            } catch (\Exception $exception) {
                return $exception->getMessage();
                //$mchItem['city']['error'] = '用户定位地址不在配送范围内';
                //return $mchItem;
            }
            $mchItem['distance'] = $distance;
            $mchItem['express_price'] = price_format($totalSecondPrice);
            $mchItem['total_price'] = price_format($mchItem['total_goods_price'] + $mchItem['express_price']);
            return $mchItem;
        }

        $groupGoodsTotalList = []; // 按商品id小计的商品金额和数量
        foreach ($mchItem['goods_list'] as $goodsItem) { // 按商品ID小计件数和金额，看是否达到包邮条件
            if (isset($groupGoodsTotalList[$goodsItem['id']])) {
                $groupGoodsTotalList[$goodsItem['id']]['total_price'] += $goodsItem['total_price'];
                $groupGoodsTotalList[$goodsItem['id']]['num'] += $goodsItem['num'];
            } else {
                $groupGoodsTotalList[$goodsItem['id']]['total_price'] = $goodsItem['total_price'];
                $groupGoodsTotalList[$goodsItem['id']]['num'] = $goodsItem['num'];
            }
            $groupGoodsTotalList[$goodsItem['id']]['total_price'] =
                price_format($groupGoodsTotalList[$goodsItem['id']]['total_price']);
        }
        foreach ($mchItem['goods_list'] as $goodsItem) {
            if (is_numeric($goodsItem['pieces']) && $goodsItem['pieces'] > 0) { // 单品设置了满件包邮
                if ($groupGoodsTotalList[$goodsItem['id']]['num'] >= $goodsItem['pieces']) { // 满足条件
                    $zeroIndRuleGoodsList[] = $goodsItem;
                } else { // 未满足条件
                    $noZeroIndRuleGoodsList[] = $goodsItem;
                }
            } elseif (is_numeric($goodsItem['forehead']) && $goodsItem['forehead'] > 0) { // 单品设置了满额包邮
                if ($groupGoodsTotalList[$goodsItem['id']]['total_price'] >= $goodsItem['forehead']) { // 满足条件
                    $zeroIndRuleGoodsList[] = $goodsItem;
                } else { // 未满足条件
                    $noZeroIndRuleGoodsList[] = $goodsItem;
                }
            } else { // 未设置包邮
                $noZeroGoodsList[] = $goodsItem;
            }
        }
        if (!count($noZeroGoodsList) && !count($noZeroIndRuleGoodsList)) {
            return $mchItem;
        }

        $globalZeroExpressPrice = false; // 是否全局包邮
        /** @var FreeDeliveryRules[] $freeDeliveries */
        $freeDeliveries = FreeDeliveryRules::find()->where([
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => $mchItem['mch']['id']
        ])->orderBy('price')->all();
        foreach ($freeDeliveries as $freeDelivery) {
            $districts = $freeDelivery->decodeDetail();
            $inDistrict = false;
            foreach ($districts as $district) {
                if ($district['id'] == $address->province_id) {
                    $inDistrict = true;
                    break;
                } elseif ($district['id'] == $address->city_id) {
                    $inDistrict = true;
                    break;
                } elseif ($district['id'] == $address->district_id) {
                    $inDistrict = true;
                    break;
                }
            }
            if ($inDistrict && $mchItem['total_goods_original_price'] >= $freeDelivery->price) {
                $globalZeroExpressPrice = true;
                break;
            }
        }

        if ($globalZeroExpressPrice) { // 满足全局包邮规则 list = $noZeroIndRuleGoodsList
            $noZeroGoodsList = $noZeroIndRuleGoodsList;
        } else { // 未满足全局包邮规则 list = $noZeroGoodsList + $noZeroIndRuleGoodsList
            $noZeroGoodsList = array_merge($noZeroGoodsList, $noZeroIndRuleGoodsList);
        }

        $postageRuleGroups = []; // 商品按匹配到的运费规则进行分组
        $noPostageRuleHit = true; // 没有比配到运费规则
        foreach ($noZeroGoodsList as $goodsItem) {
            if ($goodsItem['freight_id'] && $goodsItem['freight_id'] != -1) {
                $postageRule = PostageRules::findOne([
                    'mall_id' => \Yii::$app->mall->id,
                    'id' => $goodsItem['freight_id'],
                    'is_delete' => 0,
                    'mch_id' => $mchItem['mch']['id'],
                ]);
                if (!$postageRule) {
                    $postageRule = PostageRules::findOne([
                        'mall_id' => \Yii::$app->mall->id,
                        'status' => 1,
                        'is_delete' => 0,
                        'mch_id' => $mchItem['mch']['id'],
                    ]);
                }
            } else {
                $postageRule = PostageRules::findOne([
                    'mall_id' => \Yii::$app->mall->id,
                    'status' => 1,
                    'is_delete' => 0,
                    'mch_id' => $mchItem['mch']['id'],
                ]);
            }
            if (!$postageRule) {
                continue;
            }

            $rule = null; // 用户的收货地址是否在规则中
            $ruleDetails = $postageRule->decodeDetail();
            foreach ($ruleDetails as $ruleDetail) {
                foreach ($ruleDetail['list'] as $district) {
                    if ($district['id'] == $address->province_id) {
                        $rule = $ruleDetail;
                        break;
                    } elseif ($district['id'] == $address->city_id) {
                        $rule = $ruleDetail;
                        break;
                    } elseif ($district['id'] == $address->district_id) {
                        $rule = $ruleDetail;
                        break;
                    }
                }
                if ($rule) {
                    break;
                }
            }
            if (!$rule) {
                continue;
            }

            $noPostageRuleHit = false;
            if (!isset($postageRuleGroups['rule:' . $postageRule->id])) {
                $postageRuleGroups['rule:' . $postageRule->id] = [
                    'postage_rule' => $postageRule,
                    'rule' => $rule,
                    'goods_list' => [],
                ];
            }
            $postageRuleGroups['rule:' . $postageRule->id]['goods_list'][] = $goodsItem;
        }
        if ($noPostageRuleHit) {
            return $mchItem;
        }
        $firstPriceList = [];
        $totalSecondPrice = 0;
        foreach ($postageRuleGroups as $group) {
            /** @var PostageRules $postageRule */
            $postageRule = $group['postage_rule'];
            $rule = $group['rule'];
            $goodsList = $group['goods_list'];
            $firstPrice = $rule['firstPrice'];
            $secondPrice = 0;
            if ($postageRule->type == 1) { // 按重量计费
                $totalWeight = 0;
                foreach ($goodsList as $goods) {
                    if (is_nan($goods['goods_attr']['weight'])) {
                        throw new OrderException('商品`' . $goods['name'] . '的重量不是有效的数字。');
                    }
                    $totalWeight += ($goods['goods_attr']['weight'] * $goods['num']);
                }
                if ($rule['second'] > 0) {
                    $secondPrice = ceil(($totalWeight - $rule['first']) / $rule['second']) // 向上取整
                        * $rule['secondPrice'];
                } else {
                    $secondPrice = 0;
                }
            } elseif ($postageRule->type == 2) { // 按件数计费
                $totalNum = 0;
                foreach ($goodsList as $goods) {
                    $totalNum += $goods['num'];
                }
                if ($rule['second'] > 0) {
                    $secondPrice = ceil(($totalNum - $rule['first']) / $rule['second']) // 向上取整
                        * $rule['secondPrice'];
                } else {
                    $secondPrice = 0;
                }
            }
            if ($secondPrice < 0) {
                $secondPrice = 0;
            }
            $firstPriceList[] = $firstPrice;
            $totalSecondPrice += $secondPrice;
        }

        $mchItem['express_price'] = price_format(max($firstPriceList) + $totalSecondPrice);
        $mchItem['total_price'] = price_format($mchItem['total_goods_price'] + $mchItem['express_price']);
        return $mchItem;
    }

    protected function setOrderForm($mchItem)
    {
        $mchItem['order_form'] = null;
        if (!$this->enableOrderForm) {
            return $mchItem;
        }
        if ($mchItem['mch']['id'] != 0) {
            return $mchItem;
        }
        $option = CommonOption::get(Option::NAME_ORDER_FORM, \Yii::$app->mall->id, Option::GROUP_APP);
        if (!$option) {
            return $mchItem;
        }
        if ($option['status'] != 1) {
            return $mchItem;
        }
        if (!empty($option['value']) && is_array($option['value'])) {
            foreach ($option['value'] as $k => $item) {
                $option['value'][$k]['is_required'] = $item['is_required'] == 1 ? 1 : 0;
            }
        }
        $mchItem['order_form'] = $option;
        return $mchItem;
    }

    protected function setGoodsForm($mchItem)
    {
        $defaultForm = null;
        $noDefaultForm = false;
        $existsFormIds = [];
        $dataOfFormId = [];
        $hasGoodsForm = false;
        if(isset($mchItem['goods_list'])){
        foreach ($mchItem['goods_list'] as &$goodsItem) {
            $goodsItem['form'] = null;
            if (!isset($goodsItem['form_id']) || $goodsItem['form_id'] == -1) {
                continue;
            }
            if ($goodsItem['form_id'] == 0) {
                if ($noDefaultForm) {
                    continue;
                }
                if (!$defaultForm) {
                    $defaultForm = Form::findOne([
                        'mall_id' => \Yii::$app->mall->id,
                        'mch_id' => $mchItem['mch']['id'],
                        'is_default' => 1,
                        'status' => 1,
                        'is_delete' => 0,
                    ]);
                    if (!$defaultForm) {
                        $noDefaultForm = true;
                        continue;
                    }
                }
                $form = $defaultForm;
            } else {
                $form = Form::findOne([
                    'id' => $goodsItem['form_id'],
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => $mchItem['mch']['id'],
                    'status' => 1,
                    'is_delete' => 0,
                ]);
            }
            if (!$form) {
                continue;
            }
            $hasGoodsForm = true;
            if (is_string($form->value)) {
                $form->value = \Yii::$app->serializer->decode($form->value);
            }
            if (is_array($form->value) || $form->value instanceof \ArrayObject) {
                foreach ($form->value as &$formItem) {
                    $formItem['is_required'] = $formItem['is_required'] == 1 ? 1 : 0;
                }
            }
            if (in_array($form->id, $existsFormIds)) {
                $sameForm = true;
            } else {
                $sameForm = false;
                $existsFormIds[] = $form->id;
            }
            if (!$sameForm && !empty($goodsItem['form_data'])) {
                $dataOfFormId[$form->id] = $goodsItem['form_data'];
            } elseif ($sameForm && isset($dataOfFormId[$form->id])) {
                $goodsItem['form_data'] = $dataOfFormId[$form->id];
            }
            $goodsItem['form'] = [
                'id' => $form->id,
                'name' => $form->name,
                'value' => $form->value,
                'same_form' => $sameForm,
            ];
        }
        }
        $mchItem['diff_goods_form_count'] = intval(count($existsFormIds));
        $mchItem['has_goods_form'] = $hasGoodsForm;
        return $mchItem;
    }

    /**
     * 获取用户的收货地址
     * @return null|Address
     */
    protected function getAddress()
    {
        if ($this->xAddress) {
            return $this->xAddress;
        }
        /** @var User $user */
        $user = \Yii::$app->user->identity;
        if (!$this->form_data['address_id']) {
            $this->xAddress = Address::findOne([
                'user_id' => $user->id,
                'is_delete' => 0,
                'is_default' => 1,
            ]);
        } else {
            $this->xAddress = Address::findOne([
                'user_id' => $user->id,
                'is_delete' => 0,
                'id' => $this->form_data['address_id'],
            ]);
        }
        return $this->xAddress;
    }

    /**
     * @param $mchData
     * @return array
     * @throws OrderException
     */
    public function getUsableCouponList($mchData)
    {
        $mchList = $this->getMchListData([$mchData]);
        if (!is_array($mchList) || !count($mchList)) {
            return [];
        }
        $mch = $mchList[0];
        $goodsTotalOriginalPrice = 0;
        foreach ($mch['goods_list'] as $goodsItem) {
            $goodsTotalOriginalPrice += $goodsItem['total_original_price'];
        }

        /** @var User $user */
        $user = \Yii::$app->user->identity;
        $nowDateTime = date('Y-m-d H:i:s');

        /** @var UserCoupon[] $allList */
        $allList = UserCoupon::find()->where([
            'AND',
            ['mall_id' => \Yii::$app->mall->id,],
            ['user_id' => $user->id],
            ['is_use' => 0],
            ['is_delete' => 0],
            ['<=', 'start_time', $nowDateTime],
            ['>=', 'end_time', $nowDateTime],
            ['<=', 'coupon_min_price', $goodsTotalOriginalPrice],
        ])->with(['coupon' => function ($query) {
            /** @var Query $query */
        }])->all();
        if (!count($allList)) {
            return [];
        }

        $goodsWarehouseIdList = [];
        $catIdList = [];
        foreach ($mch['goods_list'] as &$goodsItem) {
            $goods = Goods::findOne($goodsItem['id']);
            $goodsWarehouseIdList[] = $goods->goods_warehouse_id;
            $goodsCatRelations = GoodsCatRelation::findAll([
                'goods_warehouse_id' => $goods->goods_warehouse_id,
                'is_delete' => 0,
            ]);
            $goodsItem['goodsCatRelations'] = $goodsCatRelations;
            $goodsItem['goods'] = $goods;
            foreach ($goodsCatRelations as $goodsCatRelation) {
                $catIdList[] = $goodsCatRelation->cat_id;
            }
        }

        $newList = [];
        foreach ($allList as &$userCoupon) {
            if (!$userCoupon->coupon) {
                continue;
            }
            $userCoupon->coupon_data = \Yii::$app->serializer->decode($userCoupon->coupon_data);
            $userCoupon->coupon_data->appoint_type = $userCoupon->coupon->appoint_type;
            $userCoupon->coupon_data->name = $userCoupon->coupon->name;

            if ($userCoupon->coupon->appoint_type == 2) {
                /** @var GoodsWarehouse[] $goodsList */
                $goodsWarehouseList = $userCoupon->coupon->goods;
                if (count($goodsWarehouseList)) {
                    $couponTotalGoodsPrice = 0;
                    foreach ($goodsWarehouseList as &$goodsWarehouse) {
                        foreach ($mch['goods_list'] as &$goodsItem) {
                            $goods = $goodsItem['goods'];
                            if ($goods->goods_warehouse_id == $goodsWarehouse->id) {
                                $couponTotalGoodsPrice += $goodsItem['total_original_price'];
                            }
                        }
                        unset($goodsItem);
                    }
                    unset($goodsWarehouse);
                    foreach ($goodsWarehouseList as $goodsWarehouse) {
                        if (in_array($goodsWarehouse->id, $goodsWarehouseIdList) && $couponTotalGoodsPrice >= $userCoupon->coupon_min_price) {
                            $newList[] = $userCoupon;
                            break;
                        }
                    }
                    continue;
                }
            } elseif ($userCoupon->coupon->appoint_type == 1) {
                $catList = $userCoupon->coupon->cat;
                if (count($catList)) {
                    $couponCatTotalGoodsPrice = 0;
                    foreach ($catList as &$cat) {
                        foreach ($mch['goods_list'] as &$goodsItem) {
                            foreach ($goodsItem['goodsCatRelations'] as &$goodsCatRelation) {
                                if ($goodsCatRelation->cat_id == $cat->id) {
                                    $couponCatTotalGoodsPrice += $goodsItem['total_original_price'];
                                }
                            }
                        }
                        unset($goodsItem);
                    }
                    unset($cat);

                    foreach ($catList as $cat) {
                        if (in_array($cat->id, $catIdList) && $couponCatTotalGoodsPrice >= $userCoupon->coupon_min_price) {
                            $newList[] = $userCoupon;
                            break;
                        }
                    }
                    continue;
                }
            } elseif ($userCoupon->coupon->appoint_type == 3) {
                $newList[] = $userCoupon;
            }
        }
        return $newList;
    }

    /**
     * 获取商品规格、用户库存操作
     * @param $goodsAttrId
     * @param Goods $goods
     * @return OrderGoodsAttr
     * @throws \Exception
     */
    public function getGoodsAttr($goodsAttrId, $goods)
    {
        $newGoodsAttr = $this->getGoodsAttrClass();
        $newGoodsAttr->setGoods($goods);
        $newGoodsAttr->setGoodsAttrById($goodsAttrId);

        return $newGoodsAttr;
    }

    /**
     * @return OrderGoodsAttr OrderGoodsAttr
     * 商品规格类
     */
    public function getGoodsAttrClass()
    {
        return new OrderGoodsAttr();
    }

    /**
     * 商品库存操作
     * @param OrderGoodsAttr $goodsAttr
     * @param int $subNum
     * @param array $goodsItem
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function subGoodsNum($goodsAttr, $subNum, $goodsItem)
    {
        (new GoodsAttr())->updateStock($subNum, 'sub', $goodsAttr->id);
    }

    public function extraGoodsDetail($order, $goodsItem)
    {
        $orderDetail = new OrderDetail();
        $orderDetail->order_id = $order->id;
        $orderDetail->goods_id = $goodsItem['id'];
        $orderDetail->num = $goodsItem['num'];
        $orderDetail->unit_price = $goodsItem['unit_price'];
        $orderDetail->total_original_price = $goodsItem['total_original_price'];
        $orderDetail->total_price = $goodsItem['total_price'];
        $orderDetail->member_discount_price = $goodsItem['member_discount'];
        $orderDetail->sign = $goodsItem['sign'];
        $orderDetail->goods_no = $goodsItem['goods_attr']['no'] ?: '';
        $goodsInfo = [
            'attr_list' => $goodsItem['attr_list'],
            'goods_attr' => $goodsItem['goods_attr'],
        ];
        $orderDetail->goods_info = $orderDetail->encodeGoodsInfo($goodsInfo);
        $orderDetail->form_data = \Yii::$app->serializer->encode(isset($goodsItem['form_data']) ? $goodsItem['form_data'] : null);
        $orderDetail->form_id = (isset($goodsItem['form']) && isset($goodsItem['form']['id'])) ? $goodsItem['form']['id'] : 0;

        if (!$orderDetail->save()) {
            throw new \Exception((new Model())->getErrorMsg($orderDetail));
        }
    }

    /**
     * 所选收货地址是否允许购买
     * @param Address $address
     * @param integer $mchItem
     * @return bool
     */
    protected function getAddressEnable($address, $mchItem)
    {
        $mchId = $mchItem['mch']['id'];
        if (!$address) {
            return true;
        }
        if (!$this->enableAddressEnable) {
            return true;
        }
        if (isset($mchItem['delivery'])
            && isset($mchItem['delivery']['send_type'])
            && $mchItem['delivery']['send_type'] == 'offline') {
            return true;
        }
        if (isset($mchItem['delivery'])
            && isset($mchItem['delivery']['send_type'])
            && $mchItem['delivery']['send_type'] == 'city') {
            return true;
        }

        $func = function ($area_limit, $address) {
            foreach ($area_limit as $group) {
                if (isset($group['list']) && is_array($group['list'])) {
                    foreach ($group['list'] as $item) {
                        if (isset($item['id'])) {
                            if ($item['id'] == $address->province_id
                                || $item['id'] == $address->city_id
                                || $item['id'] == $address->district_id) {
                                return true;
                            }
                        }
                    }
                }
            }
            return false;
        };

        //商品自定义
        foreach ($mchItem['goods_list'] as $item) {
            $goods = $item['goods_attr']['goods'];
            if ($goods['is_area_limit'] === 1) {
                $area_limit = \yii\helpers\BaseJson::decode($goods{'area_limit'});
                if (!isset($area_limit) || !is_array($area_limit)) {
                    return false;
                }
                return $func($area_limit, $address);
            }
        }

        $model = CommonOption::get(
            Option::NAME_TERRITORIAL_LIMITATION,
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            ['is_enable' => 0],
            $mchId
        );
        if (!$model || !isset($model['is_enable'])) {
            return true;
        }
        if ($model['is_enable'] != 1) {
            return true;
        }
        if (!isset($model['detail']) || !is_array($model['detail'])) {
            return false;
        }
        return $func($model['detail'], $address);
    }

    /**
     * 是否达到起送规则
     * @param string $totalPrice
     * @param Address $address
     * @param array $mchItem
     * @return bool
     */
    protected function getPriceEnable($totalPrice, $address, $mchItem)
    {
        if (!$this->enablePriceEnable) {
            return true;
        }
        $mchId = $mchItem['mch']['id'];
        if (isset($mchItem['delivery'])
            && isset($mchItem['delivery']['send_type'])
            && $mchItem['delivery']['send_type'] == 'city') {
            return true;
        }
        $model = CommonOption::get(
            Option::NAME_OFFER_PRICE,
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            ['is_enable' => 0, 'total_price' => 0],
            $mchId
        );
        if (!$model || !isset($model['is_enable'])) {
            return true;
        }
        if ($model['is_enable'] != 1) {
            return true;
        }
        $minPrice = null;
        if (is_array($model['detail'])) {
            foreach ($model['detail'] as $group) {
                $inArr = false;
                foreach ($group['list'] as $item) {
                    if (isset($item['id'])) {
                        if ($address && ($item['id'] == $address->province_id
                                || $item['id'] == $address->city_id
                                || $item['id'] == $address->district_id)) {
                            $inArr = true;
                            break;
                        }
                    }
                }
                if ($inArr) {
                    $minPrice = price_format($group['total_price']);
                    break;
                }
            }
        }
        if ($minPrice === null) {
            $minPrice = price_format($model['total_price']);
        }
        return $totalPrice >= $minPrice ? true : false;
    }

    /**
     * @param Goods $goods
     * @param $item
     * @return bool
     * @throws OrderException
     * 商品信息的其他判断
     */
    protected function checkGoods($goods, $item)
    {
        if ($goods->mch_id > 0) {
            $mch = Mch::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'status' => 1,
                'review_status' => 1,
                'id' => $goods->mch_id
            ]);

            if (!$mch) {
                throw new OrderException('商户不存在或已关闭');
            }
        }
        return true;
    }

    /**
     * @param Goods $goods
     * @param OrderGoodsAttr $goodsAttr
     * @return array 例如 ['2000积分']
     * 额外的货币说明
     */
    protected function getCustomCurrency($goods, $goodsAttr)
    {
        return [];
    }

    protected function getToken()
    {
        return \Yii::$app->security->generateRandomString();
    }

    /**
     * @param array $listData
     * @return array 例如 ['2000积分']
     * 额外的合计货币说明
     */
    protected function getCustomCurrencyAll($listData)
    {
        return [];
    }

    /**
     * 添加自定义额外的订单信息
     * @param $order
     * @param $mchItem
     * @return bool
     */
    public function extraOrder($order, $mchItem)
    {
        return true;
    }

    /**
     * 设置总预览价格，用于前端展示
     * @param $totalPrice
     * @return mixed
     */
    protected function setTotalPrice($totalPrice)
    {
        return $totalPrice;
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
        $originGoodsList = $mchItem['goods_list']; // 备份原商品列表
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
        // 根据旧商品列表顺序或得新的商品列表start--->
        $goodsList = [];
        foreach ($originGoodsList as &$originGoods) {
            foreach ($mchItem['goods_list'] as $newGoods) {
                if (($originGoods['goods_attr'])->id == ($newGoods['goods_attr'])->id) {
                    $goodsList[] = $newGoods;
                }
            }
        }
        $mchItem['goods_list'] = $goodsList;
        // 根据旧商品列表顺序或得新的商品列表end<---
        unset($goods);
        return $mchItem;
    }

    /**
     * 检查商品限单（商品可以下单的次数限制）
     * @param array $goodsList [ ['id','name',''] ]
     * @throws OrderException
     */
    public function checkGoodsOrderLimit($goodsList)
    {
        foreach ($goodsList as $goods) {
            if (!isset($goods['confine_order_count'])) {
                continue;
            }
            if ($goods['confine_order_count'] < 0) {
                continue;
            }
            if ($goods['confine_order_count'] == 0) {
                throw new OrderException('商品“' . $goods['name'] . '”已超出下单次数限制。');
            }
            $count = OrderDetail::find()->alias('od')
                ->select('od.order_id')
                ->leftJoin(['o' => Order::tableName()], 'od.order_id=o.id')
                ->where([
                    'o.cancel_status' => 0,
                    'o.is_delete' => 0,
                    'o.is_recycle' => 0,
                    'o.user_id' => \Yii::$app->user->id,
                    'od.is_delete' => 0,
                    'od.goods_id' => $goods['id'],
                ])
                ->groupBy('od.order_id')
                ->count();
            if ($count >= $goods['confine_order_count']) {
                throw new OrderException('商品“' . $goods['name'] . '”已超出下单次数限制。' . $count);
            }
        }
    }

    /**
     * 检查购买的商品数量是否超出限制及库存（购买数量含以往的订单）
     * @param array $goodsList [ ['id','name',''] ]
     * @throws OrderException
     */
    private function checkGoodsBuyLimit($goodsList)
    {
        $goodsIdMap = [];
        foreach ($goodsList as $goods) {
            if ($goods['num'] <= 0) {
                throw new OrderException('商品' . $goods['name'] . '数量不能小于0');
            }
            if (isset($goodsIdMap[$goods['id']])) {
                $goodsIdMap[$goods['id']]['num'] += $goods['num'];
            } else {
                $goodsIdMap[$goods['id']]['num'] = $goods['num'];
                $goodsIdMap[$goods['id']]['goods'] = $goods['goods_attr']['goods'];
            }
        }
        foreach ($goodsIdMap as $goodsId => $item) {
            /** @var Goods $goods */
            $goods = $item['goods'];
            if ($goods->confine_count <= 0) {
                continue;
            }
            $oldOrderGoodsNum = OrderDetail::find()->alias('od')
                ->leftJoin(['o' => Order::tableName()], 'od.order_id=o.id')
                ->where([
                    'od.goods_id' => $goodsId,
                    'od.is_delete' => 0,
                    'o.user_id' => \Yii::$app->user->id,
                    'o.is_delete' => 0,
                ])
                ->andWhere(['!=', 'o.cancel_status', 1])
                ->sum('od.num');
            $oldOrderGoodsNum = $oldOrderGoodsNum ? intval($oldOrderGoodsNum) : 0;
            $totalNum = $oldOrderGoodsNum + $item['num'];
            if ($totalNum > $goods->confine_count) {
                throw new OrderException('商品（' . $goods->goodsWarehouse->name . '）限购' . $goods->confine_count . '件');
            }
        }
    }

    /**
     * 检查商品库存是否充足
     * @param array $goodsList [ ['id','name',''] ]
     * @throws OrderException
     */
    public function checkGoodsStock($goodsList)
    {
        foreach ($goodsList as $goods) {
            if ($goods['num'] <= 0) {
                throw new OrderException('商品' . $goods['name'] . '数量不能小于0');
            }
            if (!empty($goods['goods_attr'])) {
                /** @var GoodsAttr $goodsAttr */
                $goodsAttr = $goods['goods_attr'];
                if ($goods['num'] > $goodsAttr->stock) {
                    throw new OrderException('商品库存不足: ' . $goods['name']);
                }
            }
        }
    }

    /**
     * 发货方式兼容全平台之前的传入参数
     */
    protected function changeParam()
    {
        if (version_compare(\Yii::$app->appVersion, '4.1.0', '<')) {
            foreach ($this->form_data['list'] as &$formMchList) {
                if (isset($formMchList['send_type'])) {
                    if ($formMchList['send_type'] === 1) {
                        $formMchList['send_type'] = 'express';
                    } elseif ($formMchList['send_type'] === 2) {
                        $formMchList['send_type'] = 'offline';
                    }
                }
            }
            unset($formMchList);
        }
    }

    /**
     * @param array $data
     * @return array
     * 发货方式兼容全平台之前的传出参数
     */
    protected function changeData($data)
    {
        $sendType = [
            'express' => 1,
            'offline' => 2
        ];
        if (version_compare(\Yii::$app->appVersion, '4.1.0', '<')) {
            foreach ($data['mch_list'] as &$formMchList) {
                $formMchList['delivery']['send_type'] = $sendType[$formMchList['delivery']['send_type']];
                foreach ($formMchList['delivery']['send_type_list'] as &$item) {
                    $item['value'] = $sendType[$item['value']];
                }
                unset($item);
            }
            unset($formMchList);
        }
        return $data;
    }

    /**
     * @param $sendType
     * @return array
     */
    protected function getNewSendType($sendType)
    {
        $list = [];
        foreach ($sendType as $item) {
            // 适配旧版没有同城配送的小程序端
            if (version_compare(\Yii::$app->appVersion, '4.1.5', '<') && $item == 'city') {
                continue;
            }
            // 字节跳动小程序不支持同城配送
            if (in_array(\Yii::$app->appPlatform, ['ttapp']) && $item == 'city') {
                continue;
            }
            $list[] = $item;
        }
        if (count($list) == 0) {
            $list[] = 'express';
        }
        return $list;
    }

    public function setVipDiscountData($mchItem)
    {
        //权限判断
        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        if (!in_array('vip_card', $permission)) {
            return $mchItem;
        }
        try {
            $plugin = \Yii::$app->plugin->getPlugin('vip_card');
            $mchItem = $plugin->vipDiscount($mchItem);
            return $mchItem;
        } catch (\Exception $e) {
            return $mchItem;
        }
    }

    protected function getTemplateMessage()
    {
        $arr = ['order_pay_tpl', 'order_cancel_tpl', 'order_send_tpl'];
        $list = TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, $arr);
        return $list;
    }
}
