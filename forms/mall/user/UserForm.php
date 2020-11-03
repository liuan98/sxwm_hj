<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\mall\user;

use app\core\response\ApiCode;
use app\forms\mall\export\BalanceLogExport;
use app\forms\mall\export\IntegralExport;
use app\forms\mall\export\UserExport;
use app\models\Order;
use app\models\OrderRefund;
use app\models\User;
use app\models\BalanceLog;
use app\models\IntegralLog;
use app\models\UserIdentity;
use app\models\UserInfo;
use app\models\UserCoupon;
use app\models\UserCard;
use app\models\Share;
use app\forms\common\coupon\CommonCouponList;
use app\models\Model;
use app\models\MallMembers;
use yii\db\Exception;
use app\forms\common\CommonUser;
use yii\helpers\ArrayHelper;

class UserForm extends Model
{
    public $id;
    public $member_level;
    public $page_size;
    public $keyword;
    public $platform;
    public $user_id;
    public $status;
    public $date;
    public $start_date;
    public $end_date;
    public $is_admin;

    public $flag;
    public $fields;
    public $is_change_name = 0;

    public function rules()
    {
        return [
            [['date', 'flag',], 'trim'],
            [['start_date', 'end_date', 'keyword', 'platform'], 'string'],
            [['id', 'member_level', 'user_id', 'status', 'is_admin'], 'integer'],
            [['keyword'], 'string', 'max' => 255],
            [['page_size'], 'default', 'value' => 10],
            [['fields'], 'safe'],
            [['keyword', 'platform'], 'default', 'value' => ''],
            [['is_change_name'], 'boolean'],
        ];
    }

