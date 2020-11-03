<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */


namespace app\plugins\integral_mall;


use app\forms\OrderConfig;
use app\forms\PickLinkForm;
use app\handlers\HandlerBase;
use app\helpers\PluginHelper;
use app\models\Order;
use app\plugins\integral_mall\forms\api\StatisticsForm;
use app\plugins\integral_mall\forms\common\CommonGoods;
use app\plugins\integral_mall\handlers\HandlerRegister;
use app\plugins\integral_mall\forms\common\SettingForm;
use app\plugins\integral_mall\models\Goods;


class Plugin extends \app\plugins\Plugin
{
    public function getMenus()
    {
        return [
            [
                'name' => '积分商城设置',
                'route' => 'plugin/integral_mall/mall/setting/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '轮播图',
                'route' => 'plugin/integral_mall/mall/slide/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '商品管理',
                'route' => 'plugin/integral_mall/mall/goods/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '商品编辑',
                        'route' => 'plugin/integral_mall/mall/goods/edit',
                    ],
                ]
            ],
            [
                'name' => '商品分类',
                'route' => 'plugin/integral_mall/mall/cats',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '优惠券管理',
                'route' => 'plugin/integral_mall/mall/coupon/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '用户兑换券',
                'route' => 'plugin/integral_mall/mall/user-coupon/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '订单列表',
                'route' => 'plugin/integral_mall/mall/order/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '订单详情',
                        'route' => 'plugin/integral_mall/mall/order/detail',
                    ],
                ]
            ]
        ];
    }

    public function handler()
    {
        $register = new HandlerRegister();
        $HandlerClasses = $register->getHandlers();
        foreach ($HandlerClasses as $HandlerClass) {
            $handler = new $HandlerClass();
            if ($handler instanceof HandlerBase) {
                /** @var HandlerBase $handler */
                $handler->register();
            }
        }
        return $this;
    }

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'integral_mall';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '积分商城';
    }

    public function getAppConfig()
    {
        $imageBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img';
        return [
            'app_image' => [
                'banner_image' => $imageBaseUrl . '/banner.jpg',
                'success' => $imageBaseUrl . '/success.png',
            ],
        ];
    }

    public function getIndexRoute()
    {
        return 'plugin/integral_mall/mall/setting/index';
    }

    //商品详情路径
    public function getGoodsUrl($item)
    {
        return sprintf("/plugins/integral_mall/goods/goods?goods_id=%u",$item['id']);
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
                'key' => 'integral_mall',
                'name' => '积分商城',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-integral.png',
                'value' => '/plugins/integral_mall/index/index',
            ],
            [
                'key' => 'integral_mall',
                'name' => '积分商品详情',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-integral.png',
                'value' => '/plugins/integral_mall/goods/goods',
                'params' => [
                    [
                        'key' => 'goods_id',
                        'value' => '',
                        'desc' => '请填写积分商品ID',
                        'is_required' => true,
                        'data_type' => 'number',
                        'page_url' => 'plugin/integral_mall/mall/goods/index',
                        'pic_url' => $iconBaseUrl . '/example_image/goods-id.png',
                        'page_url_text' => '商品管理'
                    ]
                ],
                'ignore' => [PickLinkForm::IGNORE_TITLE, PickLinkForm::IGNORE_NAVIGATE],
            ],
        ];
    }

    public function getOrderConfig()
    {
        $setting = (new SettingForm())->search();
        $config = new OrderConfig([
            'is_sms' => $setting['is_sms'],
            'is_print' => $setting['is_print'],
            'is_mail' => $setting['is_mail'],
            'is_share' => $setting['is_share'],
            'support_share' => 1,
        ]);

        return $config;
    }

    public function getGoodsData($array)
    {
        return CommonGoods::getCommon()->getDiyGoods($array);
    }

    /**
     * 返回实例化后台统计数据接口
     * @return IntegralForm
     */
    public function getApi()
    {
        return new StatisticsForm();
    }

    public function getBlackList()
    {
        return [
            'plugin/integral_mall/api/order/order-preview'
        ];
    }

    public function getStatisticsMenus()
    {
        return [
            'name' => $this->getDisplayName(),
            'key' => $this->getName(),
            'route' => 'mall/integral-statistics/mall',
        ];
    }

    public function getGoodsExtra($goods)
    {
        if ($goods->sign != $this->getName()) {
            return [];
        }
        /* @var Goods $goods */
        return [
            'price_content' => $goods->integralMallGoods->integral_num . '积分' . '+￥' . $goods->getPrice()
        ];
    }
}
