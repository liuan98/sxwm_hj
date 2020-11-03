<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 15:44
 */

namespace app\forms\api\coupon;


use app\core\response\ApiCode;
use app\forms\common\coupon\CommonCoupon;
use app\forms\common\coupon\UserCouponCenter;
use app\models\Model;

class CouponDetailForm extends Model
{
    public $coupon_id;

    public function rules()
    {
        return [
            ['coupon_id', 'required'],
            ['coupon_id', 'integer'],
        ];
    }


    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $common = new CommonCoupon($this->attributes, true);
            $common->user = \Yii::$app->user->identity;
            $res = $common->getDetail();
            if ($res['is_delete'] == 1) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '优惠券不存在'
                ];
            }
            if (isset($res['couponCat'])) {
                unset($res['couponCat']);
            }
            if (isset($res['couponGood'])) {
                unset($res['couponGood']);
            }
            if ($res['appoint_type'] == 1) {
                $res['goods'] = [];
            }
            if ($res['appoint_type'] == 2) {
                $res['cat'] = [];
            }
            if ($res['appoint_type'] == 3) {
                $res['goods'] = [];
                $res['cat'] = [];
            }
            $res['page_url'] = '/pages/goods/list?coupon_id=' . $res['id'];
            if ($res['appoint_type'] == 4) {
                $res['page_url'] = '/plugins/scan_code/index/index';
            }
            $res['begin_time'] = date('Y-m-d', strtotime($res['begin_time']));
            $res['end_time'] = date('Y-m-d', strtotime($res['end_time']));
            $res['receive_count'] = !\Yii::$app->user->isGuest ? $common->checkReceive($res['id']) : '0';

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => 'success',
                'data' => [
                    'list' => $res
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function receive()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $common = new CommonCoupon($this->attributes, false);
            $common->user = \Yii::$app->user->identity;
            $coupon = $common->getDetail();
            if ($coupon->is_delete == 1) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '优惠券不存在'
                ];
            }
            $count = $common->checkReceive($coupon->id);
            if ($count > 0) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '已领取优惠券'
                ];
            } else {
                $class = new UserCouponCenter($coupon, $common->user);
                if ($common->receive($coupon, $class, '领券中心领取')) {
                    return [
                        'code' => ApiCode::CODE_SUCCESS,
                        'msg' => '领取成功'
                    ];
                } else {
                    return [
                        'code' => ApiCode::CODE_ERROR,
                        'msg' => '优惠券已领完'
                    ];
                }
            }
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }
}
