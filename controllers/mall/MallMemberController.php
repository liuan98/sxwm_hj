<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\controllers\mall;


use app\core\response\ApiCode;
use app\forms\mall\mall_member\MallMemberEditForm;
use app\forms\mall\mall_member\MallMemberForm;
use app\plugins\vip_card\models\VipCardSetting;

class MallMemberController extends MallController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new MallMemberForm();
                $form->attributes = \Yii::$app->request->get();
                $list = $form->getList();

                return $this->asJson($list);
            }
        } else {
            return $this->render('index');
        }
    }

    /**
     * 添加、编辑
     * @return string|\yii\web\Response
     */
    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new MallMemberEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                $res = $form->save();

                return $this->asJson($res);
            } else {
                $form = new MallMemberForm();
                $form->attributes = \Yii::$app->request->get();
                $detail = $form->getDetail();

                return $this->asJson($detail);
            }
        } else {
            return $this->render('edit');
        }
    }


    /**
     * 删除
     * @return \yii\web\Response
     */
    public function actionDestroy()
    {
        $form = new MallMemberForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->destroy();

        return $this->asJson($res);
    }


    /**
     * 获取会员等级列表
     * @return \yii\web\Response
     */
    public function actionOptions()
    {
        $form = new MallMemberForm();
        $res = $form->getOptionList();

        return $this->asJson($res);
    }

    /**
     * 获取所有会员
     * @return \yii\web\Response
     */
    public function actionAllMember()
    {
        $form = new MallMemberForm();
        $res = $form->getAllMember();

        return $this->asJson($res);
    }

    public function actionSwitchStatus()
    {
        $form = new MallMemberForm();
        $form->attributes = \Yii::$app->request->post();

        return $this->asJson($form->switchStatus());
    }

    /**
     * 判断是否有会员卡插件权限
     * @return array
     * @throws \app\core\exceptions\ClassNotFoundException
     */
    public function actionVipCardPermission()
    {
        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        if (!in_array('vip_card', $permission)) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '无会员卡权限',
            ];
        }

        try {
            $plugin = \Yii::$app->plugin->getPlugin('vip_card');
            $setting = $plugin->getSetting();
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '无会员卡权限',
            ];
        }

        $p= \Yii::$app->request->get('plugin','');
        if ($p && !in_array($p,$setting['rules'])) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => "插件{$p}无权限",
            ];
        }

        if ($setting['is_vip_card'] == 0) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '会员卡插件已关闭',
            ];
        }

        $card = $plugin->getCard();
        if (!$card) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '尚未添加会员卡',
            ];
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '有会员卡权限',
        ];
    }
}
