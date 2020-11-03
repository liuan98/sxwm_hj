<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/15
 * Time: 18:25
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\forms\mall\user;

use app\core\response\ApiCode;
use app\models\Store;
use app\models\User;
use app\models\UserCard;
use app\models\Model;

class UserCardForm extends Model
{
    public $user_id;
    public $id;
    public $ids;
    public $status;
    public $clerk_id;
    public $store_id;
    public $user_name;
    public $store_name;
    public $card_name;

    public $send_date;
    public $clerk_date;

    public function rules()
    {
        return [
            [['status'], 'default', 'value' => -1],
            [['user_id', 'store_id', 'clerk_id', 'id'], 'integer'],
            [['user_name', 'send_date', 'clerk_date','store_name','card_name', 'ids'], 'trim']
        ];
    }

    public function getCard()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = UserCard::find()->alias('uc')->where([
                'uc.mall_id' => \Yii::$app->mall->id,
                'uc.is_delete' => 0,
            ])->leftJoin(['u' => User::tableName()], 'u.id = uc.user_id')
            ->leftJoin(['s' => Store::tableName()], 's.id = uc.store_id');

        $query = $query->keyword($this->user_id, ['uc.user_id' => $this->user_id])
                ->keyword($this->status == 1, ['uc.is_use' => 0])
                ->keyword($this->status == 2, ['uc.is_use' => 1])
                ->keyword($this->clerk_id, ['uc.clerk_id' => $this->clerk_id])
                ->keyword($this->store_id, ['uc.store_id' => $this->store_id])
                ->keyword($this->user_name, ['like','u.nickname',$this->user_name])
                ->keyword($this->store_name, ['like','s.name',$this->store_name])
                ->keyword($this->card_name, ['like','uc.name',$this->card_name]);


        if ($this->send_date && count($this->send_date) > 0) {
            $query->andWhere([
                'AND',
                ['>=','uc.created_at',$this->send_date[0]],
                ['<=','uc.created_at',$this->send_date[1]]
            ]);
        };

        if ($this->clerk_date && count($this->clerk_date) > 0) {
            $query->andWhere([
               'AND',
               ['>=','uc.clerked_at',$this->clerk_date[0]],
               ['<=','uc.clerked_at',$this->clerk_date[1]]
            ]);
        };

        $list = $query->select(['uc.*','u.nickname','s.name store_name'])
                    ->orderBy('created_at DESC')
                    ->page($pagination)
                    ->asArray()
                    ->all();

        $byUsername = '';
        if ($this->user_id) {
            $byUser = User::findOne($this->user_id);
            $byUsername = $byUser ? $byUser->nickname : '';
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' =>   [
                'list' => $list,
                'pagination' => $pagination,
                'by_username' => $byUsername
            ]
        ];
    }

    public function destroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = UserCard::findOne([
            'id' => $this->id,
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据不存在或已删除',
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

    public function batchDestroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $ids = $this->ids;
        if (!$ids) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据不存在或已删除',
            ];
        }

        UserCard::updateAll(['is_delete' => 1,'deleted_at' => date('Y-m-d H:i:s')], [
            'id' => $ids,
        ]);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '删除成功'
        ];
    }
}
