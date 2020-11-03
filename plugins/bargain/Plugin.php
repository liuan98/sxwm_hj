<?php
/**
 * @copyright (c)天幕网络
 * @author Lu Wei
 * @link http://www.67930603.top/
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/30 14:42
 */


namespace app\plugins\bargain;

use app\forms\PickLinkForm;
use app\helpers\PluginHelper;
use app\handlers\HandlerBase;
use app\models\Goods;
use app\plugins\bargain\forms\api\StatisticsForm;
use app\plugins\bargain\forms\common\BargainFailTemplate;
use app\plugins\bargain\forms\common\BargainSuccessTemplate;
use app\plugins\bargain\forms\common\CommonSetting;
use app\plugins\bargain\forms\common\goods\CommonBargainGoods;
use app\plugins\bargain\handlers\HandlerRegister;
use app\forms\OrderConfig;

class Plugin extends \app\plugins\Plugin
{
    public function getMenus()
    {
        return [
            [
                'name' => '基础设置',
                'route' => 'plugin/bargain/mall/index/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '轮播图',
                'route' => 'plugin/bargain/mall/index/banner',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '商品管理',
                'route' => 'plugin/bargain/mall/goods/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '商品编辑',
                        'route' => 'plugin/bargain/mall/goods/edit',
                    ],
                ]
            ],
            [
                'name' => '订单管理',
                'route' => 'plugin/bargain/mall/order/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '订单详情',
                        'route' => 'plugin/bargain/mall/order/detail',
                    ],
                ]
            ],
            [
                'name' => '砍价信息',
                'route' => 'plugin/bargain/mall/info/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '模板消息',
                'route' => 'plugin/bargain/mall/index/template',
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
        return $this;
    }

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'bargain';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '砍价';
    }

    //商品详情路径
    public function getGoodsUrl($item)
    {
        return sprintf("/plugins/bargain/goods/goods?goods_id=%u",$item['id']);
    }

    public function getAppConfig()
    {
        $imageBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img';
        return [
            'app_image' => [
                'bargain_time' => $imageBaseUrl . '/icon-bargain-time.png',
                'bargain_hb_good' => $imageBaseUrl . '/bargain-hb-good.png',
                'buy_now' => $imageBaseUrl . '/buy-now.png',
                'buy_small' => $imageBaseUrl . '/buy-small.png',
                'find' => $imageBaseUrl . '/find.png',
                'go_on' => $imageBaseUrl . '/go-on.png',
                'header' => $imageBaseUrl . '/header.png',
                'join_big' => $imageBaseUrl . '/join-big.png',
                'join_small' => $imageBaseUrl . '/join-small.png',
                'top1' => $imageBaseUrl . '/top1.png',
                'top' => $imageBaseUrl . '/top.png',
                'activity_header' => $imageBaseUrl . '/icon-bargain-activity-header-3.gif'
            ],
        ];
    }

    public function getIndexRoute()
    {
        return 'plugin/bargain/mall/index/index';
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
                'key' => 'bargain',
                'name' => '砍价列表',
                'open_type' => 'navigate',
                'icon' => $iconBaseUrl . '/icon-bargain.png',
                'value' => '/plugins/bargain/index/index',
                'ignore' => [PickLinkForm::IGNORE_NAVIGATE],
            ],
            [
                'key' => 'bargain',
                'name' => '我的砍价',
                'open_type' => 'navigate',
                'icon' => $iconBaseUrl . '/icon-bargain-order.png',
                'value' => '/plugins/bargain/order-list/order-list',
                'ignore' => [PickLinkForm::IGNORE_NAVIGATE],
            ],
            [
                'name' => '砍价商品详情',
                'key' => 'bargain',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-bargain.png',
                'value' => '/plugins/bargain/goods/goods',
                'params' => [
                    [
                        'key' => 'goods_id',
                        'value' => '',
                        'desc' => '请填写砍价商品ID',
                        'is_required' => true,
                        'data_type' => 'number',
                        'page_url' => 'plugin/bargain/mall/goods/index',
                        'pic_url' => $iconBaseUrl . '/example_image/goods-id.png',
                        'page_url_text' => '商品管理->商品列表'
                    ]
                ],
                'ignore' => [PickLinkForm::IGNORE_TITLE, PickLinkForm::IGNORE_NAVIGATE],
            ],
        ];
    }

    public function getOrderConfig()
    {
        $setting = CommonSetting::getCommon()->getList();
        $config = new OrderConfig([
            'is_sms' => $setting['is_sms'],
            'is_print' => $setting['is_print'],
            'is_share' => $setting['is_share'],
            'is_mail' => $setting['is_mail'],
            'support_share' => 1,
        ]);
        return $config;
    }

    public function getGoodsData($array)
    {
        return CommonBargainGoods::getCommonGoods()->getDiyGoods($array);
    }

    /**
     * 返回实例化后台统计数据接口
     * @return StatisticsForm
     */
    public function getApi()
    {
        return new StatisticsForm();
    }

    public function initData()
    {
        $form = new StatisticsForm();
        $form->initData();
        return true;
    }

    
    public function getBlackList()
    {
        return [
            'plugin/bargain/api/order/order-preview',
            'plugin/bargain/api/order/bargain-submit',
        ];
    }

    public function getStatisticsMenus()
    {
        return [
            'name' => $this->getDisplayName(),
            'key' => $this->getName(),
            'route' => 'mall/bargain-statistics/index',
        ];
    }

    public function install()
    {
        $sql = <<<EOF
        -- v1.0.4
ALTER TABLE `zjhj_bd_bargain_banner` ADD COLUMN `deleted_at` timestamp NOT NULL AFTER `created_at`;
EOF;
        sql_execute($sql);
        return parent::update();
    }

    /**
     * @param Goods $goods
     * @param int $page
     * @param int $limit
     * @return Goods[]
     */
    public function hasVideoGoodsList($goods, $page, $limit)
    {
        return CommonBargainGoods::getCommonGoods()->hasVideoGoodsList($goods, $page, $limit);
    }

    public function templateList()
    {
        return [
            'bargain_success_tpl' => BargainSuccessTemplate::class,
            'bargain_fail_tpl' => BargainFailTemplate::class,
        ];
    }
}
