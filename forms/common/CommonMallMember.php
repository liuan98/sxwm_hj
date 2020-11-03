<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\forms\common;


use app\models\Coupon;
use app\models\CouponMemberRelation;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\GoodsCatRelation;
use app\models\GoodsCats;
use app\models\GoodsMemberPrice;
use app\models\MallMembers;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\models\UserCoupon;
use app\models\UserCouponMember;
use app\models\UserIdentity;
use yii\db\Exception;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class CommonMallMember
{
    /**
     *  获取所有可用的会员列表
     */
    public static function getAllMember()
    {
        $all = MallMembers::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'status' => 1,
        ])->orderBy('level')->all();

        return $all;
    }

    /**
     * 商城会员列表(分页)
     * @param int $level
     * @param int $limit
     * @return array
     */
    public static function getList($level = 0, $limit = 20)
    {
        $list = MallMembers::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'status' => 1,
        ])
            ->with('rights')
            ->andWhere(['>', 'level', $level])
            ->orderBy('level')
            ->page($pagination, $limit)->asArray()->all();

        return [
            'list' => $list,
            'pagination' => $pagination
        ];
    }


    /**
     * 会员详情
     * @param $id
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getDetail($id)
    {
        $detail = MallMembers::find()->where([
            'id' => $id
        ])
            ->with(['rights'])
            ->asArray()->one();

        $detail['level'] = (int)$detail['level'];

        return $detail;
    }

    /**
     * 获取会员专属商品
     * @param int $catsId
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getMallMemberGoods($catsId = 0)
    {
        $user = CommonUser::getUserIdentity('member_level');
        $goodsIds = GoodsMemberPrice::find()->where([
            'level' => $user->member_level,
            'is_delete' => 0
        ])->select('goods_id');

        $query = Goods::find()->alias('g')->where([
            'g.mall_id' => \Yii::$app->mall->id,
            'g.is_delete' => 0,
            'g.mch_id' => 0,
            'g.sign' => '',
            'g.status' => 1,
            'g.id' => $goodsIds
        ]);

        if ($catsId) {
            $goodsWarehouseIds = GoodsCatRelation::find()->where([
                'cat_id' => $catsId,
                'is_delete' => 0
            ])->select('goods_warehouse_id');
            $query->andWhere(['g.goods_warehouse_id' => $goodsWarehouseIds]);
        }

        /** @var Goods[] $list */
        $list = $query->with(['goodsWarehouse'])->page($pagination, 10)->all();
        $newList = [];
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['name'] = $item->getName();
            $newItem['cover_pic'] = $item->getCoverPic();
            $newItem['price'] = $item->getPrice();
            $newItem['original_price'] = $item->getOriginalPrice();
            $newList[] = $newItem;
        }

        return [
            'list' => $newList,
            'pagination' => $pagination
        ];
    }

    /**
     * 获取会员专属优惠券
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getMallMemberCoupons()
    {
        $userIdentity = CommonUser::getUserIdentity('member_level');

        $memberCouponQuery = CouponMemberRelation::find()->where([
            'member_level' => $userIdentity->member_level, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id
        ])->select('coupon_id');

        $userCouponMemberQuery = UserCouponMember::find()->where([
            'uc.is_delete' => 0, 'uc.mall_id' => \Yii::$app->mall->id, 'uc.user_id' => \Yii::$app->user->id
        ])->select('user_coupon_id');

        $userCouponQuery = UserCoupon::find()->alias('uc')->where([
            'uc.is_delete' => 0, 'uc.mall_id' => \Yii::$app->mall->id, 'uc.user_id' => \Yii::$app->user->id
        ])->andWhere(['id' => $userCouponMemberQuery])->select('uc.coupon_id');

        $userCouponCountQuery = (new Query())->from(['uc' => $userCouponQuery])->where('uc.coupon_id=c.id')
            ->select('count(1)');

        $list = Coupon::find()->alias('c')->where([
            'c.mall_id' => \Yii::$app->mall->id,
            'c.is_delete' => 0
        ])->andWhere(['c.id' => $memberCouponQuery])->select(['c.*', 'user_count' => $userCouponCountQuery])->asArray()
            ->page($pagination)->all();

        return [
            'list' => $list,
            'pagination' => $pagination
        ];
    }

    /**
     * @param $params
     * @param null $level
     * @return \app\models\GoodsMemberPrice|null
     * @throws Exception
     * 获取指定规格指定会员等级的会员价
     */
    public static function getGoodsAttrMemberPrice($params, $level = null)
    {
        if ($params instanceof GoodsAttr) {
            $goodsAttr = $params;
        } else if (is_numeric($params)) {
            $goodsAttr = GoodsAttr::findOne($params);
        } else {
            throw new Exception('错误的参数,$param必须是\app\models\GoodsAttr的对象或对象ID');
        }
        $goodsMemberPrice = null;
        if ($goodsAttr->memberPrice) {
            foreach ($goodsAttr->memberPrice as $item) {
                if ($item->level == $level) {
                    $goodsMemberPrice = $item;
                }
            }
        }
        return $goodsMemberPrice;
    }

    public static $mallMember;

    /**
     * @param $level
     * @return array|\yii\db\ActiveRecord|null|MallMembers
     */
    public static function getMemberOne($level)
    {
        if (self::$mallMember) {
            return self::$mallMember;
        }
        $result = MallMembers::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'status' => 1,
            'level' => $level
        ])->one();
        self::$mallMember = $result;

        return $result;
    }

    /**
     * 获取
     */
    public function getNextConsumeUpgradeMember()
    {
        /** @var UserIdentity $userIdentity */
        $userIdentity = UserIdentity::find()->where(['user_id' => \Yii::$app->user->id])->one();
        $result = MallMembers::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'status' => 1,
            'auto_update' => 1
        ])
            ->andWhere(['>', 'level', $userIdentity->member_level])
            ->one();

        return $result;
    }

    public function getOrderMoneyCount($mallId = 0, $userId = 0)
    {
        // 订单总额
        $orderMoneyCount = 0;
        $orderList = Order::find()->where([
            'mall_id' => $mallId ?: \Yii::$app->mall->id,
            'user_id' => $userId ?: \Yii::$app->user->id,
            'is_sale' => 1,
            'is_delete' => 0,
            'is_pay' => 1,
            'cancel_status' => 0,
            'status' => 1
        ])->all();
        /* @var Order[] $orderList */
        $orderIdList = [];
        // 所有过售后的订单
        foreach ($orderList as $order) {
            $orderMoneyCount += $order->total_pay_price;
            $orderIdList[] = $order->id;
        }

        // 售后申请退款的订单详情
        $orderDetailList = OrderDetail::find()->alias('od')->where([
            'od.order_id' => $orderIdList, 'od.is_delete' => 0
        ])->leftJoin(['or' => OrderRefund::tableName()], 'or.order_detail_id=od.id')
            ->andWhere(['or.type' => 1, 'or.status' => 1])->all();

        /* @var OrderDetail[] $orderDetailList */
        foreach ($orderDetailList as $orderDetail) {
            $orderMoneyCount -= $orderDetail->total_price;
        }

        return price_format($orderMoneyCount);
    }
}
