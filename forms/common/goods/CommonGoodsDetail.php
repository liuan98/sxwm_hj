<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/25
 * Time: 15:00
 */

namespace app\forms\common\goods;

use app\forms\common\CommonMallMember;
use app\forms\common\CommonOption;
use app\forms\common\share\CommonShareLevel;
use app\forms\common\video\Video;
use app\models\Address;
use app\models\Favorite;
use app\models\FootprintGoodsLog;
use app\models\FreeDeliveryRules;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\GoodsCards;
use app\models\GoodsMemberPrice;
use app\models\GoodsServices;
use app\models\GoodsShare;
use app\models\Mall;
use app\models\MallMembers;
use app\models\Model;
use app\models\Option;
use app\models\PostageRules;
use app\models\ShareSetting;
use app\models\User;
use app\plugins\vip_card\models\VipCardUser;
use yii\helpers\ArrayHelper;

/**
 * @property Mall $mall
 * @property Goods $goods
 * @property User $user
 */
class CommonGoodsDetail extends Model
{
    public $mall;
    public $user;
    public $goods;
    public $mch_id = 0;

    protected $attr;
    protected $goodNum;
    protected $goodNo;
    protected $goodWeight;
    protected $attrGroup;
    protected $option;
    protected $service;
    protected $cards;
    protected $favorite;
    protected $share;
    protected $sales;
    protected $isShare = true;
    protected $isMember = true;
    protected $isSales = true;
    protected $isExpress = false; // 是否单件包邮

    /**
     * @param null $mall
     * @return CommonGoodsDetail
     * 使用静态方法实例化一个当前类
     */
    public static function getCommonGoodsDetail($mall = null)
    {
        $form = new self();
        $form->mall = $mall;
        return $form;
    }

    /**
     * @param $id
     * @return array|\yii\db\ActiveRecord|null|Goods
     * 通过指定商品id获取商品对象
     */
    public function getGoods($id)
    {
        $goods = Goods::find()->with(['attr', 'share', 'cards', 'services', 'goodsWarehouse.cats'])
            ->where([
                'is_delete' => 0,
                'id' => $id,
                'mall_id' => $this->mall->id,
                'mch_id' => $this->mch_id
            ])->one();
        if ($goods && !\Yii::$app->user->isGuest) {
            //记录足迹
            $model = FootprintGoodsLog::find()->where(['goods_id' => $id, 'user_id' => \Yii::$app->user->id])->andWhere(['>', 'created_at', date('Y-m-d 00:00:00', time())])->one();
            if (empty($model)) {
                $model = new FootprintGoodsLog();
                $model->mall_id = \Yii::$app->mall->id;
                $model->user_id = \Yii::$app->user->id;
                $model->goods_id = $id;
            }
            $model->save();
        }
        return $goods;
    }

    /**
     * @return array
     * @throws \Exception
     * 获取规格
     */
    public function getAttr()
    {
        if (!$this->attr) {
            $this->setAttr();
        }
        return $this->attr;
    }

    /**
     * @param $attr
     * @throws \Exception
     * 设置规格
     */
    public function setAttr($attr = null)
    {
        if (!$this->goods) {
            throw new \Exception('请先设置商品对象');
        }
        if (!$attr) {
            $attr = $this->goods->attr;
        }
        $newAttr = [];
        $attrGroup = \Yii::$app->serializer->decode($this->goods->attr_groups);
        $attrList = $this->goods->resetAttr($attrGroup);
        /* @var GoodsAttr[] $attr */
        foreach ($attr as $key => $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['attr_list'] = $attrList[$item['sign_id']];
            $newItem['price_member'] = 0;
            $newItem['member_price_list'] = $item->memberPrice;
            $newAttr[] = $newItem;
        }
        $this->attr = $newAttr;
    }

    /**
     * @return int
     * @throws \Exception
     * 获取商品库存
     */
    public function getGoodsNum()
    {
        $attr = $this->getAttr();
        $goodNumCount = 0;
        foreach ($attr as $item) {
            $goodNumCount += $item['stock'];
        }
        return $goodNumCount;
    }

