<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\integral_mall\forms\common;


use app\core\response\ApiCode;
use app\models\Coupon;
use app\models\Mall;
use app\models\Model;
use app\plugins\integral_mall\models\IntegralMallCoupons;
use app\plugins\integral_mall\models\IntegralMallCouponsOrders;
use app\plugins\integral_mall\models\IntegralMallOrders;

/**
 * @property Mall $mall
 */
class CouponListForm extends Model
{
    public $mall;
    public $page;
    public $keyword;

    public function search()
    {

        $res = $this->getCouponList();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $res['list'],
                'pagination' => $res['pagination'],
            ]
        ];
    }

    public function getCouponList()
    {
        if (!$this->mall) {
            $this->mall = \Yii::$app->mall;
        }
        $time = date('Y-m-d H:i:s');
        $query = IntegralMallCoupons::find()->alias('imc')->where([
            'imc.mall_id' => $this->mall->id,
            'imc.is_delete' => 0,
        ])->innerJoin(['c' => Coupon::tableName()], 'c.id=imc.coupon_id')
            ->andWhere([
                'or',
                [
                    'and',
                    ['c.expire_type' => 2],
                    ['>', 'c.end_time', $time]
                ],
                ['c.expire_type' => 1]
            ]);

        if ($this->keyword) {
            $couponIds = Coupon::find()->where(['like', 'name', $this->keyword])->select('id');
            $query->andWhere(['coupon_id' => $couponIds]);
        }

        $list = $query->with('coupon.couponCat', 'coupon.couponGoods', 'couponOrders')
            ->orderBy(['imc.id' => SORT_DESC])
            ->page($pagination)
            ->asArray()
            ->all();

        foreach ($list as &$item) {
            $item['is_receive'] = 0;
            if ($item['exchange_num'] != -1 && count($item['couponOrders']) >= $item['exchange_num']) {
                $item['is_receive'] = 1;
            }
            $item['page_url'] = '/pages/goods/list?coupon_id=' . $item['id'];
            if ($item['coupon']['appoint_type'] == 4) {
                $item['page_url'] = '/plugins/scan_code/index/index';
            }
        }

        return [
            'list' => $list,
            'pagination' => $pagination
        ];
    }


    public function getCoupon($id)
    {
        if (!$this->mall) {
            $this->mall = \Yii::$app->mall;
        }
        $query = IntegralMallCoupons::find()->where([
            'mall_id' => $this->mall->id,
            'is_delete' => 0,
            'id' => $id
        ]);

        $detail = $query->with('coupon.couponCat', 'coupon.couponGoods')
            ->orderBy('created_at DESC')
            ->asArray()
            ->one();

        return $detail;
    }
}
