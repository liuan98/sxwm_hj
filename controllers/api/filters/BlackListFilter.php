<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\controllers\api\filters;


use app\core\Plugin;
use app\core\response\ApiCode;
use app\models\UserInfo;
use yii\base\ActionFilter;

class BlackListFilter extends ActionFilter
{
    private $routeList = [
        'api/order/preview'
    ];

    public function beforeAction($action)
    {
        /** @var UserInfo $userInfo */
        $userInfo = UserInfo::findOne(['user_id' => \Yii::$app->user->id]);
        if ($userInfo && $userInfo->is_blacklist) {
            $plugins = \Yii::$app->plugin->list;
            foreach ($plugins as $plugin) {
                $PluginClass = 'app\\plugins\\' . $plugin->name . '\\Plugin';
                /** @var Plugin $pluginObject */
                if (!class_exists($PluginClass)) {
                    continue;
                }
                $object = new $PluginClass();
                if (method_exists($object, 'getBlackList')) {
                    $routeList = array_merge($this->routeList, $object->getBlackList());
                    $this->routeList = $routeList;
                }
            }

            // 黑名单用户无法访问相关路由
            if (in_array(\Yii::$app->requestedRoute, $this->routeList)) {
                \Yii::$app->response->data = [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '您已被限制该操作',
                ];
                return false;
            }
        }

        return true;
    }
}