    /**
     * @return string|null
     * @throws \Exception
     * 获取第一个规格的货号
     */
    public function getGoodsNo()
    {
        $attr = $this->getAttr();
        foreach ($attr as $index => $item) {
            return $item['no'];
        }
        return null;
    }

    /**
     * @return string|null
     * @throws \Exception
     * 获取第一个规格的重量
     */
    public function getGoodsWeight()
    {
        $attr = $this->getAttr();
        foreach ($attr as $index => $item) {
            return $item['weight'];
        }
        return null;
    }

    /**
     * @return mixed|string
     * @throws \Exception
     * 获取商品最低价
     */
    public function getPriceMin()
    {
        $attr = $this->getAttr();
        $price = $this->goods->price;
        foreach ($attr as $index => $item) {
            $price = min($price, $item['price']);
        }
        return price_format($price, 'float', 2);
    }

    /**
     * @return mixed|string
     * @throws \Exception
     * 获取商品最高价
     */
    public function getPriceMax()
    {
        $attr = $this->getAttr();
        $price = $this->goods->price;
        foreach ($attr as $index => $item) {
            $price = max($price, $item['price']);
        }
        return price_format($price, 'float', 2);
    }

    /**
     * @return array
     * 获取商品服务
     */
    public function getServices()
    {
        $services = [];
        if ($this->goods->is_default_services == 1) {
            $defaultService = GoodsServices::find()->where([
                'is_default' => 1,
                'is_delete' => 0,
                'mall_id' => $this->mall->id,
                'mch_id' => $this->mch_id
            ])->all();
        } else {
            $defaultService = $this->goods->services;
        }
        /* @var $defaultService GoodsServices[] */
        foreach ($defaultService as $item) {
            $services[] = $item->name;
        }

        return $services;
    }

    /**
     * @return array
     * 获取营销文字信息
     */
    public function getGoodsMarketing()
    {
        $options = CommonOption::getList(
            [
                Option::NAME_TERRITORIAL_LIMITATION,
                Option::NAME_OFFER_PRICE,
            ],
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            ['is_enable' => 0],
            $this->mch_id
        );

        $limit = '';
        $detail = $this->getAreaLimit();
        if ($detail && !empty($detail)) {
            $detail = implode('、', array_column($detail[0]['list'], 'name'));
            $limit = sprintf('仅限%s购买', $detail);
        }
        //起送
        $pickup = '';
        $offer = $options[Option::NAME_OFFER_PRICE];
        if ($offer{'is_enable'} == 1) {
            if (is_array($offer['detail'])) {
                foreach ($offer['detail'] as $i) {
                    $pickup .= sprintf('满%sFCFA起送', $i['total_price']);
                    $pickup .= '(';
                    $pickup .= implode('、', array_column($i['list'], 'name'));
                    $pickup .= ')，';
                }
            }
            $isTotalPrice = isset($offer['is_total_price']) ? $offer['is_total_price'] : 1;
            if ($isTotalPrice) {
                $pickup .= sprintf('满%sFCFA起送(其他省份)', $offer['total_price']);
            }
            $pickup = substr($pickup, -1) == ',' ? substr($pickup, 0, -1) : $pickup;
        }

        //包邮
        $shipping = '';
        if ($this->goods->pieces > 0) {
            $shipping .= sprintf('单品满%s件包邮，', $this->goods->pieces);
            if ($this->goods->pieces == 1) {
                $this->isExpress = true;
            }
        }
        if ($this->goods->forehead > 0) {
            $shipping .= sprintf('单品满FCFA%s包邮，', $this->goods->forehead);
            if ($this->goods->forehead < $this->getPriceMin()) {
                $this->isExpress = true;
            }
        }

        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        if (in_array('vip_card', $permission)) {
            try {
                $plugin = \Yii::$app->plugin->getPlugin('vip_card');
            } catch (\Exception $e) {
                $plugin = false;
            }
            if ($plugin) {
                $vipCardUser = VipCardUser::find()->where(['user_id' => \Yii::$app->user->id, 'is_delete' => 0])->one();
                if (!empty($vipCardUser) && $vipCardUser->image_is_free_delivery == 1 && isset($this->goods->mch_id) && $this->goods->mch_id == 0) {
                    $shipping = '超级会员卡用户，全部自营商品包邮';
                    $this->isExpress = true;
                }
            }
        }
        if (!$shipping) {
            $freeDelivery = FreeDeliveryRules::findAll([
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => $this->mch_id,
            ]);
            foreach ($freeDelivery as $i) {
                $shipping .= $i['price'] > 0 ? sprintf('满%sFCFA包邮', $i['price']) : '免运费';
                $shipping .= '(';
                $shipping .= implode('、', array_column(\yii\helpers\Json::decode($i['detail']), 'name'));
                $shipping .= ')，';
            }
        }
        $shipping = trim($shipping, '\，');

        return [
            'limit' => $limit,
            'pickup' => $pickup,
            'shipping' => $shipping
        ];
    }

