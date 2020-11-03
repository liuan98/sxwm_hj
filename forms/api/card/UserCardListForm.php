<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/15
 * Time: 13:57
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\forms\api\card;


use app\forms\common\card\CommonUserCardList;
use app\models\Mall;
use app\models\Model;
use app\models\User;
use app\models\UserCard;

/**
 * @property Mall $mall
 * @property User $user
 */
class UserCardListForm extends Model
{
    public $mall;
    public $user;
    public $page;
    public $limit;
    public $status;

    public function rules()
    {
        return [
            [['page', 'limit', 'status'], 'integer'],
            ['page', 'default', 'value' => 1],
            ['limit', 'default', 'value' => 10],
            ['status', 'default', 'value' => 1]
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $common = new CommonUserCardList($this->attributes);
        $common->mall = $this->mall;
        $common->user = $this->user;
        $common->user_id = $this->user->id;
        $common->isArray = false;
        $res = $common->getUserCardList();
        $list = $res['list'];
        $newList = [];
        /* @var UserCard[] $list*/
        foreach ($list as $item) {
            $newList[] = [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'mall_id' => $item->mall_id,
                'name' => $item->name,
                'pic_url' => $item->pic_url,
                'content' => $item->content,
                'is_use' => $item->is_use,
                'is_may_use' => $this->status != 1 ? 0 : 1
            ];
        }
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'list' => $newList
            ]
        ];
    }
}
