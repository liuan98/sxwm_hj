<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/3/15
 * Time: 11:01
 */

namespace app\controllers\admin;


use app\core\response\ApiCode;
use app\jobs\CleanWechatCacheJob;

class CacheController extends AdminController
{
    public function actionClean()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = \Yii::$app->request->post();
                if (isset($form['data']) && $form['data'] == 'true') {
                    \Yii::$app->cache->flush();
                    \Yii::$app->queue->delay(0)->push(new CleanWechatCacheJob());
                }
                if (isset($form['file']) && $form['file'] == 'true') {
                    $path = \Yii::$app->basePath . '/web/temp';
                    if (file_exists($path)) {
                        remove_dir($path);
                    }
                }
                if (isset($form['update']) && $form['update'] == 'true') {
                    $path = \Yii::$app->runtimePath . '/plugin-package';
                    if (file_exists($path)) {
                        remove_dir($path);
                    }
                    $path = \Yii::$app->runtimePath . '/update-package';
                    if (file_exists($path)) {
                        remove_dir($path);
                    }
                }
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '清理成功。',
                    'data' => $form,
                ];
            }
        } else {
            return $this->render('clean');
        }
    }
}
