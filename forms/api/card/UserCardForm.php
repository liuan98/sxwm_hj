<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/15
 * Time: 14:58
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\forms\api\card;


use app\core\response\ApiCode;
use app\forms\common\card\CommonUserCard;
use app\forms\common\CommonQrCode;
use app\models\ClerkUser;
use app\models\Model;
use app\models\Store;
use app\models\User;
use app\models\UserCard;
use yii\db\Exception;

class UserCardForm extends Model
{
    public $cardId;
    public $mall;
    public $user;

    public $is_clerk;
    public $clerk_id;
    public $keyword;

    public function rules()
    {
        return [
            [['cardId', 'is_clerk', 'clerk_id'], 'integer'],
            ['keyword', 'string']
        ];
    }

    public function getList()
    {
        try {
            $query = UserCard::find()->alias('uc')->leftJoin(['u' => User::tableName()], 'u.id = uc.user_id and u.is_delete = 0')
                ->select(['uc.id', 'uc.card_id', 'uc.name', 'uc.pic_url', 'u.nickname', 'uc.user_id', 'uc.store_id', 'uc.is_use'])
                ->with('user.userInfo')->with('store')
                ->where(['uc.is_delete' => 0, 'uc.mall_id' => \Yii::$app->mall->id]);
            if ($this->is_clerk && $this->is_clerk == 1) {
                $query->andWhere(['and', ['uc.is_use' => 1], ['>', 'uc.clerk_id', 0]]);
            }
            if ($this->is_clerk && $this->is_clerk == 2) {
                $query->andWhere(['uc.clerk_id' => 0, 'uc.is_use' => 0]);
            }
            if ($this->clerk_id) {
                $query->andWhere(['uc.clerk_id' => $this->clerk_id]);
            } else {
                $query->andWhere(['>', 'uc.end_time', date('Y-m-d H:i:s', time())]);
                // 门店搜索
//                $storeIds = Store::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
//                    ->andWhere(['like', 'name', $this->keyword])->select('id')->asArray()->all();
//                $arr = [];
//                foreach ($storeIds as $storeId) {
//                    $arr[] = $storeId['id'];
//                }
//                $query->andWhere(['in', 'uc.store_id', $arr]);
            }
            if ($this->keyword) {

                $query->andWhere(['or', ['like', 'u.nickname', $this->keyword], ['like', 'uc.name', $this->keyword]]);
            }
            $list = $query->orderBy('uc.id desc')->page($pagination)->asArray()->all();
            foreach ($list as $key => $value) {
                $list[$key]['store_name'] = $value['store']['name'];
//                $list[$key]['nickname'] = $value['user']['nickname'];
                $list[$key]['platform'] = $value['user']['userInfo']['platform'];
                unset($list[$key]['store']);
                unset($list[$key]['user']);
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'pagination' => $pagination,
                    'list' => $list
                ]
            ];
        } catch (Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => $e
            ];
        }
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $common = new CommonUserCard();
            $common->mall = $this->mall;
            $common->user = $this->user;
            $common->cardId = $this->cardId;
            $common->userId = $this->user->id;
            $common->isArray = true;
            $card = $common->detail();
            $card['endTime'] = strtotime($card['end_time']);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'card' => $card
                ]
            ];
        } catch (Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => $e
            ];
        }
    }

    public function clerk()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $common = new CommonUserCard();
            $common->mall = $this->mall;
            $common->user = $this->user;
            $common->cardId = $this->cardId;
            $common->user = \Yii::$app->user->identity;
            if ($common->clerk()) {
                //权限判断，用以核销后返回的页面判断
                $is_clerk = 1;
                $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
                if (empty(\Yii::$app->plugin->getInstalledPlugin('clerk')) || !in_array('clerk', $permission) || empty(ClerkUser::findOne(['user_id' => \Yii::$app->user->id, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]))) {
                    $is_clerk = 0;
                }
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '核销成功',
                    'data' => [
                        'is_clerk' => $is_clerk
                    ]
                ];
            } else {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '核销失败'
                ];
            }
        } catch (Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'errors' => $e
            ];
        }
    }

    public function qrcode()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $common = new CommonQrCode();
            $img = $common->getQrCode(['cardId' => $this->cardId], 430, 'pages/card/clerk/clerk');
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => $img
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
