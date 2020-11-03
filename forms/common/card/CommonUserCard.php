<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/15
 * Time: 15:31
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\forms\common\card;


use app\models\ClerkUser;
use app\models\ClerkUserStoreRelation;
use app\models\Mall;
use app\models\Model;
use app\models\OrderRefund;
use app\models\User;
use app\models\UserCard;
use yii\db\Exception;

/**
 * @property Mall $mall
 * @property User $user
 */
class CommonUserCard extends Model
{
    public $cardId;
    public $mall;
    public $user;
    public $userId;
    public $isArray = false;

    /**
     * @return array|\yii\db\ActiveRecord|null
     * @throws Exception
     * 卡券信息
     */
    public function detail()
    {
        $userCard = UserCard::find()->where(['id' => $this->cardId, 'is_delete' => 0, 'mall_id' => $this->mall->id])
            ->with(['clerk', 'store', 'order', 'detail'])->asArray($this->isArray)->one();

        if (!$userCard) {
            throw new Exception('卡券不存在，无效的信息');
        }

        return $userCard;
    }

    /**
     * @return bool
     * @throws Exception
     * 核销卡券
     */
    public function clerk()
    {
        /* @var ClerkUser $clerkUser */
        $clerkUser = ClerkUser::find()->where([
            'mall_id' => $this->mall->id, 'is_delete' => 0, 'user_id' => $this->user->id, 'mch_id' => 0,
        ])->one();
        if (!$clerkUser) {
            throw new Exception('没有核销权限，禁止核销');
        }

        /* @var UserCard $userCard */
        $userCard = $this->detail();

        if ($userCard->order->cancel_status == 2) {
            throw new Exception('订单申请退款中');
        }
        if ($userCard->order->cancel_status == 1) {
            throw new Exception('卡券已失效');
        }

        //售后中退款
        if ($userCard->detail->refund_status == 1) {
            $refund = OrderRefund::findOne(['order_detail_id' => $userCard->order_detail_id, 'type' => 1]);
            if (!empty($refund)) {
                throw new Exception('订单申请退款中');
            }
        }
        //售后退款完成
        if ($userCard->detail->refund_status == 2 && $userCard->detail->is_refund == 1) {
            throw new Exception('卡券已失效');
        }

        if ($userCard->is_use == 1) {
            throw new Exception('卡券已核销');
        }

        if ($userCard->start_time >= mysql_timestamp()) {
            throw new Exception('卡券未到有效期，无法核销');
        }

        if ($userCard->end_time <= mysql_timestamp()) {
            throw new Exception('卡券已过期');
        }
        $relation = ClerkUserStoreRelation::findOne(['clerk_user_id' => $clerkUser->id, 'is_delete' => 0]);

        $userCard->clerk_id = $this->user->id;
        $userCard->store_id = $relation->store_id;
        $userCard->clerked_at = mysql_timestamp();
        $userCard->is_use = 1;
        if ($userCard->save()) {
            return true;
        } else {
            throw new Exception($this->getErrorMsg($userCard), $userCard->errors);
        }
    }
}
