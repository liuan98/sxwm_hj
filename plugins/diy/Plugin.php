<?php
/**
 * @copyright (c)天幕网络
 * @author Lu Wei
 * @link http://www.67930603.top/
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/30 14:42
 */


namespace app\plugins\diy;


use app\forms\PickLinkForm;
use app\helpers\PluginHelper;
use app\plugins\diy\forms\mall\market\CommonTemplateCenter;
use app\plugins\diy\models\CoreTemplate;
use app\models\Model;
use app\plugins\diy\forms\common\CommonAlonePage;
use app\plugins\diy\forms\common\CommonPage;
use app\plugins\diy\models\DiyTemplate;

class Plugin extends \app\plugins\Plugin
{
    public function getMenus()
    {
        return [
            [
                'name' => '模板管理',
                'route' => 'plugin/diy/mall/template/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '模板编辑',
                        'route' => 'plugin/diy/mall/template/edit',
                    ],
                ]
            ],
            // [
                // 'name' => '模板市场',
                // 'route' => 'plugin/diy/mall/market/list',
                // 'icon' => 'el-icon-star-on',
            // ],
            [
                'name' => '自定义页面',
                'route' => 'plugin/diy/mall/page/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '自定义页面编辑',
                        'route' => 'plugin/diy/mall/page/edit',
                    ],
                ]
            ],
            [
                'name' => '授权页面',
                'route' => 'plugin/diy/mall/page/auth',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '表单提交信息',
                'route' => 'plugin/diy/mall/page/info',
                'icon' => 'el-icon-star-on',
            ],
        ];
    }

    public function handler()
    {
    }

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'diy';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return 'DIY装修';
    }

    public function getAppConfig()
    {
        $imageBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img';
        return [
            'app_image' => [
                'banner_image' => $imageBaseUrl . '/banner.jpg'
            ],
        ];
    }

    public function getIndexRoute()
    {
        return 'plugin/diy/mall/template/index';
    }

    /**
     * 插件小程序端链接
     * @return array
     */
    public function getPickLink()
    {
        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/images/pick-link';
        $pageList = CommonPage::getCommon(\Yii::$app->mall)->getPageList();
        $list = [];
        foreach ($pageList as $page) {
            $list[] = [
                'type' => 'diy',
                'name' => $page->title,
                'open_type' => '',
                'icon' => $iconBaseUrl . '/tpl.png',
                'value' => '/pages/index/index?page_id=' . $page->id,
                'ignore' => [PickLinkForm::IGNORE_TITLE],
            ];
        }
        return $list;
    }

    /**
     * @param string $type
     * @return array
     * 获取单页的配置例如授权页面(auth)
     */
    public function getAlonePage($type)
    {
        $commonAlonePage = CommonAlonePage::getCommon(\Yii::$app->mall);
        return $commonAlonePage->getPage($type);
    }

    /**
     * @param @pageId integer
     * @return array
     * @throws \Exception
     * 获取自定义页面
     */
    public function getPage($pageId = null)
    {
        $data = \Yii::$app->request->get();
        $common = CommonPage::getCommon(\Yii::$app->mall, $data['longitude'], $data['latitude']);
        return $common->getPage($pageId, true);
    }

    /**
     * @param $mallId
     * @return mixed
     * 暂时这么处理
     */
    public function getTemplate($mallId)
    {
        $search = \Yii::$app->request->get('search');
        $search = json_decode($search, true);
        $keyword = trim($search['keyword']);
        $list = DiyTemplate::find()->where([
            'mall_id' => $mallId,
            'is_delete' => 0
        ])->keyword($keyword, ['like', 'name', $keyword])
            ->page($pagination, 10)
            ->orderBy(['created_at' => SORT_DESC])
            ->select('id,name')
            ->all();
        return [
            'list' => $list,
            'pagination' => $pagination
        ];
    }

    /**
     * @param $mallId
     * @param $templateId
     * @return array|\yii\db\ActiveRecord|null|DiyTemplate
     */
    public function getTemplateOne($mallId, $templateId)
    {
        $list = DiyTemplate::find()->where([
            'mall_id' => $mallId,
            'is_delete' => 0,
            'id' => $templateId,
        ])->one();
        return $list;
    }

    /**
     * @param CoreTemplate $coreTemplate
     * @return array
     * @throws \Exception
     * 加载模板到diy模板
     */
    public function loadTemplate($coreTemplate)
    {
        $template = new DiyTemplate();
        $template->mall_id = \Yii::$app->mall->id;
        $template->name = $coreTemplate->name;
        $template->data = $coreTemplate->data;
        $template->is_delete = 0;
        if (!$template->save()) {
            throw new \Exception((new Model())->getErrorMsg($template));
        }
        return [
            'id' => $template->id
        ];
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     * 获取已安装的模板
     */
    public function getLocalList()
    {
        $list = CommonTemplateCenter::getInstance()
            ->getList();
        return $list;
    }

    /**
     * @param $idList
     * @return array|\yii\db\ActiveRecord[]
     * 获取已选中的已安装模板
     */
    public function getLocalListById($idList)
    {
        $list = CommonTemplateCenter::getInstance()
            ->getListById($idList);
        return $list;
    }

    /**
     * @param $params
     * @return array|\yii\db\ActiveRecord[]
     * @throws \app\core\cloud\CloudException
     * 获取模板市场列表
     */
    public function getMarketList($params)
    {
        $common = CommonTemplateCenter::getInstance();
        $common->page = isset($params['page']) ? $params['page'] : 1;
        $common->is_buy = isset($params['is_buy']) ? $params['is_buy'] : '';
        return $common->getCloudList();
    }

    /**
     * @param $idList
     * @return array|\yii\db\ActiveRecord[]
     * @throws \app\core\cloud\CloudException
     * 获取已选中的模板市场列表
     */
    public function getMarketListById($idList)
    {
        $common = CommonTemplateCenter::getInstance();
        return $common->getCloudListById($idList);
    }
}