    /*
     * 卡劵积分文字信息
     */
    public function getGoodsMarketingAward()
    {
        //卡劵列表
        $cards = $this->getCards();
        $cartTitle = implode('，', array_column($cards, 'name'));
        $card = [
            'title' => $cartTitle ? sprintf('购买即赠%s', $cartTitle) : '',
            'list' => $cards,
        ];
        if ($this->goods->give_integral > 0) {
            if ($this->goods->give_integral_type == 1) {
                $title = sprintf('购买可得%s积分', $this->goods->give_integral);
            } else {
                $price = max(array_column($this->goods->attr, 'price'));
                $title = sprintf('购买最高可得%s积分', bcmul($price, $this->goods->give_integral / 100, 0));
            }
        } else {
            $title = '';
        }
        return [
            'card' => $card,
            'integral' => ['title' => $title]
        ];
    }

    /**
     * @return array
     * 获取卡券列表
     */
    public function getCards()
    {
        $cards = [];
        $defaultCards = $this->goods->cards;
        /* @var $defaultCards GoodsCards[] */
        foreach ($defaultCards as $item) {
            $cards[] = [
                'card_id' => $item->id,
                'name' => $item->name,
                'pic_url' => $item->pic_url,
                'description' => $item->description
            ];
        }

        return $cards;
    }

    /**
     * @return array|null
     * 获取图片列表
     */
    public function getPicUrl()
    {
        return (array)\Yii::$app->serializer->decode($this->goods->goodsWarehouse->pic_url);
    }

