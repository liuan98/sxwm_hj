<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\forms\api\home_page;

use app\models\Banner;
use app\models\MallBannerRelation;
use app\models\Model;

class HomeBannerForm extends Model
{
    public function getBanners()
    {
        $bannerIds = Banner::find()->where(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])->select('id');

        $query = MallBannerRelation::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);

        $banners = $query->andWhere(['banner_id' => $bannerIds])
            ->with('banner')
            ->orderBy('id ASC')
            ->asArray()
            ->all();

        $banners = array_map(function ($item) {
            return $item['banner'];
        }, $banners);

        $newData = [];
        foreach ($banners as $banner) {
            $arr = [
                'id' => $banner['id'],
                'title' => $banner['title'],
                'params' => $banner['params'] ? json_decode($banner['params'], true) : '',
                'open_type' => $banner['open_type'],
                'pic_url' => $banner['pic_url'],
                'page_url' => $banner['page_url'],
            ];
            $newData[] = $arr;
        }

        return $newData;
    }
}
