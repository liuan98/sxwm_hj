<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/24
 * Time: 15:44
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\plugins\diy\forms\common;


use app\models\Mall;
use app\models\Model;
use app\plugins\diy\models\DiyTemplate;

/**
 * Class CommonTemplate
 * @package app\plugins\diy\forms\common
 * @property Mall $mall
 */
class CommonTemplate extends Model
{
    public $mall;
    public static function getCommon($mall = null)
    {
        $instance = new self();
        if (!$mall) {
            $mall = \Yii::$app->mall;
        }
        $instance->mall = $mall;
        return $instance;
    }

    /**
     * @param $pagination
     * @param $page
     * @param int $limit
     * @return array|\yii\db\ActiveRecord[]
     * 获取模板列表
     */
    public function getList(&$pagination, $page, $limit = 20)
    {
        $list = DiyTemplate::find()->where([
            'mall_id' => $this->mall->id,
            'is_delete' => 0
        ])->page($pagination, $limit, $page)
            ->orderBy(['created_at' => SORT_DESC])->all();
        return $list;
    }

    /**
     * @param $id
     * @return DiyTemplate|null
     */
    public function getTemplate($id)
    {
        $template = DiyTemplate::findOne([
            'mall_id' => $this->mall->id,
            'id' => $id
        ]);

        return $template;
    }

    /**
     * @param $id
     * @return DiyTemplate|null
     * @throws \Exception
     */
    public function destroy($id)
    {
        $template = $this->getTemplate($id);
        if (!$template) {
            throw new \Exception('模板不存在');
        }
        if ($template->is_delete == 1) {
            throw new \Exception('模板已删除');
        }
        $template->is_delete = 1;
        if (!$template->save()) {
            throw new \Exception($this->getErrorMsg($template));
        }
        return $template;
    }

