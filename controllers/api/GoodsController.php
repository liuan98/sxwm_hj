<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/20
 * Time: 15:15
 */

namespace app\controllers\api;


use app\controllers\api\filters\LoginFilter;
use app\forms\api\CommentsForm;
use app\forms\api\goods\GoodsListForm;
use app\forms\api\GoodsForm;
use app\forms\api\RecommendForm;

class GoodsController extends ApiController
{
    public function actionDetail()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getDetail());
    }

    public function actionCommentsList()
    {
        $form = new CommentsForm();
        $form->attributes = \Yii::$app->request->get();
        $form->mall = \Yii::$app->mall;
        return $this->asJson($form->search());
    }

    // TODO 即将废弃
    public function actionRecommend()
    {
        $form = new RecommendForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    public function actionNewRecommend()
    {
        $form = new RecommendForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getNewList());
    }

    public function actionGoodsList()
    {
        $form = new GoodsListForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }
}
