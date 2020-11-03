<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\diy\forms\common;


use app\models\Model;

class DiyBannerForm extends Model
{
    public function getNewBanner($data)
    {
        foreach ($data['banners'] as &$banner) {
            $banner['page_url'] = $banner['url'];
            $banner['open_type'] = $banner['openType'];
            $banner['pic_url'] = $banner['picUrl'];
        }
        unset($banner);

        return $data;
    }
}
