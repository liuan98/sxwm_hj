<?php

namespace app\plugins\step;

use app\forms\OrderConfig;
use app\forms\PickLinkForm;
use app\helpers\PluginHelper;
use app\handlers\HandlerBase;
use app\plugins\step\forms\api\StatisticsForm;
use app\plugins\step\forms\common\CommonStep;
use app\plugins\step\forms\common\CommonStepGoods;
use app\plugins\step\forms\common\StepNoticeTemplate;
use app\plugins\step\handlers\HandlerRegister;

class Plugin extends \app\plugins\Plugin
{
    public function getMenus()
    {
        return [
            [
                'name' => '基本配置',
                'route' => 'plugin/step/mall/setting',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '用户列表',
                'route' => 'plugin/step/mall/user',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '兑换详情',
                        'route' => 'plugin/step/mall/user/log',
                    ],
                ]
            ],
            [
                'name' => '流量主',
                'route' => 'plugin/step/mall/ad',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '步数挑战',
                'route' => 'plugin/step/mall/activity',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '商品列表',
                'route' => 'plugin/step/mall/goods/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '商品编辑',
                        'route' => 'plugin/step/mall/goods/edit',
                    ],
                ]
            ],
            [
                'name' => '轮播图',
                'route' => 'plugin/step/mall/banner',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '订单管理',
                'route' => 'plugin/step/mall/order',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '订单详情',
                        'route' => 'plugin/step/mall/order/detail',
                    ],
                    [
                        'name' => '订单列表',
                        'route' => 'plugin/step/mall/order/index',
                    ],
                ]
            ],
            [
                'name' => '模板消息',
                'route' => 'plugin/step/mall/setting/template',
                'icon' => 'el-icon-star-on',
            ],
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
    }

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'step';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '步数宝';
    }

    //商品详情路径
    public static function getGoodsUrl($item)
    {
        return sprintf("/plugins/step/goods/goods?goods_id=%u", $item['id']);
    }

    public function getAppConfig()
    {
        $imageBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img';
        return [
            'app_image' => [
                'activity_bg' => $imageBaseUrl . '/activity-bg.png',
                'activity_log_bg' => $imageBaseUrl . '/activity-log-bg.png',
                'bg' => $imageBaseUrl . '/bg.png',
                'daily' => $imageBaseUrl . '/daily.png',
                'daily_info' => $imageBaseUrl . '/daily-info.png',
                'top_bg' => $imageBaseUrl . '/top-bg.png',
                'ba' => $imageBaseUrl . '/ba.png'
            ],
        ];
    }

    public function getIndexRoute()
    {
        return 'plugin/step/mall/setting';
    }

    public function getPickLink()
    {
        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img/pick-link';

        return [
            [
                'key' => 'step',
                'name' => '步数宝',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-step.png',
                'value' => '/plugins/step/index/index',
                'ignore' => [],
            ],
            [
                'key' => 'step',
                'name' => '步数宝商品详情',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-step.png',
                'value' => '/plugins/step/goods/goods',
                'params' => [
                    [
                        'key' => 'goods_id',
                        'value' => '',
                        'desc' => '请填写步数宝商品ID',
                        'is_required' => true,
                        'data_type' => 'number',
                        'page_url' => 'plugin/step/mall/goods',
                        'pic_url' => $iconBaseUrl . '/example_image/goods-id.png',
                        'page_url_text' => '商品列表'
                    ]
                ],
                'ignore' => [PickLinkForm::IGNORE_TITLE, PickLinkForm::IGNORE_NAVIGATE],
            ],
        ];
    }

    public function getOrderConfig()
    {
        $setting = CommonStep::getSetting();
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
        return CommonStepGoods::getCommon()->getDiyGoods($array);
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
            'plugin/step/api/step/order-preview',
        ];
    }

    public function getStatisticsMenus()
    {
        return [
            [
                'name' => '步数挑战',
                'key' => $this->getName(),
                'route' => 'mall/step-statistics/index',
            ],
            [
                'name' => '步数兑换',
                'key' => $this->getName(),
                'route' => 'mall/step-statistics/ex',
            ],
        ];
//        return [
//            'name' => $this->getDisplayName(),
//            'key' => $this->getName(),
//            'route' => '',
//            'children' => [
//                [
//                    'name' => '步数挑战',
//                    'route' => 'mall/step-statistics/index',
//                ],
//                [
//                    'name' => '步数兑换',
//                    'route' => 'mall/step-statistics/ex',
//                ]
//            ]
//        ];
    }

    public function templateList()
    {
        return [
            'step_notice' => StepNoticeTemplate::class,
        ];
    }
}
