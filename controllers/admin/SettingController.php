<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\controllers\admin;


use app\controllers\behaviors\SuperAdminFilter;
use app\core\response\ApiCode;
use app\forms\admin\mall\FileForm;
use app\forms\admin\mall\MallOverrunForm;
use app\forms\common\attachment\CommonAttachment;
use app\forms\common\CommonOption;
use app\forms\common\UploadForm;
use app\jobs\TestQueueServiceJob;
use app\models\AttachmentStorage;
use app\models\Option;
use yii\web\UploadedFile;

class SettingController extends AdminController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'superAdminFilter' => [
                'class' => SuperAdminFilter::class,
                'safeRoutes' => [
                    'admin/setting/small-routine',
                    'admin/setting/upload-file',
                    'admin/setting/attachment',
                    'admin/setting/attachment-create-storage',
                    'admin/setting/attachment-enable-storage',
                ]
            ],
        ]);
    }

    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $setting = \Yii::$app->request->post('setting');
                if (CommonOption::set(Option::NAME_IND_SETTING, $setting)) {
                    return [
                        'code' => ApiCode::CODE_SUCCESS,
                        'msg' => '保存成功。',
                    ];
                } else {
                    return [
                        'code' => ApiCode::CODE_ERROR,
                        'msg' => '保存失败。',
                    ];
                }
            } else {
                $setting = CommonOption::get(Option::NAME_IND_SETTING);
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => [
                        'setting' => $setting,
                    ],
                ];
            }
        } else {
            return $this->render('index');
        }
    }

    public function actionAttachment()
    {
        if (\Yii::$app->request->isAjax) {
            $user = \Yii::$app->user->identity;
            $common = CommonAttachment::getCommon($user);
            $list = $common->getAttachmentList();
            return $this->asJson([
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list,
                    'storageTypes' => $common->getStorageType()
                ]
            ]);
        } else {
            return $this->render('attachment');
        }
    }

    public function actionAttachmentCreateStorage()
    {
        try {
            $user = \Yii::$app->user->identity;
            $common = CommonAttachment::getCommon($user);
            $data = \Yii::$app->request->post();
            $res = $common->attachmentCreateStorage($data);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function actionAttachmentEnableStorage($id)
    {
        $common = CommonAttachment::getCommon(\Yii::$app->user->identity);
        $common->attachmentEnableStorage($id);
        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '操作成功。',
        ]);
    }

    public function actionOverrun()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->post()) {
                $form = new MallOverrunForm();
                $form->form = \Yii::$app->request->post('form');

                return $this->asJson($form->save());
            } else {
                $form = new MallOverrunForm();
                return $this->asJson($form->setting());
            }
        } else {
            return $this->render('overrun');
        }
    }

    public function actionQueueService($action = null, $id = null)
    {
        if (\Yii::$app->request->isAjax) {
            if ($action == 'create') {
                try {
                    $id = \Yii::$app->queue->delay(0)->push(new TestQueueServiceJob());
                    return [
                        'code' => ApiCode::CODE_SUCCESS,
                        'data' => [
                            'id' => $id,
                        ],
                    ];
                } catch (\Exception $exception) {
                    return [
                        'code' => ApiCode::CODE_ERROR,
                        'msg' => '队列服务测试失败：' . $exception->getMessage(),
                    ];
                }
            }
            if ($action == 'test') {
                $done = \Yii::$app->queue->isDone($id);
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => [
                        'done' => $done ? true : false,
                    ],
                ];
            }
        } else {
            return $this->render('queue-service');
        }
    }

    public function actionSmallRoutine()
    {
        return $this->render('small-routine');
    }

    // 上传业务域名文件
    public function actionUploadFile($name = 'file')
    {
        $form = new FileForm();
        $form->file = UploadedFile::getInstanceByName($name);
        return $this->asJson($form->save());
    }

    public function actionUploadLogo($name = 'file')
    {
        $form = new UploadForm();
        $form->file = UploadedFile::getInstanceByName($name);
        return $this->asJson($form->save());
    }
}
