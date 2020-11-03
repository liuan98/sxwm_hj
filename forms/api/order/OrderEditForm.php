<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\forms\api\order;


use app\core\Plugin;
use app\core\response\ApiCode;
use app\forms\api\goods\MallGoods;
use app\forms\common\CommonDelivery;
use app\forms\common\order\CommonOrderDetail;
use app\forms\common\template\TemplateList;
use app\models\Model;
use app\models\Order;
use app\models\OrderRefund;
use yii\helpers\ArrayHelper;

class OrderEditForm extends Model
{
    public $id;// 订单ID
    public $action_type;//操作订单的类型,1 订单核销详情|

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id'], 'integer'],
            [['action_type'], 'string'],
        ];
    }

    public function getDetail()
    {
        try {
            if (!$this->validate()) {
                return $this->getErrorResponse();
            }

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
            $order['status_text'] = (new Order())->orderStatusText($order);
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
                /** @var Plugin $pluginObject */
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

            $order['template_message_list'] = $this->getTemplateMessage();

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

    private function getTemplateMessage()
    {
        $arr = ['order_cancel_tpl'];
        $list = TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, $arr);
        return $list;
    }
}
