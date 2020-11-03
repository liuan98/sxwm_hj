<?php
/**
 * @copyright (c)天幕网络
 * @author Lu Wei
 * @link http://www.67930603.top/
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/30 14:42
 */


namespace app\plugins\pintuan;


use app\forms\OrderConfig;
use app\forms\PickLinkForm;
use app\helpers\PluginHelper;
use app\models\GoodsWarehouse;
use app\models\Order;
use app\plugins\pintuan\forms\api\OrderCreatedHandler;
use app\plugins\pintuan\forms\api\OrderPayEventHandler;
use app\plugins\pintuan\forms\api\StatisticsForm;
use app\plugins\pintuan\forms\common\CommonGoods;
use app\plugins\pintuan\forms\common\PintuanFailTemplate;
use app\plugins\pintuan\forms\common\PintuanSuccessTemplate;
use app\plugins\pintuan\forms\common\SettingForm;
use app\plugins\pintuan\models\Goods;
use app\plugins\pintuan\models\PintuanGoods;
use app\plugins\pintuan\models\PintuanGoodsGroups;


class Plugin extends \app\plugins\Plugin
{
    public function getMenus()
    {
        return [
            [
                'name' => '拼团设置',
                'route' => 'plugin/pintuan/mall/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '模板消息',
                'route' => 'plugin/pintuan/mall/index/template',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '商品列表',
                'route' => 'plugin/pintuan/mall/goods/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '商品编辑',
                        'route' => 'plugin/pintuan/mall/goods/edit',
                    ],
                    [
                        'name' => '阶梯团',
                        'route' => 'plugin/pintuan/mall/goods/pintuan',
                    ],
                ]
            ],
            [
                'name' => '商品分类',
                'route' => 'plugin/pintuan/mall/cats',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '订单列表',
                'route' => 'plugin/pintuan/mall/order',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '订单详情',
                        'route' => 'plugin/pintuan/mall/order/detail',
                    ],
                ]
            ],
            [
                'name' => '拼团管理',
                'route' => 'plugin/pintuan/mall/order-groups/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '拼团详情',
                        'route' => 'plugin/pintuan/mall/order-groups/detail',
                    ],
                ]
            ],
            [
                'name' => '轮播图',
                'route' => 'plugin/pintuan/mall/banner',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '拼团广告',
                'route' => 'plugin/pintuan/mall/advertisement',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '机器人设置',
                'route' => 'plugin/pintuan/mall/robot/index',
                'icon' => 'el-icon-star-on',
            ],
        ];
    }

    public function handler()
    {
        \Yii::$app->on(Order::EVENT_CANCELED, function ($event) {
            // 这里开始你的代码
            if ($event->order->sign == $this->getName()) {
                $event->order->status = 1;
                $res = $event->order->save();
                if (!$res) {
                    \Yii::error('拼团订单状态更新失败');
                }
            }
        });
    }

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'pintuan';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '拼团';
    }

    public function getAppConfig()
    {
        $imageBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img';
        return [
            'app_image' => [
                'banner_image' => $imageBaseUrl . '/banner.jpg'
            ],
        ];
    }

    //商品详情路径
    public static function getGoodsUrl($item)
    {
        return sprintf("/plugins/pt/goods/goods?goods_id=%u", $item['id']);
    }

    public function getIndexRoute()
    {
        return 'plugin/pintuan/mall/index';
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
                'key' => 'pintuan',
                'name' => '拼团首页',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-pintuan.png',
                'value' => '/plugins/pt/index/index',
                'params' => [
                    [
                        'key' => 'cat_id',
                        'value' => '',
                        'desc' => '请填写拼团分类ID,不填则显示热销',
                        'is_required' => false,
                        'data_type' => 'number',
                        'page_url' => 'plugin/pintuan/mall/cats',
                        'pic_url' => $iconBaseUrl . '/example_image/cat-id.png',
                        'page_url_text' => '商品分类'
                    ]
                ]
            ],
            [
                'key' => 'pintuan',
                'name' => '我的拼团',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-pintuan.png',
                'value' => '/plugins/pt/order/order',
            ],
            [
                'key' => 'pintuan',
                'name' => '拼团商品详情',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-pintuan.png',
                'value' => '/plugins/pt/goods/goods',
                'params' => [
                    [
                        'key' => 'goods_id',
                        'value' => '',
                        'desc' => '请填写拼团商品ID',
                        'is_required' => true,
                        'data_type' => 'number',
                        'page_url' => 'plugin/pintuan/mall/goods',
                        'pic_url' => $iconBaseUrl . '/example_image/goods-id.png',
                        'page_url_text' => '商品列表'
                    ]
                ],
                'ignore' => [PickLinkForm::IGNORE_TITLE, PickLinkForm::IGNORE_NAVIGATE],
            ],
        ];
    }

    /**
     * @return OrderPayEventHandler
     * @throws \Exception
     * 获取订单支付完成事件
     */
    public function getOrderPayedHandleClass()
    {
        $orderPayedHandlerClass = new OrderPayEventHandler();
        return $orderPayedHandlerClass;
    }

    /**
     * 订单创建完成事件
     * @return \app\handlers\orderHandler\OrderCreatedHandlerClass
     */
    public function getOrderCreatedHandleClass()
    {
        $orderCreatedHandlerClass = new OrderCreatedHandler();
        return $orderCreatedHandlerClass;
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

    public function getHomePage($type)
    {
        return CommonGoods::getCommon()->getHomePage($type);
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
            'plugin/pintuan/api/order/order-preview'
        ];
    }

    public function getStatisticsMenus()
    {
        return [
            'name' => $this->getDisplayName(),
            'key' => $this->getName(),
            'route' => 'mall/pintuan-statistics/index',
        ];
    }

    public function install()
    {
        $sql = <<<EOF
alter table `zjhj_bd_pintuan_order_relation` add cancel_status tinyint(1) not NULL default '0' COMMENT '拼团订单取消状态:0.未取消|1.超出拼团总人数取消';
EOF;
        sql_execute($sql);
        return parent::install();
    }

    public function getGoodsExtra($goods)
    {
        if ($goods->sign != $this->getName()) {
            return [];
        }
        return CommonGoods::getCommon()->getGoodsExtra($goods);
    }

    public function hasVideoGoodsList($goods, $page, $limit)
    {
        $nowDate = date('Y-m-d H:i:s');
        $list = Goods::find()->alias('g')
            ->with(['goodsWarehouse', 'attr'])
            ->where(['g.sign' => $goods->sign, 'g.is_delete' => 0, 'g.status' => 1, 'g.mall_id' => \Yii::$app->mall->id])
            ->andWhere(['!=', 'g.id', $goods->id])
            ->leftJoin(['gw' => GoodsWarehouse::tableName()], 'gw.id=g.goods_warehouse_id')
            ->andWhere(['!=', 'gw.video_url', ''])
            ->leftJoin(['pg' => PintuanGoods::tableName()], 'pg.goods_id=g.id')
            ->andWhere(['<', 'pg.end_time', $nowDate])
            ->leftJoin(['pgg' => PintuanGoodsGroups::tableName(), 'pgg.goods_id=g.id'])
            ->orderBy(['g.sort' => SORT_ASC, 'g.id' => SORT_DESC])
            ->groupBy('g.goods_warehouse_id')
            ->apiPage($limit, $page)
            ->all();
        return $list;
    }

    public function templateList()
    {
        return [
            'pintuan_success_notice' => PintuanSuccessTemplate::class,
            'pintuan_fail_notice' => PintuanFailTemplate::class,
        ];
    }

    public function updateGoodsPrice($goods)
    {
        // TODO 处理拼团阶梯团价
        return true;
    }
}
