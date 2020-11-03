<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\mall\user;

use app\core\response\ApiCode;
use app\events\ShareMemberEvent;
use app\handlers\HandlerRegister;
use app\models\User;
use app\models\Model;

class UserEditForm extends Model
{
    public $id;
    public $member_level;
    public $money;

    public $is_blacklist;
    public $remark;
    public $contact_way;
    public $parent_id;

    public function rules()
    {
        return [
            [['parent_id', 'is_blacklist', 'id', 'member_level'], 'integer'],
            [['money'], 'number'],
            [['contact_way', 'remark'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent_id' => '上级id',
            'member_level' => '等级',
            'is_blacklist' => '是否黑名单',
            'contact_way' => '联系方式',
            'remark' => '备注',
            'money' => '佣金',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        /* @var User $form */
        $form = User::find()->alias('u')
            ->with(['share' => function ($query) {
                $query->where(['is_delete' => 0]);
            }])
            ->with('identity')
            ->with('userInfo')
            ->where(['u.id' => $this->id])
            ->one();

        if (!$form) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据为空'
            ];
        }
        $beforeParentId = $form->userInfo->parent_id;
        $form->userInfo->is_blacklist = $this->is_blacklist;
        $form->userInfo->parent_id = $this->parent_id;
        $form->userInfo->junior_at = mysql_timestamp();
        $form->userInfo->remark = $this->remark;
        $form->userInfo->contact_way = $this->contact_way;
        $form->identity->member_level = $this->member_level;


        $t = \Yii::$app->db->beginTransaction();
        if ($form->share && $form->identity->is_distributor == 1) {
            $diff = $this->money - $form->share->money;
            \Yii::$app->currency->setUser($form)->brokerage->add($diff, '后台修改分销佣金为：' . $this->money);
        }

        try {
            if (!$form->identity->save()) {
                throw new \Exception($this->getErrorMsg($form->identity));
            }

            if (!$form->userInfo->save()) {
                throw new \Exception($this->getErrorMsg($form->userInfo));
            }

            $t->commit();
            \Yii::$app->trigger(HandlerRegister::CHANGE_SHARE_MEMBER, new ShareMemberEvent([
                'mall' => \Yii::$app->mall,
                'beforeParentId' => $beforeParentId,
                'parentId' => $this->parent_id,
                'userId' => $form->id
            ]));
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $e) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }
}
