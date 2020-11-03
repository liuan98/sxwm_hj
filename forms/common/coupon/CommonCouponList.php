<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27
 * Time: 16:31
 */

namespace app\forms\common\coupon;

use app\models\Coupon;
use app\models\CouponCenter;
use app\models\Mall;
use app\models\User;
use app\models\UserCoupon;
use app\models\UserCouponCenter;
use yii\base\BaseObject;
use yii\db\Query;

/**
 * @property User $user
 * @property Mall $mall
 */
class CommonCouponList extends BaseObject
{
    public $mall;
    public $user;
    public $page;
    public $limit;
    public $status;
    public $date;
    public $user_id;
    public $isArray;

    private $is_expired = true;

    public function __construct($config = [], $isArray = true)
    {
        parent::__construct($config);
        $this->mall = \Yii::$app->mall;
        $this->isArray = $isArray;
    }

    public function setExpired(bool $expired):CommonCouponList
    {
        $this->is_expired = $expired;
        return $this;
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     * 获取领券中心的优惠券列表
     */
    public function getList()
    {
        $userId = $this->user ? $this->user->id : 0;
        $userCouponId = null;
        if ($this->user) {
            $userCouponId = UserCouponCenter::find()->alias('ucc')
                ->where(['ucc.mall_id' => $this->mall->id, 'ucc.is_delete' => 0, 'ucc.user_id' => $userId])
                ->select('ucc.user_coupon_id');
        }
        $receiveQuery = UserCoupon::find()->alias('uc')
            ->where(['uc.mall_id' => $this->mall->id, 'uc.is_delete' => 0, 'uc.user_id' => $userId])
            ->keyword($userCouponId, ['uc.id' => $userCouponId])
            ->select('uc.coupon_id coupon_id');

        $receiveQuery = (new Query())->from(['uc' => $receiveQuery])
            ->where('uc.coupon_id=c.id')
            ->select('count(uc.coupon_id)');

        $couponCenter = CouponCenter::find()->where([
            'mall_id' => $this->mall->id,
            'is_delete' => 0
        ])->select('coupon_id');

        $list = Coupon::find()->alias('c')
            ->where([
                'c.mall_id' => $this->mall->id,
                'c.is_delete' => 0,
                'c.id' => $couponCenter
            ])
            ->andWhere([
                'or',
                [
                    'and',
                    ['c.expire_type' => 2],
                    ['>', 'c.end_time', date('Y-m-d H:i:s')]
                ],
                ['c.expire_type' => 1]
            ])
            ->with(['cat', 'goods'])
            ->select(['c.*', 'is_receive' => $receiveQuery])
            ->apiPage($this->limit, $this->page)
            ->orderBy(['is_receive' => SORT_ASC, 'c.sort' => SORT_ASC, 'c.created_at' => SORT_DESC])
            ->asArray($this->isArray)->all();
        array_walk($list, function (&$item) {
            $item['page_url'] = '/pages/goods/list?coupon_id=' . $item['id'];
            if ($item['appoint_type'] == 4) {
                $item['page_url'] = '/plugins/scan_code/index/index';
            }
        });
        unset($item);

        return $list;
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     * 我的优惠券列表
     */
    public function getUserCouponList()
    {
        if ($this->user) {
            $userId = $this->user->id;
        } elseif ($this->user_id) {
            $userId = $this->user_id;
        } else {
            $userId = 0;
        }
        $query = UserCoupon::find()->where(['mall_id' => $this->mall->id, 'is_delete' => 0, 'user_id' => $userId])
            ->with(['coupon', 'coupon.cat', 'coupon.goods'])
            ->orderBy(['created_at' => SORT_DESC]);
        switch ($this->status) {
            case 1:
                $query->andWhere(['is_use' => 0])
                    ->keyword($this->is_expired, ['>', 'end_time', date('Y-m-d H:i:s', time())]);
                break;
            case 2:
                $query->andWhere(['is_use' => 1]);
                break;
            case 3:
                $query->andWhere(['is_use' => 0])
                    ->keyword($this->is_expired, ['<=', 'end_time', date('Y-m-d H:i:s', time())]);
                break;
            default:
        }

        if ($this->date) {
            $query->andWhere(['<', 'created_at', $this->date[1]])->andWhere(['>', 'created_at', $this->date[0]]);
        }

        if ($this->user_id) {
            $query->andWhere(['user_id' => $this->user_id]);
        }

        $list = $query->page($pagination, $this->limit)->asArray($this->isArray)->all();
        return [
            'list' => $list,
            'pagination' => $pagination
        ];
    }
}
