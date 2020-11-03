<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/2
 * Time: 16:51
 */

namespace app\forms\api;


use app\core\response\ApiCode;
use app\forms\api\goods\ApiGoods;
use app\forms\common\goods\CommonGoodsMember;
use app\models\Favorite;
use app\models\Goods;
use app\models\Model;
use app\models\Topic;
use app\models\TopicFavorite;
use app\models\User;
use yii\helpers\ArrayHelper;

class FavoriteListForm extends Model
{
    public $page;
    public $limit;

    public function rules()
    {
        return [
            [['page', 'limit'], 'integer'],
            ['page', 'default', 'value' => 1],
            ['limit', 'default', 'value' => 20]
        ];
    }

    public function goods()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $query = Favorite::find()
                ->where(['user_id' => \Yii::$app->user->id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])
                ->select('goods_id');
            $list = Goods::find()->with('goodsWarehouse')
                ->where(['is_delete' => 0, 'status' => 1, 'mall_id' => \Yii::$app->mall->id])
                ->andWhere(['id' => $query])->apiPage($this->limit, $this->page)->all();
            $newList = [];
            foreach ($list as $item) {
                $apiGoods = ApiGoods::getCommon();
                $apiGoods->goods = $item;
                $apiGoods->isSales = 0;
                $goods = $apiGoods->getDetail();
                $newItem = [];
                $newItem['goods'] = $goods;

                $newList[] = $newItem;
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => 'success',
                'data' => [
                    'list' => $newList
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function topic()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $mall = \Yii::$app->mall;
        /* @var User $user */
        $user = \Yii::$app->user->identity;
        $favoriteQuery = TopicFavorite::find()->where(['mall_id' => $mall->id, 'user_id' => $user->id, 'is_delete' => 0])
            ->select('topic_id');
        $list = Topic::find()->where(['is_delete' => 0, 'mall_id' => $mall->id, 'id' => $favoriteQuery])
            ->apiPage($this->limit, $this->page)->asArray()
            ->orderBy(['sort' => SORT_ASC])->all();

        foreach ($list as &$item) {
            $readCount = intval($item['read_count'] + $item['virtual_read_count']);
            $item['read_count'] = $readCount < 10000 ? $readCount . '人浏览' : intval($readCount / 10000) . '万+人浏览';
            $goodsClass = 'class="goods-link"';
            $goodsCount = mb_substr_count($item['content'], $goodsClass);
            $item['goods_count'] = $goodsCount ? $goodsCount . '件宝贝' : 0;
            $item['pic_list'] = json_decode($item['pic_list'], true);
        }
        unset($item);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $list
            ]
        ];
    }
}
