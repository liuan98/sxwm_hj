<?php
/**
 * @copyright (c)天幕网络
 * @author Lu Wei
 * @link http://www.67930603.top/
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/30 14:42
 */


namespace app\plugins\mch;


use app\forms\common\mch\SettingForm;
use app\forms\common\template\TemplateSend;
use app\forms\OrderConfig;
use app\forms\PickLinkForm;
use app\helpers\PluginHelper;
use app\models\Order;
use app\models\User;
use app\plugins\mch\forms\api\OrderPayEventHandler;
use app\plugins\mch\forms\common\CommonMchForm;
use app\plugins\mch\forms\common\MchOrderTemplate;
use app\plugins\mch\models\Mch;
use app\plugins\mch\forms\mall\CashEditForm;
use app\plugins\mch\forms\mall\CashForm;
use app\plugins\mch\forms\mall\MchEditForm;
use app\plugins\mch\forms\mall\MchForm;
use app\plugins\mch\forms\mall\MchReviewForm;
use app\plugins\mch\models\MchSetting;
use app\plugins\wxapp\models\WxappTemplate;

class Plugin extends \app\plugins\Plugin
{
    public function getMenus()
    {
        return [
            [
                'name' => '多商户设置',
                'route' => 'plugin/mch/mall/setting/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '模板消息',
                'route' => 'plugin/mch/mall/setting/template',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '商户列表',
                'route' => 'plugin/mch/mall/mch/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '商户编辑',
                        'route' => 'plugin/mch/mall/mch/edit',
                    ],
                    [
                        'name' => '商户编辑',
                        'route' => 'plugin/mch/mall/mch/mall-setting',
                    ],
                ]
            ],
            [
                'name' => '入驻审核',
                'route' => 'plugin/mch/mall/mch/review',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '所售类目',
                'route' => 'plugin/mch/mall/common-cat/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '类目编辑',
                        'route' => 'plugin/mch/mall/common-cat/edit',
                    ],
                ]
            ],
            [
                'name' => '提现管理',
                'route' => 'plugin/mch/mall/cash/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '商品管理',
                'route' => 'plugin/mch/mall/goods/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '商品编辑',
                        'route' => 'plugin/mch/mall/goods/edit',
                    ],
                ]
            ],
            [
                'name' => '订单管理',
                'route' => 'plugin/mch/mall/order/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '订单详情',
                        'route' => 'plugin/mch/mall/order/detail',
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
        return 'mch';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '多商户';
    }

    public function getAppConfig()
    {
        $imageBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img';
        return [
            'app_image' => [
                'banner_image' => $imageBaseUrl . '/banner.jpg',
                'mch_account_header_bg' => $imageBaseUrl . '/mch-account-header-bg.png',
                'qrcode_header_bg' => $imageBaseUrl . '/qrcode-header-bg.png',
                'shop_logo' => $imageBaseUrl . '/shop-logo.png',
                'wechat' => $imageBaseUrl . '/wechat.png',
                'alipay' => $imageBaseUrl . '/alipay.png',
                'baidu' => $imageBaseUrl . '/baidu.png',
                'byte_dance' => $imageBaseUrl . '/byte-dance.png',
                'success' => $imageBaseUrl . '/success.png',
                'error' => $imageBaseUrl . '/error.png',
                'load' => $imageBaseUrl . '/load.png',
                'mch_login_bg' => $imageBaseUrl . '/mch-login-bg.png'
            ],
        ];
    }

    public function getIndexRoute()
    {
        return 'plugin/mch/mall/setting/index';
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
                'key' => 'mch',
                'name' => '好店推荐',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-store-recommend.png',
                'value' => '/plugins/mch/list/list',
            ],
            [
                'key' => 'mch',
                'name' => '多商户店铺',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-mch.png',
                'value' => '/plugins/mch/shop/shop',
                'params' => [
                    [
                        'key' => 'mch_id',
                        'value' => '',
                        'desc' => '请填写入驻商户ID',
                        'is_required' => true,
                        'data_type' => 'number',
                        'page_url' => 'plugin/mch/mall/mch/index',
                        'pic_url' => $iconBaseUrl . '/example_image/mch-id.png',
                        'page_url_text' => '商户列表'
                    ]
                ]
            ],
            [
                'key' => 'mch',
                'name' => '入驻商',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-enter.png',
                'value' => '/plugins/mch/mch/myshop/myshop',
            ],
            [
                'key' => 'mch',
                'name' => '多商户商品',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-enter.png',
                'value' => '/plugins/mch/goods/goods',
                'params' => [
                    [
                        'key' => 'id',
                        'value' => '',
                        'desc' => "填写商品ID",
                        'is_required' => true,
                        'data_type' => 'number',
                        'pic_url' => $iconBaseUrl . '/example_image/goods-id.png',
                    ],
                    [
                        'key' => 'mch_id',
                        'value' => '',
                        'desc' => "填写店铺的ID",
                        'is_required' => true,
                        'data_type' => 'number',
                        'pic_url' => $iconBaseUrl . '/example_image/mch-id.png',
                    ],
                ],
                'ignore' => [PickLinkForm::IGNORE_TITLE, PickLinkForm::IGNORE_NAVIGATE],
            ],
        ];
    }

    //商品详情路径
    public function getGoodsUrl($item)
    {
        return sprintf("/plugins/mch/goods/goods?id=%u&mch_id=%u", $item['id'], $item['mch_id']);
    }

    public function getHomePage($type)
    {
        $common = new CommonMchForm();
        return $common->getHomePage($type);
    }

    public function getOrderConfig()
    {
        $setting = MchSetting::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->mchId,
        ])->one();
        if (!$setting) {
            $setting = (new SettingForm())->getDefault();
        }
        $config = new OrderConfig([
            'is_sms' => $setting['is_sms'],
            'is_print' => $setting['is_print'],
            'is_mail' => $setting['is_mail'],
            'is_share' => $setting['is_share'],
            'support_share' => 1,
        ]);

        return $config;
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

    public function getMchReview()
    {
        $form = new MchReviewForm();
        return $form;
    }

    public function getMch()
    {
        $form = new MchForm();
        return $form;
    }

    public function getCashForm()
    {
        $form = new CashForm();
        return $form;
    }

    public function getCashEditForm()
    {
        $form = new CashEditForm();
        return $form;
    }

    public function getMchEdit()
    {
        $form = new MchEditForm();
        return $form;
    }

    public function getBlackList()
    {
        return [
            'plugin/mch/api/order/preview',
        ];
    }

    public function getStatisticsMenus()
    {
        return [
            'key' => $this->getName(),
            'name' => $this->getDisplayName(),
            'route' => 'mall/order-statistics/mch',
        ];
    }

    public function install()
    {
        $sql = <<<EOF
-- v1.0.4
ALTER TABLE `zjhj_bd_mch` ALTER column `user_id` SET DEFAULT '0';
EOF;
        sql_execute($sql);
        return parent::install();
    }

    public function templateList()
    {
        return [
            'mch_order_tpl' => MchOrderTemplate::class,
        ];
    }
}