    /**
     * @param array $params
     * @param array $ignore
     * @return array
     * @throws \Exception
     * 固定返回\app\models\Goods中的属性信息
     * 额外返回$params中包含的字段信息
     */
    public function getAll($params = [], $ignore = [])
    {
        $result = ArrayHelper::toArray($this->goods);
        $result['attr_groups'] = \Yii::$app->serializer->decode($this->goods->attr_groups);
        $goodsWarehouse = $this->goods->goodsWarehouse;
        $result = array_merge($result, [
            'name' => $goodsWarehouse->name,
            'original_price' => $goodsWarehouse->original_price,
            'cover_pic' => $goodsWarehouse->cover_pic,
            'unit' => $goodsWarehouse->unit,
            'detail' => $goodsWarehouse->detail,
            'video_url' => Video::getUrl($goodsWarehouse->video_url),
        ]);
        if (count($params) == 0) {
            $params = $this->getDefault();
        }
        // 商品分销价格关闭
        $mallSetting = $this->mall->getMallSetting(['is_share_price', 'is_common_user_member_price', 'is_member_user_member_price', 'is_sales', 'is_express']);
        if ($mallSetting['is_share_price'] == 0) {
            $this->setShare(false);
        }

        //图片替换
        try {
            $temp = [];
            $this->setAttr($this->goods->attr);
            foreach ($this->attr as $v) {
                foreach ($v['attr_list'] as $w) {
                    if (!isset($temp[$w['attr_id']])) {
                        $temp[$w['attr_id']] = $v['pic_url'];
                    }
                }
            }
            foreach ($result['attr_groups'] as $k => $v) {
                foreach ($v['attr_list'] as $l => $w) {
                    $result['attr_groups'][$k]['attr_list'][$l]['pic_url'] = $temp[$w['attr_id']] ?: "";
                }
            }
        } catch (\Exception $exception) {
        }

        if (!$this->isMember) {
            $levelShow = 0;
        } else {
            if ($this->goods->is_level) {
                if ($this->user && $this->user->identity->member_level > 0) {
                    $levelShow = 1;
                } else {
                    $levelShow = 2;
                }
            } else {
                $levelShow = 0;
            }

            // 会员价显示 开关，分普通用户开关，会员用户开关
            $isMemberPrice = 0;
            if ($this->user) {
                if ($this->user->identity->member_level) {
                    $isMemberPrice = $mallSetting['is_member_user_member_price'] ? 1 : 0;
                } else {
                    $isMemberPrice = $mallSetting['is_common_user_member_price'] ? 1 : 0;
                }
            } else {
                $isMemberPrice = $mallSetting['is_common_user_member_price'] ? 1 : 0;
            }
            if ($isMemberPrice == 0) {
                $levelShow = 0;
            }
        }
        $this->setMember($levelShow);
        $this->setSales($mallSetting['is_sales']);
        $result['level_show'] = $levelShow; // 会员价显示状态 0--不显示 1--显示用户会员价 2--显示下一等级会员价
        $result['is_sales'] = intval($mallSetting['is_sales']);

        foreach ($params as $item) {
            if (in_array($item, $ignore)) {
                continue;
            }
            $get = 'get' . hump($item);
            if (method_exists($this, $get)) {
                $result[$item] = $this->$get();
            }
        }
        $result['express'] = $mallSetting['is_express'] == 1 ? $this->getExpressPrice() : '';

        if ($levelShow != 0) {
            $result['attr'] = $this->getMemberPrice();
            $result['price_member_max'] = 0;
            $result['price_member_min'] = 0;
            foreach ($result['attr'] as &$item) {
                if ($this->user && $this->user->identity->member_level > 0) {
                    if (isset($item['member_price_' . $this->user->identity->member_level])) {
                        $memberPrice = $item['member_price_' . $this->user->identity->member_level];
                    } else {
                        $memberPrice = $item['price'];
                        $result['level_show'] = 0;
                    }
                    $item['price_member'] = $memberPrice;
                } else {
                    $memberPrice = $item['price_member'];
                }
                if ($result['price_member_max'] == 0) {
                    $result['price_member_max'] = $memberPrice;
                }
                if ($result['price_member_min'] == 0) {
                    $result['price_member_min'] = $memberPrice;
                }
                $result['price_member_max'] = floatval(max($result['price_member_max'], $memberPrice));
                $result['price_member_min'] = floatval(min($result['price_member_min'], $memberPrice));
            }
            unset($item);
        }

        return $result;
    }

    /**
     * @return array
     * 获取默认$params信息
     */
    private function getDefault()
    {
        return [
            'attr', 'goods_num', 'goods_no', 'goods_weight', 'attr_group', 'option', 'services',
            'cards', 'price_min', 'price_max', 'pic_url', 'share', 'sales', 'favorite', 'goods_marketing',
            'goods_marketing_award', 'vip_card_appoint'
        ];
    }

    /**
     * @return array
     * @throws \Exception
     * 获取各个会员等级的会员价
     */
    public function getMemberPrice()
    {
        if (!$this->isMember) {
            return $this->attr;
        }
        /* @var MallMembers[] $members */
        $members = CommonMallMember::getAllMember();
        /* @var GoodsAttr[] $goodsAttr */
        $attr = $this->getAttr();
        foreach ($attr as &$item) {
            $first = true;
            $item['price_member'] = $item['price'];
            foreach ($members as $member) {
                if ($this->goods->is_level_alone == 1) {
                    $isDiscount = true;
                    /* @var GoodsMemberPrice[] $memberPriceList */
                    $memberPriceList = $item['member_price_list'];
                    foreach ($memberPriceList as $value) {
                        if ($value->level == $member->level) {
                            $isDiscount = false;
                            $item['member_price_' . $member->level] = $value->price;
                            break;
                        }
                    }
                    if ($isDiscount) {
                        $item['member_price_' . $member->level] = round($item['price'] * $member->discount / 10, 2);
                    }
                } else {
                    $item['member_price_' . $member->level] = round($item['price'] * $member->discount / 10, 2);
                }
                if ($first) {
                    $first = false;
                    $item['price_member'] = $item['member_price_' . $member->level];
                }
            }
        }
        unset($value);
        return $attr;
    }

