<?php
/**
 * @copyright (c)天幕网络
 * @author Lu Wei
 * @link http://www.67930603.top/
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/30 14:42
 */


namespace app\plugins\booking;


use app\forms\OrderConfig;
use app\forms\PickLinkForm;
use app\helpers\PluginHelper;
use app\plugins\booking\forms\common\CommonBooking;
use app\plugins\booking\forms\common\CommonBookingGoods;
use app\plugins\booking\forms\api\StatisticsForm;
use yii\base\Event;

class Plugin extends \app\plugins\Plugin
{
    public function getMenus()
    {
        return [
            [
                'name' => '基本配置',
                'route' => 'plugin/booking/mall/setting',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '商品分类',
                'route' => 'plugin/booking/mall/cats',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '商品管理',
                'route' => 'plugin/booking/mall/goods/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '商品编辑',
                        'route' => 'plugin/booking/mall/goods/edit',
                    ],
                ]
            ],
            [
                'name' => '订单列表',
                'route' => 'plugin/booking/mall/order/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '订单详情',
                        'route' => 'plugin/booking/mall/order/detail',
                    ],
                    [
                        'name' => '订单列表',
                        'route' => 'plugin/booking/mall/order',
                    ],
                ]
            ]
        ];
    }

    public function handler()
    {
//         $register = new HandlerRegister();
//         $HandlerClasses = $register->getHandlers();
//         foreach ($HandlerClasses as $HandlerClass) {
//             $handler = new $HandlerClass();
//             if ($handler instanceof HandlerBase) {
//                 /** @var HandlerBase $handler */
//                 $handler->register();
//             }
//         }
    }

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'booking';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '预约';
    }

    public function getAppConfig()
    {
        $imageBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img';
        return [
            'app_image' => [
                'scratch_bg' => $imageBaseUrl . '/step-bg.png',
                'scratch_win' => $imageBaseUrl . '/step-win.png'
            ],
        ];
    }

    //商品详情路径
    public function getGoodsUrl($item)
    {
        return sprintf("/plugins/book/goods/goods?goods_id=%u", $item['id']);
    }

    public function getIndexRoute()
    {
        return 'plugin/booking/mall/setting';
    }

    public function getPickLink()
    {
        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img/pick-link';
        return [
            [
                'key' => 'booking',
                'name' => '预约',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-booking.png',
                'value' => '/plugins/book/index/index',
                'params' => [
                    [
                        'key' => 'cat_id',
                        'value' => '',
                        'desc' => '请填写预约分类ID,不填显示全部',
                        'is_required' => false,
                        'data_type' => 'number',
                        'page_url' => 'plugin/booking/mall/cats',
                        'pic_url' => $iconBaseUrl . '/example_image/cat-id.png',
                        'page_url_text' => '分类管理'
                    ]
                ]
            ],
            [
                'key' => 'booking',
                'name' => '预约商品详情',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-booking.png',
                'value' => '/plugins/book/goods/goods',
                'params' => [
                    [
                        'key' => 'goods_id',
                        'value' => '',
                        'desc' => '请填写预约商品ID',
                        'is_required' => true,
                        'data_type' => 'number',
                        'page_url' => 'plugin/booking/mall/goods/index',
                        'pic_url' => $iconBaseUrl . '/example_image/goods-id.png',
                        'page_url_text' => '商品管理'
                    ]
                ],
                'ignore' => [PickLinkForm::IGNORE_TITLE, PickLinkForm::IGNORE_NAVIGATE],
            ],
            [
                'key' => 'booking',
                'name' => '我的预约',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-booking.png',
                'value' => '/plugins/book/order/order',
            ],
        ];
    }

    public function getOrderConfig()
    {
        $setting = (new CommonBooking())->getSetting(\Yii::$app->mall->id);
        $config = new OrderConfig([
            'is_sms' => $setting['is_sms'],
            'is_print' => $setting['is_print'],
            'is_share' => $setting['is_share'],
            'is_mail' => $setting['is_mail'],
            'support_share' => 1,
        ]);
        return $config;
    }

    public function getHomePage($type)
    {
        return CommonBookingGoods::getCommon()->getHomePage($type);
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
            'plugin/booking/api/order/order-preview',
        ];
    }

    public function getStatisticsMenus()
    {
        return [
            'name' => $this->getDisplayName(),
            'key' => $this->getName(),
            'route' => 'mall/booking-statistics/index',
        ];
    }

    public function getGoodsData($array)
    {
        return CommonBookingGoods::getCommon()->getDiyGoods($array);
    }
}
