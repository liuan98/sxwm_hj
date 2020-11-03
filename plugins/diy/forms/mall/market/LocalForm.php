<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/11/15
 * Time: 17:23
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\plugins\diy\forms\mall\market;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\models\Banner;
use app\plugins\diy\models\CoreTemplate;
use app\models\HomeBlock;
use app\models\HomeNav;
use app\models\MallBannerRelation;
use app\models\Model;
use app\models\Option;
use app\plugins\diy\models\DiyTemplate;

class LocalForm extends Model
{
    public $keyword;
    public $page;
    public $template_id;

    public function rules()
    {
        return [
            [['keyword'], 'trim'],
            [['keyword'], 'string'],
            [['page', 'template_id'], 'integer'],
            [['page'], 'default', 'value' => 1]
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $list = CoreTemplate::find()->where([
            'is_delete' => 0,
        ])->keyword($this->keyword !== '', [
            'or',
            ['like', 'name', $this->keyword],
            ['like', 'author', $this->keyword],
        ])->page($pagination)->select('id,name,author,price,pics,type')->all();
        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        $hasDiy = false;
        if (in_array('diy', $permission)) {
            $hasDiy = true;
        }
        $common = CommonTemplateCenter::getInstance();
        $templatePermission = $common->getTemplatePermission();
        array_walk($list, function (&$item) use ($hasDiy, $templatePermission) {
            $item = $item->toArray();
            $pics = json_decode($item['pics'], true);
            $item['img'] = $pics[0];
            $item['is_use'] = $item['type'] == 'diy' ? $hasDiy : true;
            if (!$templatePermission['is_all'] && !in_array($item['id'], $templatePermission['list'])) {
                $item['is_use'] = false;
            }
        });
        unset($item);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
            ]
        ];
    }

    public function loadTemplate()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $coreTemplate = CoreTemplate::findOne(['template_id' => $this->template_id, 'is_delete' => 0]);
            if (!$coreTemplate) {
                throw new \Exception('未知模板');
            }
            switch ($coreTemplate->type) {
                case 'diy':
                    $template = new DiyTemplate();
                    $template->mall_id = \Yii::$app->mall->id;
                    $template->name = $coreTemplate->name;
                    $data = json_decode($coreTemplate->data, true);
                    $issue = new Issue();
                    $template->data = json_encode($issue->unsetList($data), JSON_UNESCAPED_UNICODE);
                    $template->is_delete = 0;
                    if (!$template->save()) {
                        throw new \Exception((new Model())->getErrorMsg($template));
                    }
                    $data = [
                        'id' => $template->id,
                    ];
                    break;
//                case 'home':
//                    $data = $this->saveHome($coreTemplate);
//                    break;
                default:
                    throw new \Exception('错误的模板信息，请刷新重试');
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => $data
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }

    /**
     * @param CoreTemplate $coreTemplate
     * @return mixed
     * @throws \Exception
     * 保存到diy
     */
    private function saveByDiy($coreTemplate)
    {
        try {
            $plugin = \Yii::$app->plugin->getPlugin('diy');
            if (!method_exists($plugin, 'loadTemplate')) {
                throw new \Exception('请更新diy插件');
            }
            return $plugin->loadTemplate($coreTemplate);
        } catch (\Exception $exception) {
            throw new \Exception('未安装diy插件，无法使用diy模板，请联系管理员');
        }
    }

    /**
     * @param CoreTemplate $coreTemplate
     * @return mixed
     * @throws \Exception
     * 保存到首页布局
     */
    private function saveHome($coreTemplate)
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            $list = json_decode($coreTemplate->data, true);
            $block = [];
            foreach ($list['model'] as $key => $item) {
                $arr = explode('-', $key);
                if (count($arr) < 2) {
                    continue;
                }
                $relation = $arr[0];
                $relation_id = $arr[1];
                switch ($relation) {
                    case 'banner':
                        foreach ($item as $value) {
                            $value['mall_id'] = \Yii::$app->mall->id;
                            $value['is_delete'] = 0;
                            $value['created_at'] = mysql_timestamp();
                            $model = new Banner();
                            $model->attributes = $value;
                            if (!$model->save()) {
                                throw new \Exception($this->getErrorMsg($model));
                            }
                            $mallBanner = new MallBannerRelation();
                            $mallBanner->mall_id = \Yii::$app->mall->id;
                            $mallBanner->banner_id = $model->id;
                            $mallBanner->is_delete = 0;
                            if (!$mallBanner->save()) {
                                throw new \Exception($this->getErrorMsg($mallBanner));
                            }
                        }
                        break;
                    case 'home_nav':
                        foreach ($item as $value) {
                            $value['mall_id'] = \Yii::$app->mall->id;
                            $value['is_delete'] = 0;
                            $value['created_at'] = mysql_timestamp();
                            $model = new HomeNav();
                            $model->attributes = $value;
                            if (!$model->save()) {
                                throw new \Exception($this->getErrorMsg($model));
                            }
                        }
                        break;
                    case 'block':
                        $model = new HomeBlock();
                        $model->mall_id = \Yii::$app->mall->id;
                        $model->name = $item['name'];
                        $model->value = json_encode($item['value'], JSON_UNESCAPED_UNICODE);
                        $model->type = $item['type'];
                        $model->is_delete = 0;
                        if (!$model->save()) {
                            throw new \Exception($this->getErrorMsg($model));
                        }
                        $block[$relation_id] = $model->id;
                        break;
                    default:
                        break;
                }
            }
            foreach ($list['list'] as &$item) {
                if ($item['key'] == 'block') {
                    $item['relation_id'] = $block[$item['relation_id']];
                }
            }
            unset($item);
            CommonOption::set(Option::NAME_HOME_PAGE, $list['list'], \Yii::$app->mall->id, Option::GROUP_APP);
            $t->commit();
            return [];
        } catch (\Exception $exception) {
            $t->rollBack();
            throw $exception;
        }
    }
}
