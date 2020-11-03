<?php
/**
 * @copyright (c)天幕网络
 * @author Lu Wei
 * @link http://www.67930603.top/
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/30 14:44
 */


namespace app\plugins\bargain\controllers\mall;


use app\plugins\bargain\controllers\Controller;
use app\plugins\bargain\forms\common\BannerListForm;
use app\plugins\bargain\forms\mall\BannerForm;
use app\plugins\bargain\forms\mall\SettingForm;
use app\plugins\bargain\forms\mall\TemplateForm;

class IndexController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionIndexData()
    {
        if (\Yii::$app->request->isAjax) {
            // 获取数据
            $form = new SettingForm();
            if (\Yii::$app->request->isGet) {
                return $this->asJson($form->getList());
            }
            // 保存数据
            if (\Yii::$app->request->isPost) {
                $form->attributes = \Yii::$app->request->post();
                $form->mall = \Yii::$app->mall;
                return $this->asJson($form->save());
            }
        }
    }

    public function actionBanner()
    {
        return $this->render('banner');
    }

    public function actionBannerStore()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new BannerListForm();
                $form->attributes = \Yii::$app->request->get();
                $form->mall = \Yii::$app->mall;
                return $this->asJson($form->search());
            }
            if (\Yii::$app->request->isPost) {
                $form = new BannerForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            }
        }
    }

    public function actionTemplate()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new TemplateForm();
                $form->mall = \Yii::$app->mall;
                $add = \Yii::$app->request->get('add');
                $platform = \Yii::$app->request->get('platform');
                return $this->asJson($form->getDetail($add, $platform));
            }
            if (\Yii::$app->request->isPost) {
                $form = new TemplateForm();
                $form->attributes = \Yii::$app->request->post();
                $form->mall = \Yii::$app->mall;
                return $this->asJson($form->save());
            }
        }
        return $this->render('template');
    }
}
