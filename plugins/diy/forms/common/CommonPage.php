<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/24
 * Time: 9:16
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\plugins\diy\forms\common;


use app\forms\api\app_platform\Transform;
use app\forms\common\order\CommonOrder;
use app\forms\common\video\Video;
use app\models\Mall;
use app\models\Model;
use app\plugins\diy\models\DiyPage;

/**
 * @property Mall $mall
 */
class CommonPage extends Model
{
    public $mall;
    public $longitude;
    public $latitude;

    public static function getCommon($mall, $longitude = null, $latitude = null)
    {
        $common = new self();
        $common->mall = $mall;
        $common->longitude = $longitude;
        $common->latitude = $latitude;
        return $common;
    }

    /**
     * @param null $pageId 自定义页面ID
     * @param bool $isIndex 是否查找首页
     * @return array
     * @throws \Exception
     * 获取自定义页面
     */
    public function getPage($pageId = null, $isIndex = false)
    {
        if (!$pageId) {
            if (!$isIndex) {
                throw new \Exception('页面不存在');
            }
            $exists = DiyPage::find()->where([
                'is_home_page' => 1,
                'mall_id' => $this->mall->id,
                'is_disable' => 0,
                'is_delete' => 0
            ])->exists();
            if (!$exists) {
                throw new \Exception('页面不存在');
            }
        }
        $query = DiyPage::find()->select('id,title,show_navs,is_home_page')
            ->where([
                'mall_id' => $this->mall->id,
                'is_disable' => 0,
                'is_delete' => 0,
            ])->with(['navs' => function ($query) {
                $query->select('id,name,page_id,template_id')->with(['template' => function ($query) {
                    $query->select('id,name,data')->where(['is_delete' => 0]);
                }]);
            }]);
        if ($pageId) {
            $query->andWhere(['id' => $pageId]);
        } else {
            if ($isIndex) {
                $query->andWhere(['is_home_page' => 1]);
            } else {
                throw new \Exception('页面不存在');
            }
        }
        $page = $query->asArray()->one();
        if (!$page) {
            throw new \Exception('页面不存在');
        }

        if (!empty($page['navs'])) {
            try {
                // 商品组件数据
                $goodsIds = [];
                $goodsCats = [];
                // 优惠券数据
                $coupons = [];
                // 多商户数据
                $mchIds = [];
                $mchGoodsIds = [];
                // 门店数据
                $storeIds = [];
                $pintuanGoodsIds = [];
                $bookingGoodsIds = [];
                $miaoshaGoodsIds = [];
                $bargainGoodsIds = [];
                $integralMallGoodsIds = [];
                $lotteryGoodsIds = [];

                //预售
                $advanceGoodsIds = [];

                //小程序管理入口权限
                $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
                $app_admin = true;
                if (empty(\Yii::$app->plugin->getInstalledPlugin('app_admin')) || !in_array('app_admin', $permission) || empty(\Yii::$app->user->identity->identity->is_admin) || \Yii::$app->user->identity->identity->is_admin != 1) {
                    $app_admin = false;
                }
                // 统一数据查询
                foreach ($page['navs'] as &$nav) {
                    if (!empty($nav['template']['data'])) {
                        $nav['template']['data'] = json_decode($nav['template']['data'], true);
                        foreach ($nav['template']['data'] as &$item) {
                            //小程序入口权限
                            if ($item['id'] == 'nav') {
                                foreach ($item['data']['navs'] as $i => $v) {
                                    if ($v['openType'] == 'app_admin' && !$app_admin) {
                                        array_splice($item['data']['navs'], $i, 1);
                                    }
                                    if ($v['openType'] == 'contact' && \Yii::$app->appPlatform === APP_PLATFORM_TTAPP) {
                                        array_splice($item['data']['navs'], $i, 1);
                                    }
                                }
                            }
                            // 优惠券
                            if ($item['id'] == 'coupon') {
                                // 防止重复查询
                                if (!$coupons) {
                                    $diyCouponForm = new DiyCouponForm();
                                    $coupons = $diyCouponForm->getCoupons();
                                }
                            }
                            // 好店推荐
                            if ($item['id'] == 'mch') {
                                $diyMchForm = new DiyMchForm();
                                $res = $diyMchForm->getMchData($item['data']);
                                $mchIds = array_merge($res['mchIds'], $mchIds);
                                $mchGoodsIds = array_merge($res['mchGoodsIds'], $mchGoodsIds);
                            }
                            // 专题
                            if ($item['id'] == 'topic') {
                                $diyTopicsForm = DiyTopicsForm::getInstance();
                                if (isset($item['data']['style']) && $item['data']['style'] == 'list') {
                                    if (isset($item['data']['cat_show']) && !$item['data']['cat_show']) {
                                        foreach ($item['data']['topic_list'] as $topic) {
                                            $diyTopicsForm->setIdList($topic['id']);
                                        }
                                    } else {
                                        foreach ($item['data']['list'] as $type) {
                                            if ($type['custom']) {
                                                foreach ($type['children'] as $topic) {
                                                    $diyTopicsForm->setIdList($topic['id']);
                                                }
                                            } else {
                                                $diyTopicsForm->setTypeList($type['cat_id']);
                                            }
                                        }
                                    }
                                }
                            }
                            // 商品
                            if ($item['id'] == 'goods') {
                                $diyGoodsForm = new DiyGoodsForm();
                                $goodsIds = array_merge($diyGoodsForm->getGoodsIds($item['data']), $goodsIds);
                                $goodsCats = $diyGoodsForm->getCats($item['data'], $goodsCats);
                            }
                            // 门店
                            if ($item['id'] == 'store') {
                                $diyStoreForm = new DiyStoreForm();
                                $storeIds = array_merge($diyStoreForm->getStoreIds($item['data']), $storeIds);
                            }

                            // 拼团
                            if ($item['id'] == 'pintuan') {
                                $diyPintuanForm = new DiyPintuanForm();
                                $pintuanGoodsIds = array_merge(
                                    $diyPintuanForm->getGoodsIds($item['data']),
                                    $pintuanGoodsIds
                                );
                            }
                            // 预约
                            if ($item['id'] == 'booking') {
                                $diyBookingForm = new DiyBookingForm();
                                $bookingGoodsIds = array_merge(
                                    $diyBookingForm->getGoodsIds($item['data']),
                                    $bookingGoodsIds
                                );
                            }
                            // 秒杀
                            if ($item['id'] == 'miaosha') {
                                $diyMiaoshaForm = new DiyMiaoshaForm();
                                $miaoshaGoodsIds = array_merge(
                                    $diyMiaoshaForm->getGoodsIds($item['data']),
                                    $miaoshaGoodsIds
                                );
                            }
                            // 砍价
                            if ($item['id'] == 'bargain') {
                                $diyBargainForm = new DiyBargainForm();
                                $bargainGoodsIds = array_merge(
                                    $diyBargainForm->getGoodsIds($item['data']),
                                    $bargainGoodsIds
                                );
                            }
                            // 积分商城
                            if ($item['id'] == 'integral-mall') {
                                $diyIntegralMallForm = new DiyIntegralMallForm();
                                $integralMallGoodsIds = array_merge(
                                    $diyIntegralMallForm->getGoodsIds($item['data']),
                                    $integralMallGoodsIds
                                );
                            }
                            // 幸运抽奖
                            if ($item['id'] == 'lottery') {
                                $diyLotteryForm = new DiyLotteryForm();
                                $lotteryGoodsIds = array_merge(
                                    $diyLotteryForm->getGoodsIds($item['data']),
                                    $lotteryGoodsIds
                                );
                            }
                            // 快捷导航
                            if ($item['id'] == 'quick-nav') {
                                if (isset($item['data']['navSwitch']) && $item['data']['navSwitch'] && $item['data']['useMallConfig']) {
                                    $diyQuickNavForm = new DiyQuickNavForm();
                                } else {
                                    if (isset($item['data']['web']['url'])) {
                                        $item['data']['web']['url'] = urlencode($item['data']['web']['url']);
                                    }
                                }
                            }
                            // 签到
                            if ($item['id'] == 'check-in') {
                                $diyCheckInForm = new DiyCheckInForm();
                            }

                            // 预售
                            if ($item['id'] == 'advance') {
                                $diyAdvanceForm = new DiyAdvanceForm();
                                $advanceGoodsIds = array_merge(
                                    $diyAdvanceForm->getGoodsIds($item['data']),
                                    $advanceGoodsIds
                                );
                            }

                            //超级会员卡
                            if ($item['id'] == 'vip_card') {
                                $diyVipCardForm = new DiyVipCardForm();
                            }
                        }
                        unset($item);
                    }
                }

                // 商品组件数据
                if (isset($diyGoodsForm)) {
                    $diyGoods = $diyGoodsForm->getGoodsById($goodsIds);
                    $diyCatsGoods = $diyGoodsForm->getGoodsByCat($goodsCats);
                }
                // 好店推荐组件数据
                if (isset($diyMchForm)) {
                    $diyMchGoods = $diyMchForm->getMchGoodsById($mchGoodsIds);
                    $diyMch = $diyMchForm->getMch($mchIds);
                }
                // 门店组件数据
                if (isset($diyStoreForm)) {
                    $diyStores = $diyStoreForm->getStoreById($storeIds);
                }
                // 拼团组件数据
                if (isset($diyPintuanForm)) {
                    $diyPintuanGoods = $diyPintuanForm->getGoodsById($pintuanGoodsIds);
                }
                // 预约组件数据
                if (isset($diyBookingForm)) {
                    $diyBookingGoods = $diyBookingForm->getGoodsById($bookingGoodsIds);
                }
                // 秒杀组件数据
                if (isset($diyMiaoshaForm)) {
                    $diyMiaoshaGoods = $diyMiaoshaForm->getGoodsById($miaoshaGoodsIds);
                }
                // 砍价组件数据
                if (isset($diyBargainForm)) {
                    $diyBargainGoods = $diyBargainForm->getGoodsById($bargainGoodsIds);
                }
                // 积分商城组件数据
                if (isset($diyIntegralMallForm)) {
                    $diyIntegralMallGoods = $diyIntegralMallForm->getGoodsById($integralMallGoodsIds);
                }
                // 积分商城组件数据
                if (isset($diyLotteryForm)) {
                    $diyLotteryGoods = $diyLotteryForm->getGoodsById($lotteryGoodsIds);
                }
                // 快捷导航
                if (isset($diyQuickNavForm)) {
                    $quickNav = array_merge($diyQuickNavForm->getQuickNav());
                }
                // 签到
                if (isset($diyCheckInForm)) {
                    $checkIn = $diyCheckInForm->getCheckIn();
                }
                // 预售
                if (isset($diyAdvanceForm)) {
                    $advance = $diyAdvanceForm->getGoodsById($advanceGoodsIds);
                }
                // 超级会员卡
                if (isset($diyVipCardForm)) {
                    $vipCard = $diyVipCardForm->getVipCard();
                }
                // 统一数据处理
                foreach ($page['navs'] as $index => &$nav) {
                    if (!empty($nav['template']['data'])) {
                        foreach ($nav['template']['data'] as &$item) {
                            if ($item['id'] == 'nav') {
                                foreach ($item['data']['navs'] as $key => &$value) {
                                    $value['icon_url'] = $value['icon'];
                                    $value['link_url'] = $value['url'];
                                    $value['open_type'] = $value['openType'];
                                }
                                unset($value);
                                $item['data']['navs'] = Transform::getInstance()->transformHomeNav($item['data']['navs']);
                            }
                            // 优惠券
                            if ($item['id'] == 'coupon') {
                                $item['data']['coupon_list'] = $coupons;
                            }
                            // 商品
                            if ($item['id'] == 'goods') {
                                $item['data'] = $diyGoodsForm->getNewGoods($item['data'], $diyGoods, $diyCatsGoods);
                            }
                            // 专题
                            if ($item['id'] == 'topic') {
                                $diyTopicsForm = DiyTopicsForm::getInstance();
                                $item['data'] = $diyTopicsForm->getNewTopics($item['data']);
                            }
                            // 好店推荐
                            if ($item['id'] == 'mch') {
                                $item['data'] = $diyMchForm->getNewMch($item['data'], $diyMchGoods, $diyMch);
                            }
                            // 门店
                            if ($item['id'] == 'store') {
                                $item['data'] = $diyStoreForm->getNewStore(
                                    $item['data'],
                                    $diyStores,
                                    $this->longitude,
                                    $this->latitude
                                );
                            }
                            // 拼团
                            if ($item['id'] == 'pintuan') {
                                $item['data'] = $diyPintuanForm->getNewGoods($item['data'], $diyPintuanGoods);
                            }
                            // 预约
                            if ($item['id'] == 'booking') {
                                $item['data'] = $diyBookingForm->getNewGoods($item['data'], $diyBookingGoods);
                            }
                            // 秒杀
                            if ($item['id'] == 'miaosha') {
                                $item['data'] = $diyMiaoshaForm->getNewGoods($item['data'], $diyMiaoshaGoods);
                            }
                            // 砍价
                            if ($item['id'] == 'bargain') {
                                $item['data'] = $diyBargainForm->getNewGoods($item['data'], $diyBargainGoods);
                            }
                            // 积分商城
                            if ($item['id'] == 'integral-mall') {
                                $item['data'] = $diyIntegralMallForm->getNewGoods($item['data'], $diyIntegralMallGoods);
                            }
                            // 抽奖
                            if ($item['id'] == 'lottery') {
                                $item['data'] = $diyLotteryForm->getNewGoods($item['data'], $diyLotteryGoods);
                            }
                            // 轮播图
                            if ($item['id'] == 'banner') {
                                $bannerForm = new DiyBannerForm();
                                $item['data'] = $bannerForm->getNewBanner($item['data']);
                            }
                            // 快捷导航
                            if ($item['id'] == 'quick-nav') {
                                if (isset($quickNav)) {
                                    $item['data'] = $quickNav;
                                } else {
                                    $arr = explode(',', $item['data']['mapNav']['location']);
                                    $item['data']['mapNav']['latitude'] = isset($arr[0]) ? $arr[0] : 0;
                                    $item['data']['mapNav']['longitude'] = isset($arr[1]) ? $arr[1] : 0;
                                }
                            }
                            // 弹窗广告
                            if ($item['id'] == 'modal') {
                                $newList = [];
                                foreach ($item['data']['list'] as $dItem) {
                                    $newList[] = $dItem;
                                }
                                $item['data']['list'] = [];
                                if (count($newList) > 0) {
                                    $item['data']['list'][] = $newList;
                                }
                            }
                            // 签到
                            if ($item['id'] == 'check-in') {
                                if (isset($checkIn)) {
                                    $item['data']['award'] = $checkIn;
                                }
                            }
                            // 预售
                            if ($item['id'] == 'advance') {
                                $item['data'] = $diyAdvanceForm->getNewGoods($item['data'], $advance);
                            }
                            // 超级会员卡
                            if ($item['id'] == 'vip_card') {
                                if (isset($vipCard)) {
                                    $item['data']['vip_card'] = $vipCard;
                                }
                            }

                            if ($item['id'] == 'map') {
                                $arr = explode(',', $item['data']['location']);
                                $item['data']['latitude'] = isset($arr[0]) ? $arr[0] : 0;
                                $item['data']['longitude'] = isset($arr[1]) ? $arr[1] : 0;
                            }

                            if ($item['id'] == 'user-order') {
                                $res = (new CommonOrder())->getOrderInfoCount();
                                foreach ($item['data']['navs'] as $key => &$value) {
                                    $value['url'] = '/pages/order/index/index?status=' . ($key + 1);
                                    $value['num'] = $res[$key];
                                    $value['open_type'] = $value['openType'];
                                    $value['link_url'] = $value['url'];
                                    $value['name'] = $value['text'];
                                    $value['icon_url'] = $value['picUrl'];
                                }
                                unset($value);
                            }
                            if ($item['id'] == 'video') {
                                $item['data']['url'] = Video::getUrl($item['data']['url']);
                            }
                            if ($item['id'] == 'copyright') {
                                if (isset($item['data']['link']['data']['params'])) {
                                    $newParams = [];
                                    foreach ($item['data']['link']['data']['params'] as $param) {
                                        $newParams[] = [
                                            'key' => $param['key'],
                                            'value' => $param['value']
                                        ];
                                    }
                                    $item['data']['params'] = $newParams;
                                }
                            }
                        }
                        unset($item);
                    } else {
                        $nav['template']['data'] = [];
                    }

                    // 是设置首页的diy页面才添加裂变红包广告
                    if ($page['is_home_page'] == 1 && $index == 0) {
                        $modal = (new DiyModalForm())->getModal();
                        if (count($modal) > 0) {
                            $nav['template']['data'][] = [
                                'id' => 'modal',
                                'data' => [
                                    'opened' => true,
                                    'times' => 1,
                                    'list' => [$modal]
                                ]
                            ];
                        }
                    }
                }
                unset($nav);
                // 是设置首页的diy页面才添加裂变红包广告
//                if ($page['is_home_page'] == 1) {
//                    $modal = (new DiyModalForm())->getModal();
//                    $page['navs'][0]['template']['data'][] = [
//                        'id' => 'modal',
//                        'data' => [
//                            'opened' => true,
//                            'times' => 1,
//                            'list' => [$modal]
//                        ]
//                    ];
//                }
            } catch (\Exception $e) {
                throw $e;
            }
        }

        return $page;
    }

    /**
     * @return array|\yii\db\ActiveRecord[]|DiyPage[]
     * 获取所有页面
     */
    public function getPageList()
    {
        $pageList = DiyPage::find()
            ->where(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'is_disable' => 0])
            ->all();
        return $pageList;
    }
}
