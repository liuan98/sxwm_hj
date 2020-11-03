<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\scan_code_pay;


use app\forms\OrderConfig;
use app\helpers\PluginHelper;
use app\plugins\scan_code_pay\forms\common\CommonScanCodePaySetting;
use app\plugins\scan_code_pay\handlers\OrderCancelEventHandler;
use app\plugins\scan_code_pay\handlers\OrderCreatedEventHandler;
use app\plugins\scan_code_pay\handlers\OrderPayEventHandler;
use app\plugins\scan_code_pay\handlers\OrderSalesEventHandler;
use app\plugins\scan_code_pay\models\ScanCodePayOrders;

class Plugin extends \app\plugins\Plugin
{
    public function getMenus()
    {
        return [
            [
                'name' => '基础设置',
                'route' => 'plugin/scan_code_pay/mall/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '买单设置',
                'route' => 'plugin/scan_code_pay/mall/activity/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '买单编辑',
                        'route' => 'plugin/scan_code_pay/mall/activity/edit',
                    ],
                ]
            ],
            [
                'name' => '订单列表',
                'route' => 'plugin/scan_code_pay/mall/order/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '订单详情',
                        'route' => 'plugin/scan_code_pay/mall/order/detail',
                    ],
                ]
            ],
        ];
    }

    public function handler()
    {

    }

    public function getName()
    {
        return 'scan_code_pay';
    }

    public function getDisplayName()
    {
        return '当面付';
    }

    public function getIndexRoute()
    {
        return 'plugin/scan_code_pay/mall/index';
    }

    public function getOrderCreatedHandleClass()
    {
        return new OrderCreatedEventHandler();
    }

    public function getOrderPayedHandleClass()
    {
        return new OrderPayEventHandler();
    }

    public function getOrderCanceledHandleClass()
    {
        return new OrderCancelEventHandler();
    }

    public function getOrderSalesHandleClass()
    {
        return new OrderSalesEventHandler();
    }

    public function getOrderConfig()
    {
        $setting = (new CommonScanCodePaySetting())->getSetting();
        $config = new OrderConfig([
            'is_sms' => $setting['is_sms'],
            'is_mail' => $setting['is_mail'],
            'is_print' => 1,
            'is_share' => $setting['is_share'],
            'support_share' => 1,
        ]);

        return $config;
    }

    /**
     * 插件小程序端链接
     * @return array
     */
    public function getPickLink()
    {
        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img/pick-link';

        return [
            [
                'key' => 'scan_code_pay',
                'name' => '当面付',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-scan-code-pay.png',
                'value' => '/plugins/scan_code/index/index',
            ],
        ];
    }

    //商品详情路径
    public static function getGoodsUrl($item)
    {
        return sprintf("");
    }

    public function getOrderInfo($orderId)
    {
        $order = ScanCodePayOrders::findOne(['order_id' => $orderId]);
        if ($order) {
            $data = [
                'activity_preferential_price' => [
                    'label' => '活动优惠',
                    'value' => '-' . $order->activity_preferential_price
                ]
            ];
            return $data;
        }

        return [];
    }
}