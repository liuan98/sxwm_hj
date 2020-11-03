<?php
/**
 * @copyright (c)天幕网络
 * @author Lu Wei
 * @link http://www.67930603.top/
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/30 14:42
 */


namespace app\plugins\miaosha;


use app\forms\OrderConfig;
use app\forms\PickLinkForm;
use app\helpers\PluginHelper;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\plugins\miaosha\forms\api\IndexForm;
use app\plugins\miaosha\forms\api\StatisticsForm;
use app\plugins\miaosha\forms\common\CommonGoods;
use app\plugins\miaosha\forms\common\SettingForm;
use app\plugins\miaosha\handler\OrderCreatedEventHandler;
use app\plugins\miaosha\models\MiaoshaGoods;


class Plugin extends \app\plugins\Plugin
{
    public function getMenus()
    {
        return [
            [
                'name' => '秒杀设置',
                'route' => 'plugin/miaosha/mall/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '秒杀商品',
                'route' => 'plugin/miaosha/mall/goods/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '商品编辑',
                        'route' => 'plugin/miaosha/mall/goods/edit',
                    ],
                    [
                        'name' => '商品编辑',
                        'route' => 'plugin/miaosha/mall/goods/miaosha-list',
                    ],
                ]
            ],
            [
                'name' => '订单列表',
                'route' => 'plugin/miaosha/mall/order',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '商品编辑',
                        'route' => 'plugin/miaosha/mall/order/detail',
                    ],
                ]
            ],
        ];
    }

    public function handler()
    {

    }

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'miaosha';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '整点秒杀';
    }

    //商品详情路径
    public function getGoodsUrl($item)
    {
        return sprintf("/plugins/miaosha/goods/goods?id=%u", $item['id']);
    }

    public function getAppConfig()
    {
        $imageBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img';
        return [
            'app_image' => [
                'ms_goods_bg' => $imageBaseUrl . '/ms_goods_bg.png',
                'ms_advance_null' => $imageBaseUrl . '/ms-advance-null.png',
            ],
        ];
    }

    public function getIndexRoute()
    {
        return 'plugin/miaosha/mall/index';
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
                'key' => 'miaosha',
                'name' => '秒杀首页',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-miaosha.png',
                'value' => '/plugins/miaosha/advance/advance',
            ],
            [
                'key' => 'miaosha',
                'name' => '秒杀商品详情',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-miaosha.png',
                'value' => '/plugins/miaosha/goods/goods',
                'params' => [
                    [
                        'key' => 'id',
                        'value' => '',
                        'desc' => '请填写秒杀商品ID',
                        'is_required' => true,
                        'data_type' => 'number',
                        'page_url' => 'plugin/miaosha/mall/goods',
                        'pic_url' => $iconBaseUrl . '/example_image/goods-id.png',
                        'page_url_text' => '商品列表'
                    ]
                ],
                'ignore' => [PickLinkForm::IGNORE_TITLE, PickLinkForm::IGNORE_NAVIGATE],
            ],
        ];
    }

    public function getCartList()
    {
        $form = new IndexForm();
        $res = $form->getCartList();

        return $res;
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

    public function getOrderCreatedHandleClass()
    {
        return new OrderCreatedEventHandler();
    }

    public function getBlackList()
    {
        return [
            'plugin/miaosha/api/order/order-preview'
        ];
    }

    public function getStatisticsMenus()
    {
        return [
            'name' => $this->getDisplayName(),
            'key' => $this->getName(),
            'route' => 'mall/miaosha-statistics/index',
        ];
    }

    public function install()
    {
        return parent::install();
    }


    public function getSignCondition($where)
    {
        $miaoshaGoodsList = MiaoshaGoods::find()
            ->where(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])
            ->andWhere([
                'or',
                ['>', 'open_date', date('Y-m-d')],
                [
                    'and',
                    ['open_date' => date('Y-m-d')],
                    ['>=', 'open_time', date('H')]
                ]
            ])->select('goods_id');
        return $miaoshaGoodsList;
    }

    public function hasVideoGoodsList($goods, $page, $limit)
    {
        $nowDate = date('Y-m-d');
        $H = date('H');
        $list = Goods::find()->alias('g')->with(['goodsWarehouse', 'attr'])->where([
            'g.sign' => $goods->sign, 'g.is_delete' => 0, 'g.status' => 1, 'g.mall_id' => \Yii::$app->mall->id,
        ])->andWhere(['!=', 'g.id', $goods->id])
            ->leftJoin(['gw' => GoodsWarehouse::tableName()], 'gw.id=g.goods_warehouse_id')
            ->andWhere(['!=', 'gw.video_url', ''])
            ->leftJoin(['mg' => MiaoshaGoods::tableName()], 'mg.goods_id=g.id')
            ->andWhere(
                [
                    'and',
                    ['=', 'mg.open_date', $nowDate],
                    ['=', 'mg.open_time', $H]
                ]
            )
            ->orderBy(['g.sort' => SORT_ASC, 'g.id' => SORT_DESC])
            ->groupBy('g.goods_warehouse_id')
            ->apiPage($limit, $page)
            ->all();
        return $list;
    }
}
