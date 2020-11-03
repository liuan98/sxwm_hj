<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\integral_mall\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\User;
use app\plugins\integral_mall\models\IntegralMallCouponsUser;

class CouponUserForm extends Model
{
    public $page;
    public $keyword;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['page'], 'integer'],
            [['keyword'], 'string'],
            [['page'], 'default', 'value' => 1]
        ]);
    }

    public function getList()
    {

        $query = IntegralMallCouponsUser::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ]);

        if ($this->keyword) {
            $userIds = User::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0
            ])->andWhere(['like', 'nickname', $this->keyword])->select('id');
            $query->andWhere(['user_id' => $userIds]);
        }

        $list = $query->with(['integralMallCoupon.coupon', 'userCoupon', 'user.userInfo'])
            ->orderBy('created_at DESC')
            ->page($pagination)
            ->asArray()
            ->all();

        foreach ($list as &$item) {
            if ($item['userCoupon']) {
                $item['userCoupon']['is_expired'] = $item['userCoupon']['end_time'] > date('Y-m-d H:i:s') ? 0 : 1;
            }
        }
        unset($item);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }
}
