<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/19
 * Time: 10:27
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\plugins\bargain\forms\api;


use app\forms\api\order\OrderException;
use app\plugins\bargain\forms\common\CommonBargainOrder;
use app\plugins\bargain\forms\common\CommonSetting;
use app\plugins\bargain\models\BargainGoods;
use app\plugins\bargain\models\BargainOrder;
use app\plugins\bargain\Plugin;

/**
 * @property BargainOrder $bargainOrder
 * @property BargainGoods $bargainGoods
 */
class OrderSubmitForm extends \app\forms\api\order\OrderSubmitForm
{
    public $bargainOrder;
    public $bargainGoods;

    public function setPluginData()
    {
        $setting = CommonSetting::getCommon()->getList();
        $this->setSign(\Yii::$app->plugin->getCurrentPlugin()->getName())
            ->setSupportPayTypes($setting['payment_type'])
            ->setEnableMemberPrice(false)
            ->setEnablePriceEnable(false);
        return $this;
    }

    protected function checkGoods($goods, $item)
    {
        $bargainGoods = BargainGoods::findOne(['goods_id' => $goods->id, 'is_delete' => 0]);
        if (!$bargainGoods) {
            throw new OrderException('砍价活动不存在');
        }

        if ($goods->status == 0) {
            throw new OrderException('砍价活动已关闭');
        }

        if (strtotime($bargainGoods->end_time) <= time()) {
            throw new OrderException('砍价活动已结束');
        }

        $commonBargainOrder = CommonBargainOrder::getCommonBargainOrder(\Yii::$app->mall);
        /* @var BargainOrder $bargainOrder */
        $bargainOrder = $commonBargainOrder->getUserOrder($bargainGoods->id, \Yii::$app->user->id);
        if (!$bargainOrder) {
            throw new OrderException('砍价已购买或不存在');
        }
        if ($bargainOrder->resetTime <= 0) {
            throw new OrderException('砍价活动已结束');
        }

        $bargainUserOrderList = $bargainOrder->userOrderList;
        $totalPrice = array_sum(array_column($bargainUserOrderList, 'price'));
        if (round($bargainOrder->price - $bargainOrder->min_price, 2) > round($totalPrice, 2) && $bargainGoods->type == 2) {
            throw new OrderException('不允许中途下单');
        }

        if ($bargainOrder->order) {
            throw new OrderException('已下单购买');
        }

        $this->bargainOrder = $bargainOrder;
        $this->bargainGoods = $bargainGoods;

        return true;
    }

    public function getGoodsAttr($goodsAttrId, $goods)
    {
        $newGoodsAttr = parent::getGoodsAttr($goodsAttrId, $goods);

        $bargainOrder = $this->bargainOrder;
        $bargainUserOrderList = $bargainOrder->userOrderList;
        $totalPrice = array_sum(array_column($bargainUserOrderList, 'price'));
        $resetPrice = $bargainOrder->getNowPrice($totalPrice);
        $newGoodsAttr->original_price = $resetPrice;
        $newGoodsAttr->price = $resetPrice;

        return $newGoodsAttr;
    }

    // 砍价下单不需要判断库存
    public function subGoodsNum($goodsAttr, $subNum, $goodsItem)
    {
        return true;
    }

    public function checkGoodsStock($goodsList)
    {
        return true;
    }

    protected function getToken()
    {
        if (!$this->bargainOrder) {
            foreach ($this->form_data['list'] as $formMchItem) {
                $commonBargainOrder = CommonBargainOrder::getCommonBargainOrder(\Yii::$app->mall);
                /* @var BargainOrder $bargainOrder */
                $this->bargainOrder = $commonBargainOrder->getBargainOrder($formMchItem['bargain_order_id']);
                break;
            }
        }
        return $this->bargainOrder->token;
    }
    
    protected function getSendType($mchItem)
    {
        $setting = CommonSetting::getCommon()->getList();
        return $setting['send_type'];
    }
}
