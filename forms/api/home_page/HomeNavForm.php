<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\forms\api\home_page;


use app\forms\api\app_platform\Transform;
use app\models\HomeNav;
use app\models\Model;

class HomeNavForm extends Model
{
    /**
     * @return mixed
     */
    public function getHomeNav()
    {
        $homeNavs = HomeNav::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'status' => 1,
            'is_delete' => 0,
        ])->orderBy(['sort' => SORT_ASC])->asArray()->all();
        //小程序管理入口权限
        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        $app_admin = true;
        if (empty(\Yii::$app->plugin->getInstalledPlugin('app_admin')) || !in_array('app_admin', $permission) || empty(\Yii::$app->user->identity->identity->is_admin) || \Yii::$app->user->identity->identity->is_admin != 1) {
            $app_admin = false;
        }
        $is_live = true;
        if (!in_array('live', $permission)) {
            $is_live = false;
        }
        $newData = [];
        foreach ($homeNavs as $homeNav) {
            if ($homeNav['open_type'] == 'app_admin' && !$app_admin) {
                continue;
            }

            $check = strpos($homeNav['url'], 'wx2b03c6e691cd7370') !== false;
            if (($homeNav['url'] == '/pages/live/index' || $check) && !$is_live) {
                continue;
            }
            $arr = [
                'id' => $homeNav['id'],
                'icon_url' => $homeNav['icon_url'],
                'link_url' => $homeNav['url'],
                'name' => $homeNav['name'],
                'open_type' => $homeNav['open_type'],
                'params' => $homeNav['params'] ? json_decode($homeNav['params'], true) : [],
            ];
            $newData[] = $arr;
        }
        $newData = Transform::getInstance()->transformHomeNav($newData);

        return $newData;
    }
}
