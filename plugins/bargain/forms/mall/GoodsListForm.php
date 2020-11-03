<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/20
 * Time: 9:42
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\plugins\bargain\forms\mall;


use app\forms\mall\goods\BaseGoodsList;
use app\models\BaseQuery\BaseActiveQuery;

class GoodsListForm extends BaseGoodsList
{

    public $goodsModel = 'app\plugins\bargain\models\Goods';

    /**
     * @param BaseActiveQuery $query
     * @return mixed
     */
    protected function setQuery($query)
    {
        $query->andWhere([
            'g.sign' => \Yii::$app->plugin->getCurrentPlugin()->getName(),
        ])->with('bargainGoods');

        return $query;
    }

    protected function handleGoodsData($goods)
    {
        $newItem = [];
        $newItem['num_count'] = $goods->bargainGoods->stock;

        return $newItem;
    }
}
