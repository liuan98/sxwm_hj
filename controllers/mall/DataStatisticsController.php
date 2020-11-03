<?php


namespace app\controllers\mall;


use app\forms\mall\statistics\DataForm;
use app\forms\mall\statistics\InitDataForm;

class DataStatisticsController extends MallController
{
    //页面总数据渲染
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new DataForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->search());
        } else {
            return $this->render('index');
        }
    }

    //店铺列表
    public function actionMch_list()
    {
        $form = new DataForm();
        $form->attributes = \Yii::$app->request->get();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->mch_search());
    }

    //图表查询
    public function actionTable()
    {
        $form = new DataForm();
        $form->attributes = \Yii::$app->request->get();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->table_search());
    }

    //商品查询-排序
    public function actionGoods_top()
    {
        if (\Yii::$app->request->post('flag') === 'EXPORT') {
            $form = new DataForm();
            $form->attributes = \Yii::$app->request->post();
            $form->search(1);
            return false;
        } else {
            $form = new DataForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->search(1));
        }
    }

    //用户查询-排序
    public function actionUsers_top()
    {
        if (\Yii::$app->request->post('flag') === 'EXPORT') {
            $form = new DataForm();
            $form->attributes = \Yii::$app->request->post();
            $form->search(2);
            return false;
        } else {
            $form = new DataForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->search(2));
        }
    }

    // 数据初始
    public function actionInitial()
    {
        $form = new InitDataForm();
        return $this->asJson($form->search());
    }
}
