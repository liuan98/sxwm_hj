<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/11
 * Time: 9:57
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\forms\api\coupon;


use app\core\response\ApiCode;
use app\forms\common\coupon\CommonCoupon;
use app\models\Mall;
use app\models\Model;
use app\models\User;
use app\models\UserCoupon;

/**
 * @property Mall $mall
 * @property User $user
 */
class UserCouponDetailForm extends Model
{
    public $user;
    public $mall;

    public $user_coupon_id;

    public function rules()
    {
        return [
            [['user_coupon_id'], 'required']
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $couponCommon = new CommonCoupon();
            $couponCommon->mall = $this->mall;
            $couponCommon->user = $this->user;
            /* @var UserCoupon $userCoupon */
            $userCoupon = $couponCommon->getUserCoupon($this->user_coupon_id);
            if (!$userCoupon) {
                throw new \Exception('用户优惠券不存在或已删除');
            }

            $isExpired = 0;
            if (strtotime($userCoupon->end_time) < time()) {
                $isExpired = 1;
            }
            $data = [
                'sub_price' => $userCoupon->sub_price,
                'discount' => $userCoupon->discount,
                'min_price' => $userCoupon->coupon_min_price,
                'type' => $userCoupon->type,
                'start_time' => $userCoupon->start_time,
                'end_time' => $userCoupon->end_time,
                'is_use' => $userCoupon->is_use,
                'is_expired' => $isExpired,
                'receive_type' => $userCoupon->receive_type,
                'name' => $userCoupon->coupon->name,
                'pic_url' => $userCoupon->coupon->pic_url,
                'desc' => $userCoupon->coupon->desc,
                'rule' => $userCoupon->coupon->rule,
                'appoint_type' => $userCoupon->coupon->appoint_type,
                'goods' => $userCoupon->coupon->getGoods()->select('name')->all(),
                'cat' => $userCoupon->coupon->getCat()->select('name')->all(),
                'coupon_id' => $userCoupon->coupon_id,
                'discount_limit' => $userCoupon->discount_limit,
                'page_url' => $userCoupon->coupon->appoint_type == 4 ? '/plugins/scan_code/index/index' : '/pages/goods/list?coupon_id=' . $userCoupon->coupon_id,
            ];

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'list' => $data
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
