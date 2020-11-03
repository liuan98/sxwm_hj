<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/8/21
 * Time: 10:21
 */

namespace app\plugins\vip_card;

use app\core\response\ApiCode;
use app\forms\api\order\OrderException;
use app\forms\api\order\OrderGoodsAttr;
use app\forms\OrderConfig;
use app\handlers\HandlerBase;
use app\models\Goods;
use app\models\GoodsCatRelation;
use app\models\GoodsCats;
use app\models\User;
use app\models\UserIdentity;
use app\forms\PickLinkForm;
use app\helpers\PluginHelper;
use app\plugins\vip_card\forms\common\CommonVip;
use app\plugins\vip_card\forms\common\CommonVipCardSetting;
use app\plugins\vip_card\handlers\HandlerRegister;
use app\plugins\vip_card\handlers\OrderCreatedEventHandler;
use app\plugins\vip_card\handlers\OrderPayEventHandler;
use app\plugins\vip_card\handlers\OrderSalesEventHandler;
use app\plugins\vip_card\models\RemindTemplate;
use app\plugins\vip_card\models\VipCard;
use app\plugins\vip_card\models\VipCardAppointGoods;
use app\plugins\vip_card\models\VipCardDiscount;
use app\plugins\vip_card\models\VipCardUser;

class Plugin extends \app\plugins\Plugin
{
    private static $setting;
    private static $card;
    private static $vipUser;
    private static $user;
    private static $userInfo;

