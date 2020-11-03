<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/14
 * Time: 9:14
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\plugins\booking\controllers\api;

use app\controllers\api\ApiController;
use app\plugins\booking\forms\api\GoodsForm;
use app\plugins\booking\forms\api\GoodsListForm;

class GoodsController extends ApiController
{
    //列表v
    public function actionList()
    {
        $form = new GoodsListForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }

    //详情v待补充
    public function actionDetail()
    {
        $form = new GoodsForm();
        //$form->goods_id = 1;
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }
}
