<?php
/**
 * @copyright (c)天幕网络
 * @author Lu Wei
 * @link http://www.67930603.top/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/14 11:42
 */


namespace app\controllers\api;


use app\forms\AttachmentUploadForm;
use yii\web\UploadedFile;

class AttachmentController extends ApiController
{
    public function actionUpload($name = 'file')
    {
        $form = new AttachmentUploadForm();
        $form->file = UploadedFile::getInstanceByName($name);
        if (\Yii::$app->request->post('file_name') && \Yii::$app->request->post('file_name') !== 'null') {
            $form->file->name = \Yii::$app->request->post('file_name');
        }
        return $this->asJson($form->save());
    }
}
