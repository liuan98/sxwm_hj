<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/24
 * Time: 19:29
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\plugins\diy\forms\mall;


use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\diy\forms\common\CommonTemplate;
use app\plugins\diy\models\DiyTemplate;

class TemplateEditForm extends Model
{
    public $name;
    public $data;
    public $id;

    public function rules()
    {
        return [
            [['name', 'data'], 'required'],
            [['id'], 'integer']
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '模板名称',
            'data' => '模板内容'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $common = CommonTemplate::getCommon();
        $template = $common->getTemplate($this->id);
        if (!$template) {
            $template = new DiyTemplate();
            $template->is_delete = 0;
            $template->mall_id = \Yii::$app->mall->id;
        }
        $template->name = $this->name;
        $template->data = $this->data;

        if (!$template->save()) {
            return $this->getErrorMsg($template);
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '保存成功',
            'data' => [
                'id' => $template->id
            ]
        ];
    }
}