    public function allComponents()
    {
        $pluginUrl = \app\helpers\PluginHelper::getPluginBaseAssetsUrl();
        $result = [
            [
                'groupName' => '基础组件',
                'list' => [
                    [
                        'id' => 'search',
                        'name' => '搜索',
                        'icon' => $pluginUrl . '/images/search.png',
                    ],
                    [
                        'id' => 'nav',
                        'name' => '导航图标',
                        'icon' => $pluginUrl . '/images/nav.png',
                    ],
                    [
                        'id' => 'banner',
                        'name' => '轮播广告',
                        'icon' => $pluginUrl . '/images/banner.png',
                    ],
                    [
                        'id' => 'notice',
                        'name' => '公告',
                        'icon' => $pluginUrl . '/images/notice.png'
                    ],
                    [
                        'id' => 'topic',
                        'name' => '专题',
                        'icon' => $pluginUrl . '/images/topic.png',
                        'key' => 'topic'
                    ],
                    [
                        'id' => 'link',
                        'name' => '关联链接',
                        'icon' => $pluginUrl . '/images/link.png',
                    ],
                    [
                        'id' => 'rubik',
                        'name' => '图片广告',
                        'icon' => $pluginUrl . '/images/rubik.png',
                    ],
                    [
                        'id' => 'video',
                        'name' => '视频',
                        'icon' => $pluginUrl . '/images/video.png',
                        'key' => 'video'
                    ],
                    [
                        'id' => 'goods',
                        'name' => '商品',
                        'icon' => $pluginUrl . '/images/goods.png',
                    ],
                    [
                        'id' => 'store',
                        'name' => '门店',
                        'icon' => $pluginUrl . '/images/mch.png',
                    ],
                    [
                        'id' => 'copyright',
                        'name' => '版权',
                        'icon' => $pluginUrl . '/images/copyright.png',
                        'key' => 'copyright'
                    ],
                    [
                        'id' => 'check-in',
                        'name' => '签到',
                        'icon' => $pluginUrl . '/images/check-in.png',
                        'key' => 'check_in'
                    ],
                    [
                        'id' => 'user-info',
                        'name' => '用户信息',
                        'icon' => $pluginUrl . '/images/user-info.png',
                    ],
                    [
                        'id' => 'user-order',
                        'name' => '订单入口',
                        'icon' => $pluginUrl . '/images/user-order.png',
                    ],
                    [
                        'id' => 'map',
                        'name' => '地图',
                        'icon' => $pluginUrl . '/images/map.png',
                    ],
                    [
                        'id' => 'mp-link',
                        'name' => '微信公众号',
                        'icon' => $pluginUrl . '/images/mp-link.png',
                    ],
                    [
                        'id' => 'form',
                        'name' => '自定义表单',
                        'icon' => $pluginUrl . '/images/form.png',
                    ],
                    [
                        'id' => 'image-text',
                        'name' => '图文详情',
                        'icon' => $pluginUrl . '/images/image-text.png',
                    ],
                ]
            ],
            [
                'groupName' => '营销组件',
                'list' => [
                    [
                        'id' => 'coupon',
                        'name' => '优惠券',
                        'icon' => $pluginUrl . '/images/coupon.png',
                        'key' => 'coupon'
                    ],
                    [
                        'id' => 'timer',
                        'name' => '倒计时',
                        'icon' => $pluginUrl . '/images/time.png'
                    ],
                    [
                        'id' => 'mch',
                        'name' => '好店推荐',
                        'icon' => $pluginUrl . '/images/mch.png',
                        'key' => 'mch'
                    ],
                    [
                        'id' => 'pintuan',
                        'name' => '拼团',
                        'icon' => $pluginUrl . '/images/pintuan.png',
                        'key' => 'pintuan'
                    ],
                    [
                        'id' => 'booking',
                        'name' => '预约',
                        'icon' => $pluginUrl . '/images/book.png',
                        'key' => 'booking'
                    ],
                    [
                        'id' => 'miaosha',
                        'name' => '秒杀',
                        'icon' => $pluginUrl . '/images/miaosha.png',
                        'key' => 'miaosha'
                    ],
                    [
                        'id' => 'bargain',
                        'name' => '砍价',
                        'icon' => $pluginUrl . '/images/bargain.png',
                        'key' => 'bargain'
                    ],
                    [
                        'id' => 'integral-mall',
                        'name' => '积分商城',
                        'icon' => $pluginUrl . '/images/integral.png',
                        'key' => 'integral_mall'
                    ],
                    [
                        'id' => 'lottery',
                        'name' => '抽奖',
                        'icon' => $pluginUrl . '/images/lottery.png',
                        'key' => 'lottery'
                    ],
                    [
                        'id' => 'advance',
                        'name' => '预售',
                        'icon' => $pluginUrl . '/images/advance.png',
                        'key' => 'advance'
                    ],
                    [
                        'id' => 'vip-card',
                        'name' => '超级会员卡',
                        'icon' => $pluginUrl . '/images/svip.png',
                        'key' => 'vip_card'
                    ],
                ]
            ],
            [
                'groupName' => '其他组件',
                'list' => [
                    [
                        'id' => 'empty',
                        'name' => '空白块',
                        'icon' => $pluginUrl . '/images/empty.png'
                    ],
                    [
                        'id' => 'ad',
                        'name' => '流量主广告',
                        'icon' => $pluginUrl . '/images/ad.png',
                        'single' => true,
                    ],
                    [
                        'id' => 'modal',
                        'name' => '弹窗广告',
                        'icon' => $pluginUrl . '/images/modal.png',
                        'single' => true,
                    ],
                    [
                        'id' => 'quick-nav',
                        'name' => '快捷导航',
                        'icon' => $pluginUrl . '/images/float.png',
                        'single' => true,
                    ],
                ]
            ]
        ];
        $permission = \Yii::$app->role->getAccountPermission();
        if ($permission !== true) {
            $newList = [];
            foreach ($result as $item) {
                if (isset($item['list'])) {
                    $list = [];
                    foreach ($item['list'] as $value) {
                        if (!$permission || (isset($value['key']) && !in_array($value['key'], $permission))) {
                            continue;
                        }
                        $list[] = $value;
                    }
                    if (count($list) > 0) {
                        $newItem = $item;
                        $newItem['list'] = $list;
                        $newList[] = $newItem;
                    }
                } else {
                    $newItem = $item;
                    $newList[] = $newItem;
                }
            }
        } else {
            $newList = $result;
        }
        return $newList;
    }
}
