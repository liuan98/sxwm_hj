<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\pintuan\controllers\mall;


use app\plugins\Controller;
use app\plugins\pintuan\forms\mall\GoodsEditForm;
use app\plugins\pintuan\forms\mall\GoodsForm;
use app\plugins\pintuan\forms\mall\GoodsListForm;
use app\plugins\pintuan\forms\mall\PinTuanGoodsEditForm;
use app\plugins\pintuan\forms\mall\PinTuanGoodsForm;

class GoodsController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new GoodsListForm();
            $form->attributes = \Yii::$app->request->get();
            $form->search = \Yii::$app->request->get('search');
            $res = $form->getList();

            return $this->asJson($res);
        } else {
            return $this->render('index');
        }
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new GoodsForm();
                $form->mall = \Yii::$app->mall;
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->search());
            }
            if (\Yii::$app->request->isPost) {
                $form = new GoodsEditForm();
                $data = \Yii::$app->request->post();
                $dataForm = json_decode($data['form'], true);
                $attrGroups = json_decode($data['attrGroups'], true);
                $form->attributes = isset($dataForm) ? $dataForm : [];
                $form->attrGroups = isset($attrGroups) ? $attrGroups : [];
                $form->mall = \Yii::$app->mall;
                return $this->asJson($form->save());
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionSwitchStatus()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $form->setSign(\Yii::$app->plugin->getCurrentPlugin()->getName());
        $res = $form->switchStatus();

        return $this->asJson($res);
    }

    /**
     * 热销状态
     * @return \yii\web\Response
     */
    public function actionSwitchSellWell()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->switchSellWell();

        return $this->asJson($res);
    }

    public function actionPintuan()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new PinTuanGoodsForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->detail());
            }
            if (\Yii::$app->request->isPost) {
                $form = new PinTuanGoodsEditForm();
                $data = \Yii::$app->request->post();
                $dataForm = json_decode($data['form'], true);
                $form->data = $dataForm;
                $form->attributes = $data['goods'];
                return $this->asJson($form->save());
            }
        } else {
            return $this->render('pintuan');
        }
    }

    public function actionBatchUpdateHotSell()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->updateHotSell();

        return $this->asJson($res);
    }

    public function actionBatchUpdateStatus()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->batchUpdateStatus();

        return $this->asJson($res);
    }
}
