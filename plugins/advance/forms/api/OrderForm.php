<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: zbj
 */

namespace app\plugins\advance\forms\api;

use app\core\response\ApiCode;
use app\forms\api\goods\MallGoods;
use app\forms\common\CommonDelivery;
use app\forms\common\order\CommonOrderDetail;
use app\models\Model;
use app\models\OrderRefund;
use app\plugins\advance\models\AdvanceOrder;
use app\plugins\advance\models\Order;
use app\plugins\advance\Plugin;

class OrderForm extends Model
{
    public $id;
    public $page;

    public function rules()
    {
        return [
            [['id', 'page'], 'integer'],
            [['page'], 'default', 'value' => 1],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $list = Order::find()->where([
            'user_id' => \Yii::$app->user->id,
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
            'sign' => (new Plugin())->getName()
        ])
            ->with(['detail.goods.goodsWarehouse', 'advanceOrder'])
            ->orderBy(['created_at' => SORT_DESC])
            ->page($pagination)->asArray()->all();

        foreach ($list as $lKey => $lItem) {
            foreach ($lItem['detail'] as $dKey => $dItem) {
                $goodsInfo = \Yii::$app->serializer->decode($dItem['goods_info']);
                $picUrl = isset($goodsInfo['goods_attr']['pic_url']) ? $goodsInfo['goods_attr']['pic_url'] : '';
                $coverPic = isset($dItem['goods']['cover_pic']) ? $dItem['goods']['cover_pic'] : '';
                $goodsInfo['goods_attr']['pic_url'] = $picUrl ?: $coverPic;
                $goodsInfo['name'] = $dItem['goods']['goodsWarehouse']['name'];
                $list[$lKey]['detail'][$dKey]['goods_info'] = $goodsInfo;
            }
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    public function detail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $form = new CommonOrderDetail();
            $form->id = $this->id;
            $form->is_detail = 1;
            $form->is_goods = 1;
            $form->is_refund = 1;
            $form->is_array = 1;
            $form->is_store = 1;
            $form->relations = ['detailExpress.expressRelation.orderDetail', 'detailExpressRelation'];
            $form->is_vip_card = 1;
            $order = $form->search();

            if (!$order) {
                throw new \Exception('订单不存在');
            }

            $goodsNum = 0;
            $memberDeductionPriceCount = 0;
            // 统一商品信息，用于前端展示
            $orderRefund = new OrderRefund();
            foreach ($order['detail'] as $key => &$item) {
                $goodsNum += $item['num'];
                $memberDeductionPriceCount += $item['member_discount_price'];
                $goodsInfo = MallGoods::getGoodsData($item);
                // 售后订单 状态
                if (isset($item['refund'])) {
                    $item['refund']['status_text'] = $orderRefund->statusText($item['refund']);
                }

                $item['goods_info'] = $goodsInfo;
            }

            foreach ($order['detailExpress'] as &$detailExpress) {
                foreach ($detailExpress['expressRelation'] as &$expressRelation) {
                    $expressRelation['orderDetail']['goods_info'] = \Yii::$app->serializer->decode($expressRelation['orderDetail']['goods_info']);
                }
                unset($expressRelation);
            }
            unset($detailExpress);
            // 订单状态
            $order['status_text'] = (new \app\models\Order())->orderStatusText($order);
            $order['pay_type_text'] = (new Order())->getPayTypeText($order['pay_type']);
            // 订单商品总数
            $order['goods_num'] = $goodsNum;
            $order['member_deduction_price_count'] = price_format($memberDeductionPriceCount);
            $order['city'] = json_decode($order['city_info'], true);
            if ($order['send_type'] == 2) {
                $order['delivery_config'] = CommonDelivery::getInstance()->getConfig();
            }

            $plugins = \Yii::$app->plugin->list;
            $order['plugin_data'] = [];
            $newData = [];

            foreach ($plugins as $plugin) {
                $PluginClass = 'app\\plugins\\' . $plugin->name . '\\Plugin';
                /** @var \app\core\Plugin $pluginObject */
                if (!class_exists($PluginClass)) {
                    continue;
                }
                $object = new $PluginClass();
                if (method_exists($object, 'getOrderInfo')) {
                    $data = $object->getOrderInfo($order['id']);
                    if ($data && is_array($data)) {
                        foreach ($data as $datum) {
                            $newData[] = $datum;
                        }
                    }
                    $order['plugin_data'] = $newData;
                }
            }

            // 兼容发货方式
            try {
                $order['is_offline'];
            } catch (\Exception $exception) {
                $order['is_offline'] = $order['send_type'];
            }

            $order['advance_order'] = AdvanceOrder::find()->where(['order_id' => $order['id']])->asArray()->one();
            if (!empty($order['advance_order'])) {
                $order['advance_order']['deposit'] *= $order['advance_order']['goods_num'];
                $order['advance_order']['swell_deposit'] *= $order['advance_order']['goods_num'];
                $order['advance_order']['preferential_price'] *= $order['advance_order']['goods_num'];
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $order
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}