    /**
     * @return float|int|string
     * @throws \Exception
     */
    public function getShare()
    {
        $isSharePrice = \Yii::$app->mall->getMallSetting(['is_share_price']);
        if ($isSharePrice['is_share_price'] != 1) {
            return 0;
        }
        // 用户是不是分销商
        if (!$this->user) {
            return 0;
        } else {
            if ($this->user->identity->is_distributor == 0) {
                return 0;
            }
        }
        // 商城是否开启分销
        $shareSetting = ShareSetting::getList($this->goods->mall_id);
        if (!$shareSetting) {
            return 0;
        }
        if ($shareSetting[ShareSetting::LEVEL] == 0) {
            return 0;
        }
        if (!$this->isShare) {
            return 0;
        }
        if ($this->user->share->level > 0 && $this->mch_id == 0) {
            $res = $this->shareLevel($shareSetting);
        } else {
            $res = $this->share($shareSetting);
        }
        $shareType = $res['shareType'];
        $first = $res['first'];

        // 分销佣金是百分比还是固定金额
        if ($shareType == 0) {
            $share = price_format($first, 'float', 2);
        } else {
            $priceMax = $this->getPriceMax();
            $share = price_format($priceMax * $first / 100, 'float', 2);
        }
        return $share;
    }

    // 分销层级佣金计算
    private function share($shareSetting)
    {
        // 是否单独设置分销
        if ($this->goods->individual_share == 1) {
            $first = 0;
            // 是否详细设置分销
            if ($this->goods->attr_setting_type == 1) {
                foreach ($this->goods->attr as $item) {
                    $first = max($first, $item->share->share_commission_first);
                }
            } else {
                foreach ($this->goods->share as $item) {
                    if ($item->goods_attr_id == 0) {
                        $first = $item->share_commission_first;
                        break;
                    }
                }
            }
            $shareType = $this->goods->share_type;
        } else {
            // 多商户没有全局分销价
            if ($this->mch_id > 0) {
                return [
                    'first' => 0,
                    'shareType' => 0
                ];
            }
            $shareType = $shareSetting[ShareSetting::PRICE_TYPE] == 2 ? 0 : 1;
            $first = $shareSetting[ShareSetting::FIRST];
        }
        return [
            'first' => $first,
            'shareType' => $shareType,
        ];
    }

    // 分销等级佣金计算（若分销等级佣金没有设置，则按照分销层级佣金进行计算）
    private function shareLevel($shareSetting)
    {
        if ($this->goods->individual_share == 1) {
            $first = 0;
            if ($this->goods->attr_setting_type == 1) {
                /* @var GoodsShare[] $goodsShareLevel */
                $goodsShareLevel = GoodsShare::find()
                    ->where(['goods_id' => $this->goods->id, 'level' => $this->user->share->level, 'is_delete' => 0])
                    ->andWhere(['>', 'goods_attr_id', 0])
                    ->all();
                if (empty($goodsShareLevel)) {
                    return $this->share($shareSetting);
                }
                foreach ($goodsShareLevel as $item) {
                    $first = max($first, $item->share_commission_first);
                }
            } else {
                /* @var GoodsShare $goodsShareLevel */
                $goodsShareLevel = GoodsShare::find()
                    ->where([
                        'goods_id' => $this->goods->id, 'level' => $this->user->share->level, 'is_delete' => 0,
                        'goods_attr_id' => 0
                    ])
                    ->one();
                if (!$goodsShareLevel) {
                    return $this->share($shareSetting);
                }
                $first = $goodsShareLevel->share_commission_first;
            }
            $shareType = $this->goods->share_type;
        } else {
            $shareLevel = CommonShareLevel::getInstance()->getShareLevelByLevel($this->user->share->level);
            if (!$shareLevel) {
                return $this->share($shareSetting);
            }
            $shareType = $shareLevel->price_type == 2 ? 0 : 1;
            $first = $shareLevel->first;
        }
        return [
            'first' => $first,
            'shareType' => $shareType,
        ];
    }

    /**
     * @return int
     * 获取商品销量（包括虚拟销量）
     */
    public function getSales()
    {
        if (!$this->isSales) {
            return 0;
        }
        return $this->goods->sales + $this->goods->virtual_sales;
    }

