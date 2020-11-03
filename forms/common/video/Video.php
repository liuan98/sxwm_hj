<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/26
 * Time: 14:50
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\forms\common\video;


class Video
{
    public static function getUrl($url)
    {
        $url = trim($url);
        if (strpos($url, 'v.qq.com') != -1) {
            $model = new TxVideo();
            return $model->getVideoUrl($url);
        } else {
            return $url;
        }
    }
}
