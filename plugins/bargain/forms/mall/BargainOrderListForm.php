<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/20
 * Time: 15:38
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\plugins\bargain\forms\mall;


use app\core\response\ApiCode;
use app\models\Mall;
use app\models\Model;
use app\plugins\bargain\forms\common\CommonBargainOrder;
use app\plugins\bargain\models\BargainOrder;

/**
 * @property Mall $mall
 */
class BargainOrderListForm extends Model
{
    public $mall;

    public $status;
    public $keyword;
    public $page;
    public $limit;

    public function rules()
    {
        return [
            [['status', 'keyword'], 'trim'],
            [['status', 'keyword'], 'string'],
            [['page', 'limit'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['limit'], 'default', 'value' => 20],
        ];
    }

    public function search()
    {
        if (!$this->mall) {
            $this->mall = \Yii::$app->mall;
        }
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $commonBargainOrder = CommonBargainOrder::getCommonBargainOrder($this->mall);
        $res = $commonBargainOrder->getBargainOrderAll($this->attributes);

        $newList = [];
        /* @var BargainOrder $bargainOrder */
        foreach ($res['list'] as $bargainOrder) {
            $bargainUserOrderAll = $bargainOrder->userOrderList;
            $totalPrice = 0;
            $totalPeople = 0;
            $userList = [];
            foreach ($bargainUserOrderAll as $bargainUserOrder) {
                $totalPrice += floatval($bargainUserOrder->price);
                $totalPeople++;
                $userList[] = [
                    'nickname' => $bargainUserOrder->user->nickname,
                    'avatar' => $bargainUserOrder->user->userInfo->avatar,
                    'price' => $bargainUserOrder->price
                ];
            }


            $newItem = [
                'created_at' => $bargainOrder->created_at,
                'user_id' => $bargainOrder->user_id,
                'nickname' => $bargainOrder->user->nickname,
                'platform' => $bargainOrder->user->userInfo->platform,
                'price' => $bargainOrder->price,
                'min_price' => $bargainOrder->min_price,
                'now_price' => $bargainOrder->getNowPrice($totalPrice),
                'user_list' => $userList,
                'status' => $bargainOrder->status,
                'total_people' => $totalPeople,
                'goods' => [
                    'name' => $bargainOrder->goods->name,
                    'cover_pic' => $bargainOrder->goods->coverPic
                ]
            ];
            $newList[] = $newItem;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $newList,
                'pagination' => $res['pagination']
            ]
        ];
    }
}