    /**
     * @param $val
     * 是否显示分销
     * 系统常规设置的优先级最高
     */
    public function setShare($val)
    {
        $this->isShare = $val;
    }

    /**
     * @param $val
     * 是否显示会员价
     * 系统常规设置的优先级最高
     */
    public function setMember($val)
    {
        $this->isMember = $val;
    }

    /**
     * @param $val
     * 是否显示销量
     * 系统常规设置的优先级最高
     */
    public function setSales($val)
    {
        $this->isSales = $val;
    }

    public function getFavorite()
    {
        if (!$this->user) {
            return false;
        }
        $model = Favorite::findOne([
            'user_id' => $this->user->id, 'mall_id' => $this->mall->id,
            'goods_id' => $this->goods->id, 'is_delete' => 0
        ]);
        return $model ? true : false;
    }

    public function getVipCardAppoint()
    {
        $my = 0;
        $discount = null;
        $isVipCardUser = 0;
        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        try {
            $plugin = \Yii::$app->plugin->getPlugin('vip_card');
        } catch (\Exception $e) {
            $plugin = false;
        }
        if ($plugin && in_array('vip_card', $permission)) {
            return $plugin->getAppoint($this->goods);
        }

        return [
            'discount' => $discount,
            'is_my_vip_card_goods' => $my,
            'is_vip_card_user' => $isVipCardUser
        ];
    }

    /**
     * @param $val
     * 是否单件商品包邮
     */
    public function setExpress($val)
    {
        $this->isExpress = $val;
    }

    public function getExpressPrice()
    {
        if ($this->isExpress == 1) {
            return '免运费';
        }
        if (!$this->user) {
            return '';
        }
        $address = Address::findOne(['is_default' => 1, 'user_id' => $this->user->id, 'is_delete' => 0]);
        if (!$address) {
            return '';
        }

        // 计算允许购买区域
        $district = [$address->province_id, $address->city_id, $address->district_id];
        $areaList = $this->getAreaLimit();
        if ($areaList && !empty($areaList)) {
            foreach ($areaList as $item) {
                $arr = array_column($item['list'], 'id');
                if (empty($arr) || empty(array_intersect($district, $arr))) {
                    return '';
                }
            }
        }

        // 计算运费
        $postageRule = null;
        if ($this->goods->freight_id && $this->goods->freight_id != -1) {
            $postageRule = PostageRules::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->goods->freight_id,
                'is_delete' => 0,
                'mch_id' => $this->goods->mch_id,
            ]);
        }
        if (!$postageRule) {
            $postageRule = PostageRules::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'status' => 1,
                'is_delete' => 0,
                'mch_id' => $this->goods->mch_id,
            ]);
        }
        if (!$postageRule) {
            return '';
        }

        $rule = null; // 用户的收货地址是否在规则中
        $ruleDetails = $postageRule->decodeDetail();
        foreach ($ruleDetails as $ruleDetail) {
            foreach ($ruleDetail['list'] as $district) {
                if ($district['id'] == $address->province_id) {
                    $rule = $ruleDetail;
                    break;
                } elseif ($district['id'] == $address->city_id) {
                    $rule = $ruleDetail;
                    break;
                } elseif ($district['id'] == $address->district_id) {
                    $rule = $ruleDetail;
                    break;
                }
            }
            if ($rule) {
                break;
            }
        }
        if (!$rule) {
            return '';
        }
        return 'FCFA' . price_format($rule['firstPrice']);
    }

    /**
     * @return array|mixed
     * 计算允许购买的区域
     */
    public function getAreaLimit()
    {
        $list = [];
        if ($this->goods->is_area_limit == 1) {
            $list = json_decode($this->goods->area_limit, true);
        } else {
            $territorial = CommonOption::get(
                Option::NAME_TERRITORIAL_LIMITATION,
                \Yii::$app->mall->id,
                Option::GROUP_APP,
                ['is_enable' => 0],
                $this->goods->mch_id
            );
            if ($territorial && isset($territorial['is_enable']) && $territorial['is_enable'] == 1
                && isset($territorial['detail']) && is_array($territorial['detail'])) {
                $list = $territorial['detail'];
            }
        }
        return $list;
    }
}
