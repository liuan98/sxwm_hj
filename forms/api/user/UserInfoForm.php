<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/1/29
 * Time: 11:14
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\forms\api\user;


use app\forms\api\app_platform\Transform;
use app\forms\common\CommonAppConfig;
use app\forms\common\CommonUser;
use app\forms\common\order\CommonOrder;
use app\forms\common\template\TemplateList;
use app\models\Favorite;
use app\models\FootprintGoodsLog;
use app\models\Goods;
use app\models\MallMembers;
use app\models\Model;
use app\models\User;
use app\models\UserCard;
use app\models\UserCoupon;
use app\models\UserInfo;

class UserInfoForm extends Model
{
    private function userInfo()
    {
        if (\Yii::$app->user->isGuest) {
            return null;
        }
        /** @var User $user */
        $user = \Yii::$app->user->identity;
        /* @var UserInfo $userInfo */
        $userInfo = CommonUser::getUserInfo();
        unset($userInfo->platform_user_id);

        $parentName = '总店';
        if ($userInfo->parent_id != 0) {
            $parent = User::findOne([
                'id' => $userInfo->parent_id,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0
            ]);
            if ($parent) {
                $parentName = $parent->nickname;
            }
        }

        $levelName = '普通用户';
        $memberPicUrl = '';
        if ($user->identity->member_level != 0) {
            $level = MallMembers::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'level' => $user->identity->member_level,
                'status' => 1, 'is_delete' => 0
            ]);
            if ($level) {
                $levelName = $level->name;
                $memberPicUrl = $level->pic_url;
            }
        }

        $couponCount = UserCoupon::find()->andWhere(['user_id' => $user->id, 'is_delete' => 0, 'is_use' => 0])
            ->andWhere(['>', 'end_time', mysql_timestamp()])->count();
        $cardCount = UserCard::find()->andWhere(['user_id' => $user->id, 'is_delete' => 0, 'is_use' => 0])
            ->andWhere(['>', 'end_time', mysql_timestamp()])->count();

        $favoriteCount = Favorite::find()->alias('f')->where(['f.user_id' => $user->id, 'f.is_delete' => 0])
            ->leftJoin(['g' => Goods::tableName()], 'g.id = f.goods_id')
            ->andWhere(['g.status' => 1, 'g.is_delete' => 0])->count();

        $result = [
            'nickname' => $user->nickname,
            'mobile' => $user->mobile,
            'avatar' => $userInfo->avatar,
            'integral' => $userInfo->integral,
            'balance' => $userInfo->balance,
            'options' => $userInfo,
            'favorite' => $favoriteCount ?? '0',
            'footprint' => FootprintGoodsLog::find()->where(['user_id' => $user->id, 'is_delete' => 0])->count() ?? '0',
            'identity' => [
                'parent_name' => $parentName,
                'level_name' => $levelName,
                'member_level' => $user->identity->member_level,
                'member_pic_url' => $memberPicUrl,
                'is_admin' => $user->identity->is_admin,
            ],
            'coupon' => $couponCount,
            'card' => $cardCount,
            'is_vip_card_user' => 0,
        ];
        $result = array_merge($result, \Yii::$app->plugin->getUserInfo($user));
        return $result;
    }

    public function getInfo()
    {
        $result = $this->userInfo();
        /** @var User $user */
        $user = \Yii::$app->user->identity;
        $cacheKey = 'user_register_' . $user->id . '_' . $user->mall_id;
        $couponList = \Yii::$app->cache->get($cacheKey);
        if ($couponList && count($couponList) > 0) {
            $result['register'] = ['coupon_list' => $couponList];
            \Yii::$app->cache->delete($cacheKey);
        }

        return [
            'code' => 0,
            'data' => $result,
        ];
    }

    public function config()
    {
        $mall = \Yii::$app->mall->getMallSetting();
        $mall['setting']['web_service_url'] = urlencode($mall['setting']['web_service_url']);

        $userCenter = CommonAppConfig::getUserCenter();

        if (!\Yii::$app->user->isGuest) {
            $orderInfoCount = (new CommonOrder())->getOrderInfoCount();
            foreach ($orderInfoCount as $i => $v) {
                $userCenter['order_bar'][$i]['text'] = $orderInfoCount[$i] ?: '';
                $userCenter['order_bar'][$i]['num'] = $orderInfoCount[$i] ?: '';
            }
        }

        // 商城权限判断
        $permissions = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        $menus = $userCenter['menus'];
        foreach ($menus as $key => $menu) {
            if (isset($menu['key']) && !in_array($menu['key'], $permissions)) {
                unset($menus[$key]);
            }
        }
        $transform = Transform::getInstance();
        if (isset($mall['setting']['is_not_share_show']) && $mall['setting']['is_not_share_show'] == 0) {
            if (!(!\Yii::$app->user->isGuest && \Yii::$app->user->identity->identity->is_distributor == 1)) {
                $transform->setNotSupport([
                    'user_center' => [
                        '/pages/share/index/index'
                    ],
                ]);
            }
        }
        $userCenter['menus'] = $transform->transformUserCenter(array_values($menus));

        $res = [
            'code' => 0,
            'data' => [
                'mall' => $mall,
                'config' => [
                    'title_bar' => [
                        'background' => '#ff4544',
                        'color' => '#ffffff',
                    ],
                    'user_center' => $userCenter,
                    'copyright' => CommonAppConfig::getCoryRight(),
                ],
                'user_info' => $this->userInfo(),
            ],
        ];

        return $res;
    }
}