    public function shareUser()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        try {
            /** @var UserInfo $userInfo */
            $userInfo = UserInfo::find()->where(['user_id' => $this->user_id])->one();
            if (!$userInfo) {
                throw new \Exception('用户不存在,ID:' . $this->user_id);
            }

            $query = Share::find()->alias('s')
                ->leftJoin(['u' => User::tableName()], 'u.id=s.user_id')
                ->leftJoin(['ui' => UserInfo::tableName()], 'ui.user_id=s.user_id')
                ->where([
                    'AND',
                    ['s.is_delete' => 0],
                    ['s.mall_id' => \Yii::$app->mall->id],
                    ['s.status' => 1],
                    ['u.is_delete' => 0],
                    ['!=', 'u.id', $this->user_id],
                    ['ui.platform' => $userInfo->platform]
                ]);

            if ($this->keyword) {
                $query->andWhere([
                    'or',
                    ['like', 's.name', $this->keyword],
                    ['like', 'u.nickname', $this->keyword],
                    ['=', 'u.id', $this->keyword],
                ]);
            }
            $list = $query->select('u.id,u.nickname,s.name')->apiPage()->asArray()->all();
            foreach ($list as $k => $v) {
                $list[$k]['new_name'] = $v['nickname'];
                if ($v['name']) {
                    $list[$k]['new_name'] .= '/' . $v['name'];
                }
            }
            array_unshift($list, ['id' => 0, 'new_name' => '总店']);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function searchUser()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $query = User::find()->alias('u')->select('u.id,u.nickname')->where([
            'AND',
            ['or', ['LIKE', 'u.nickname', $this->keyword], ['u.id' => $this->keyword], ['u.mobile' => $this->keyword]],
            ['u.mall_id' => \Yii::$app->mall->id],
        ]);
        $list = $query->InnerJoinwith('userInfo')->orderBy('nickname')->limit(30)->all();

        $newList = [];
        /** @var User $item */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['avatar'] = $item->userInfo ? $item->userInfo->avatar : '';
            $platform = $item->userInfo ? $item->userInfo->platform : '';
            $newItem['nickname'] = UserInfo::getPlatformText($platform) . '（' . $item->nickname . '）';
            $newList[] = $newItem;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $newList
            ]
        ];
    }

    //用户列表
    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };
        $query = User::find()->alias('u')->where([
            'u.is_delete' => 0,
            'u.mall_id' => \Yii::$app->mall->id,
        ])->InnerJoin([
            'i' => UserInfo::tableName()
        ], 'u.id = i.user_id')
            ->InnerJoin([
                'd' => UserIdentity::tableName()
            ], 'd.user_id = u.id');

        $query->keyword($this->member_level, ['d.member_level' => $this->member_level]);
        $query->keyword($this->platform, ['i.platform' => $this->platform]);
        $query->keyword($this->keyword, [
            'OR',
            ['like', 'u.nickname', $this->keyword],
            ['like', 'u.mobile', $this->keyword],
            ['like', 'u.id', $this->keyword],
        ]);
        $query->keyword($this->is_admin, ['d.is_admin' => ($this->is_admin == 1) ? $this->is_admin : 0]);

        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $exp = new UserExport();
            $exp->fieldsKeyList = $this->fields;
            $exp->export($new_query);
            return false;
        }

        $cardQuery = UserCard::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->andWhere('user_id = u.id')->select('count(1)');
        $couponQuery = UserCoupon::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->andWhere('user_id = u.id')->select('count(1)');
        $orderQuery = Order::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->andWhere('user_id = u.id')->select('count(1)');
        $orderSum = Order::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'is_pay' => 1])
            ->andWhere('user_id = u.id')->select(['COALESCE(SUM(`total_price`),0)']);
        //未发货 成功取消的订单金额
        $orderSumCancel = Order::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'is_pay' => 1, 'cancel_status' => 1])
            ->andWhere('user_id = u.id')->select(['COALESCE(SUM(`total_price`),0)']);
        //售后成功的订单金额
        $orderSumRefund = Order::find()->alias('o')->where(['o.mall_id' => \Yii::$app->mall->id, 'o.is_delete' => 0, 'o.is_pay' => 1])
            ->leftJoin(['re' => OrderRefund::tableName()], 're.order_id=o.id')
            ->andWhere(['re.type' => 1, 're.status' => 2])
            ->andWhere('o.user_id = u.id')
            ->select(['COALESCE(SUM(`refund_price`),0)']);
        $mall_members = MallMembers::findAll(['mall_id' => \Yii::$app->mall->id, 'status' => 1, 'is_delete' => 0]);

        $list = $query
            ->select(['i.*', 'u.nickname', 'u.mobile', 'd.member_level', 'd.is_admin', 'coupon_count' => $couponQuery, 'u.mobile',
                'order_count' => $orderQuery,
                'order_sum' => $orderSum,
                'order_sum_cancel' => $orderSumCancel,
                'order_sum_refund' => $orderSumRefund,
                'card_count' => $cardQuery, 'u.created_at'])
            ->page($pagination, $this->page_size)
            ->orderBy('id DESC')
            ->asArray()
            ->all();

        foreach ($list as &$v) {
            if ($this->is_change_name) {
                $v['nickname'] = UserInfo::getPlatformText($v['platform']) . '（' . $v['nickname'] . '）';
            }
            $v['order_sum'] = price_format($v['order_sum'] - $v['order_sum_cancel'] - $v['order_sum_refund']);
        }
        unset($v);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
                'mall_members' => $mall_members,
                'exportList' => (new UserExport())->fieldsList(),
            ]
        ];
    }

    //用户编辑
    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };
        /* @var User $user */
        $user = User::find()->alias('u')
            ->with('parent')
            ->with('identity')
            ->with(['share' => function ($query) {
                $query->where(['is_delete' => 0, 'status' => 1]);
            }])->with('userInfo')
            ->where(['u.id' => $this->id, 'u.mall_id' => \Yii::$app->mall->id])
            ->one();

        if (!$user) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据为空',
            ];
        }

        $newList = [
            'id' => $user->id,
            'username' => $user->username,
            'nickname' => $user->nickname,
            'mobile' => $user->mobile,
            'avatar' => $user->userInfo->avatar,
            'contact_way' => $user->userInfo->contact_way,
            'remark' => $user->userInfo->remark,
            'is_blacklist' => $user->userInfo->is_blacklist,
            'new_name' => $user->parent ? $user->parent->nickname : '总店',
            'money' => $user->share ? $user->share->money : 0,
            'member_level' => (int)$user->identity->member_level,
            'created_at' => $user->created_at,
            'parent_id' => $user->userInfo->parent_id,
            'share' => $user->identity->is_distributor == 1 ? $user->share : null
        ];

        $mall_members = MallMembers::findAll(['mall_id' => \Yii::$app->mall->id, 'status' => 1, 'is_delete' => 0]);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'mall_members' => $mall_members,
            ]
        ];
    }

    /**
     * 优惠券信息
     * @return [type] [description]
     */
    public function getCoupon()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        $form = new CommonCouponList();
        $form->status = $this->status;
        $form->user_id = $this->user_id;
        $form->date = $this->date;
        $data = $form->setExpired(false)->getUserCouponList();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => $data
        ];
    }

    //优惠券删除
    public function destroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = UserCoupon::findOne([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ]);

        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据不存在或已经删除',
            ];
        }
        $model->is_delete = 1;
        $model->deleted_at = date('Y-m-d H:i:s');
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '删除成功'
        ];
    }


    /**
     * 余额记录
     * @return [type] [description]
     */
    public function balanceLog()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        $query = BalanceLog::find()->alias('b')->where([
            'b.mall_id' => \Yii::$app->mall->id,
        ])->joinwith(['user' => function ($query) {
            if ($this->keyword) {
                $query->where(['like', 'nickname', $this->keyword]);
            }
        }])->orderBy('id desc');

        if ($this->user_id) {
            $query->andWhere(['b.user_id' => $this->user_id]);
        }

        if ($this->start_date && $this->end_date) {
            $query->andWhere(['<', 'b.created_at', $this->end_date])
                ->andWhere(['>', 'b.created_at', $this->start_date]);
        }

        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $exp = new BalanceLogExport();
            $exp->fieldsKeyList = $this->fields;
            $exp->export($new_query);
            return false;
        }

        $list = $query->page($pagination, $this->page_size)->asArray()->all();

        foreach ($list as &$v) {
            $v['info_desc'] = json_decode($v['custom_desc'], true);
        };
        unset($v);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'export_list' => (new BalanceLogExport())->fieldsList(),
                'pagination' => $pagination
            ]
        ];
    }

    /**
     * 积分记录
     * @return [type] [description]
     */
    public function integralLog()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        $query = IntegralLog::find()->alias('i')->where([
            'i.mall_id' => \Yii::$app->mall->id,
        ])->joinwith(['user' => function ($query) {
            if ($this->keyword) {
                $query->where(['like', 'nickname', $this->keyword]);
            }
        }])->orderBy('id desc');

        if ($this->user_id) {
            $query->andWhere(['i.user_id' => $this->user_id]);
        }

        if ($this->start_date && $this->end_date) {
            $query->andWhere(['<', 'i.created_at', $this->end_date])->andWhere(['>', 'i.created_at', $this->start_date]);
        }

        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $exp = new IntegralExport();
            $exp->fieldsKeyList = $this->fields;
            $exp->export($new_query);
            return false;
        }

        $list = $query->page($pagination, $this->page_size)->asArray()->all();

        foreach ($list as &$v) {
            $v['info_desc'] = json_decode($v['custom_desc'], true);
        };
        unset($v);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'export_list' => (new IntegralExport())->fieldsList(),
                'pagination' => $pagination,
            ]
        ];
    }
}