    public function getMenus()
    {
        return [
            [
                'name' => '基础设置',
                'route' => 'plugin/vip_card/mall/setting/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '消息通知',
                'route' => 'plugin/vip_card/mall/setting/template',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '会员卡管理',
                'route' => 'plugin/vip_card/mall/card/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '编辑超级会员卡',
                        'route' => 'plugin/vip_card/mall/card/edit',
                    ],
                    [
                        'name' => '编辑超级会员子卡',
                        'route' => 'plugin/vip_card/mall/card/edit-detail',
                    ],
                    [
                        'name' => '编辑排序',
                        'route' => 'plugin/vip_card/mall/card/edit-sort',
                    ],
                    [
                        'name' => '开关',
                        'route' => 'plugin/vip_card/mall/card/switch-detail-status',
                    ],
                    [
                        'name' => '删除子卡',
                        'route' => 'plugin/vip_card/mall/card/detail-destroy',
                    ],
                ]
            ],
            [
                'name' => '订单管理',
                'route' => 'plugin/vip_card/mall/order/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '会员管理',
                'route' => 'plugin/vip_card/mall/user/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '添加新会员',
                        'route' => 'plugin/vip_card/mall/user/edit',
                    ],
                    [
                        'name' => '删除会员',
                        'route' => 'plugin/vip_card/mall/user/delete',
                    ],
                ]
            ]
        ];
    }

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'vip_card';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '超级会员卡';
    }

    public function getIndexRoute()
    {
        return 'plugin/vip_card/mall/setting/index';
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
                'key' => 'vip_card',
                'name' => '超级会员卡',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-svip.png',
                'value' => '/plugins/vip_card/index/index',
            ],
        ];
    }
    public function getOrderCreatedHandleClass()
    {
        return new OrderCreatedEventHandler();
    }

    public function getOrderPayedHandleClass()
    {
        return new OrderPayEventHandler();
    }

    public function getOrderSalesHandleClass()
    {
        return new OrderSalesEventHandler();
    }

    public function getOrderConfig()
    {
        $setting = (new CommonVipCardSetting())->getSetting();
        $config = new OrderConfig([
            'is_sms' => $setting['is_sms'],
            'is_mail' => $setting['is_mail'],
            'is_print' => 0,
            'is_share' => $setting['is_share'],
            'support_share' => 1,
        ]);

        return $config;
    }

    public function vipDiscount($mchItem)
    {
        /** @var User $user */
        $user = \Yii::$app->user->identity;
        /** @var UserIdentity $identity */
        $identity = $user->getIdentity()->andWhere(['is_delete' => 0,])->one();
        if (!$identity) {
            return $mchItem;
        }
        $vip = VipCardUser::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'user_id' => \Yii::$app->user->id,
        ]);
        if (!$vip) {
            return $mchItem;
        }
        $main = VipCard::findOne(['mall_id' => \Yii::$app->mall->id]);
        if (strtotime($vip->end_time) < time()) {
            return $mchItem;
        }
        $mchItem['vip_discount'] = price_format(0);
        $totalSubPrice = 0; // 超级会员卡总计优惠金额
        foreach ($mchItem['goods_list'] as &$goodsItem) {

            if ($vip->image_is_free_delivery == 1) {
                if ($mchItem['express_price'] != 0 ) {
                    $mchItem['express_price_origin'] = $mchItem['express_price'];
                    $mchItem['express_price_desc'] = ($main->name ?? $vip->image_name).'包邮-￥'.$mchItem['express_price'];
                    $mchItem['express_price'] = price_format(0);
                } else {
                    $mchItem['express_price_desc'] = '';
                }
            }

            $typeInfo = json_decode($vip->image_type_info,true);
            if (is_array($typeInfo) && !empty($typeInfo)) {
                $cat = GoodsCatRelation::find()->select('cat_id')->where(['goods_warehouse_id' => $goodsItem['goods_warehouse_id'], 'is_delete' => 0])->all();
                $cats = array_column($cat,'cat_id');
                $isInGoods = in_array($goodsItem['goods_warehouse_id'],$typeInfo['goods']);
                $isInCats = count(array_intersect($cats,$typeInfo['cats'])) > 0 ? true : false;
                $appoint = VipCardAppointGoods::find()->where(['goods_id' => $goodsItem['id']])->one();
                $isAppoint = !empty($appoint) ? true: false;
                $setting = (new CommonVipCardSetting())->getSetting();
                $setting['rules'][] = '';
                $isRule = in_array($goodsItem['sign'],$setting['rules']) ? true : false;

                if ((($typeInfo['all'] == true) || $isInGoods || $isInCats) && $isAppoint && $isRule) {
                    $vipUnitPrice = null;
                    $discountName = $vip->image_discount."折".($main->name ?? $vip->image_name) . '优惠';

                    $goodsItem['vip_discount'] = price_format(0);

                    /* @var OrderGoodsAttr $goodsAttr */
                    $goodsAttr = $goodsItem['goods_attr'];
                    if (!($vip->image_discount >= 0.1 && $vip->image_discount <= 10)) {
                        throw new OrderException('超级会员卡折扣率不合法，折扣率必须在1折~10折。');
                    }

                    //折上折
                    $vipSubPrice = $goodsItem['total_price'] * (1 - $vip->image_discount / 10);
                    if ($vipSubPrice != 0) {
                        // 减去超级会员卡优惠金额
                        $vipSubPrice = min($goodsItem['total_price'], $vipSubPrice);
                        $goodsItem['total_price'] = price_format($goodsItem['total_price'] - $vipSubPrice);
                        $totalSubPrice += $vipSubPrice;
                        $goodsItem['discounts'][] = [
                            'name' => $discountName,
                            'value' => $vipSubPrice > 0 ?
                                ('-' . price_format($vipSubPrice))
                                : ('+' . price_format(0 - $vipSubPrice))
                        ];
                        $mchItem['total_goods_price'] = price_format($mchItem['total_goods_price'] - $vipSubPrice);
                        $goodsItem['vip_discount'] = price_format($vipSubPrice);
                    }
                }
            }
        }
        if ($totalSubPrice) {
            $mchItem['vip_discount'] = price_format($totalSubPrice);
        }
        return $mchItem;
    }

    public function getSetting()
    {
        return (new CommonVipCardSetting())->getSetting();
    }

    public function getCard()
    {
        $setting = (new CommonVipCardSetting())->getSetting();
        $rules = !empty($setting['rules']) ? $setting['rules'] : '';
        is_array($rules) && $rules[] = '';
        $card = VipCard::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->asArray()->one();
        if (empty($card)) {
            return [];
        }
        $types = json_decode($card['type_info'],true);
        $card['type_info'] = $types;
        $goods = Goods::find()->select('goods_warehouse_id')->where(['id' => $types['goods'], 'sign' => ''])->all();
        $goodsIds = Goods::find()->alias('g')->select('g.id')->where(['g.goods_warehouse_id' => $goods,'g.sign' => $rules,'g.is_delete' => 0])->rightJoin(['ap' => VipCardAppointGoods::tableName()], "g.`id` = ap.`goods_id`")->all();
        $card['type_info']['goods'] = !empty($goodsIds) ? array_unique(array_column($goodsIds,'id')) : [];
        return $card;
    }

    public function getMyCard()
    {
        $card = VipCardUser::find()->where(['mall_id' => \Yii::$app->mall->id,'user_id' => \Yii::$app->user->id, 'is_delete' => 0])->asArray()->one();
        if (empty($card)) {
            return [];
        }
        $setting = (new CommonVipCardSetting())->getSetting();
        $rules = !empty($setting['rules']) ? $setting['rules'] : '';
        is_array($rules) && $rules[] = '';

        $types = json_decode($card['image_type_info'],true);
        $card['image_type_info'] = $types;
        $goods = Goods::find()->select('goods_warehouse_id')->where(['id' => $types['goods'], 'sign' => ''])->all();
        $goodsIds = Goods::find()->alias('g')->select('g.id')->where(['g.goods_warehouse_id' => $goods,'g.sign' => $rules,'g.is_delete' => 0])->rightJoin(['ap' => VipCardAppointGoods::tableName()], "g.`id` = ap.`goods_id`")->all();
        $card['image_type_info']['goods'] = !empty($goodsIds) ? array_unique(array_column($goodsIds,'id')) : [];
        return $card;
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

    public function getUserInfo($user)
    {
        if (self::$userInfo) {
            $user = self::$userInfo;
        } else {
            $user = VipCardUser::find()
                ->where(['mall_id' => \Yii::$app->mall->id,'user_id' => $user->id, 'is_delete' => 0])
                ->one();
            self::$userInfo = $user;
        }

        return [
            'is_vip_card_user' => isset($user->id) ? 1 : 0
        ];
    }

    public function getAppConfig()
    {
        $p = 1;
        $errorMsg = '有会员卡权限';
        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        if (!in_array('vip_card', $permission)) {
            $errorMsg = '无会员卡权限';
            $p = 0;
        }

        $setting = $this->getSetting();

        if ($setting['is_vip_card'] == 0) {
            $errorMsg = '会员卡插件已关闭';
            $p = 0;
        }

        $card = $this->getCard();
        if (!$card) {
            $errorMsg = '尚未添加会员卡';
            $p = 0;
        }

        $form = new CommonVipCardSetting();
        $setting = $form->getSetting();

        $return = ['setting' => $setting,
            'permission' => $p,
            'permission_msg' => $errorMsg,];

        return $return;

    }

    public function getOrderInfo($orderId)
    {
        try {
            $order = VipCardDiscount::find()->where(['order_id' => $orderId])->one();
            if (self::$card) {
                $main = self::$card;
            } else {
                $main = VipCard::findOne(['mall_id' => \Yii::$app->mall->id,'is_delete' => 0]);
                self::$card = $main;
            }
            if ($order) {
                $name = !empty($order->main_name) ? $order->main_name : $main->name;
                $data = [
                    'vip_discount' => [
                        'label' => price_format($order->discount_num,'string',1).'折'.$name.'优惠',
                        'value' => $order->discount
                    ],
                ];
                return $data;
            } else {
                return [];
            }
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getAppoint($goods)
    {
        return CommonVip::getCommon()->getAppoint($goods);
    }

    public function getSmsSetting()
    {
        return [
            'vipCard' => [
                'title' => '超级会员卡续费短信通知',
                'content' => '例如：模板内容：您的超级会员卡即将到期，请及时续费，会员卡名称为${name}',
                'support_mch' => false,
                'loading' => false,
                'variable' => [
                    [
                        'key' => 'name',
                        'value' => '模板变量name',
                        'desc' => '例如：模板内容: "您的超级会员卡即将到期，请及时续费，会员卡名称为${name}"，则需填写name'
                    ],
                ]
            ],
        ];
    }

    public function getGoodsExtra($goods)
    {
        return [
            'vip_card_appoint' => $this->getAppoint($goods),
        ];
    }

    public function templateList()
    {
        return [
            'vip_card_remind' => RemindTemplate::class,
        ];
    }
}
