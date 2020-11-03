<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/15
 * Time: 18:49
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\plugins\diy\controllers\mall;


use app\core\response\ApiCode;
use app\forms\admin\mall\MallOverrunForm;
use app\forms\common\CommonOption;
use app\models\Option;
use app\plugins\Controller;
use app\plugins\diy\forms\common\CommonTemplate;
use app\plugins\diy\forms\mall\GoodsForm;
use app\plugins\diy\forms\mall\TemplateEditForm;
use app\plugins\diy\forms\mall\TemplateForm;

class TemplateController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new TemplateForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        } else {
            return $this->render('index');
        }
    }

    public function actionEdit($id = null)
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new TemplateEditForm();
                $form->attributes = \Yii::$app->request->post();
                $form->id = $id;
                return $this->asJson($form->save());
            } else {
                $common = CommonTemplate::getCommon();
                $template = $common->getTemplate($id);
                $option = (new MallOverrunForm())->getSetting();
                $data = [
                    'allComponents' => $common->allComponents(),
                    'overrun' => $option
                ];
                if ($template) {
                    $templateData = json_decode($template->data, true);
                    $newData = [];
                    foreach ($templateData as $datum) {
                        $flag = false;
                        foreach ($data['allComponents'] as $allComponent) {
                            foreach ($allComponent['list'] as $item) {
                                if ($datum['id'] == $item['id'] || $datum['id'] == 'background') {
                                    $flag = true;
                                }
                            }
                        }
                        if ($flag) {
                            $newData[] = $datum;
                        }
                    }
                    $data['name'] = $template->name;
                    $data['data'] = json_encode($newData, JSON_UNESCAPED_UNICODE);
                }
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => $data
                ];
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionDestroy()
    {
        $form = new TemplateForm();
        $id = \Yii::$app->request->get('id');
        return $this->asJson($form->destroy($id));
    }

    public function actionGetGoods()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->get();
        $form->setSign(\Yii::$app->request->get('sign'));
        return $this->asJson($form->search());
    }
}
