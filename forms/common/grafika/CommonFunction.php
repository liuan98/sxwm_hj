<?php

namespace app\forms\common\grafika;

use GuzzleHttp\Client;

class CommonFunction
{
    use CustomizeFunction;
    public static function setName($text)
    {
        if (mb_strlen($text, 'UTF-8') > 8) {
            $text = mb_substr($text, 0, 8, 'UTF-8') . '...';
        }
        return $text;
    }

    /**
     * @param integer $fontsize 字体大小
     * @param integer $angle 角度
     * @param string $fontface 字体名称
     * @param string $string 字符串
     * @param integer $width 预设宽度
     */
    public static function autowrap($fontsize, $angle, $fontface, $string, $width, $max_line = null)
    {
        $content = "";
        // 将字符串拆分成一个个单字 保存到数组 letter 中
        $letter = [];
        for ($i = 0; $i < mb_strlen($string, 'UTF-8'); $i++) {
            $letter[] = mb_substr($string, $i, 1, 'UTF-8');
        }
        $line_count = 0;
        foreach ($letter as $l) {
            $teststr = $content . " " . $l;
            $testbox = imagettfbbox($fontsize, $angle, $fontface, $teststr);
            // 判断拼接后的字符串是否超过预设的宽度
            if (($testbox[2] > $width) && ($content !== "")) {
                $line_count++;
                if ($max_line && $line_count >= $max_line) {
                    $content = mb_substr($content, 0, -1, 'UTF-8') . "...";
                    break;
                }
                $content .= "\n";
            }
            $content .= $l;
        }

        return $content;
    }

    //获取网络图片到临时目录
    public static function saveTempImage($url, $default_url = '')
    {
        $url = trim($url) ?: trim($default_url);
        $wdcp_patch = false;
        $wdcp_patch_file = \Yii::$app->basePath . '/patch/wdcp.json';
        if (file_exists($wdcp_patch_file)) {
            $wdcp_patch = json_decode(file_get_contents($wdcp_patch_file), true);
            if ($wdcp_patch && in_array(\Yii::$app->request->hostName, $wdcp_patch)) {
                $wdcp_patch = true;
            } else {
                $wdcp_patch = false;
            }
        }
        if ($wdcp_patch) {
            $url = str_replace('http://', 'https://', $url);
        }

        if (!is_dir(\Yii::$app->runtimePath . '/image')) {
            mkdir(\Yii::$app->runtimePath . '/image');
        }
        $save_path = \Yii::$app->runtimePath . '/image/' . md5($url) . '.jpg';

        $client = new Client(['verify' => false]);
        $response = $client->get($url, ['save_to' => $save_path]);
        if($response->getStatusCode() == 200) {
            return $save_path;
        } else {
            throw new \Exception('保存失败');
        }
    }

    //第一步生成圆角图片
    public static function avatar($url, $path = './', $w, $h)
    {
        list($w,$h) = getimagesize($url);
        $original_path = $url;
        $dest_path = $path . uniqid('r', true) . '.png';
        $src = imagecreatefromstring(file_get_contents($original_path));
        $newpic = imagecreatetruecolor($w, $h);
        imagealphablending($newpic, false);
        $transparent = imagecolorallocatealpha($newpic, 0, 0, 0, 127);
        $r = $w / 2;
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $c = imagecolorat($src, $x, $y);
                $_x = $x - $w / 2;
                $_y = $y - $h / 2;
                if ((($_x * $_x) + ($_y * $_y)) < ($r * $r)) {
                    imagesetpixel($newpic, $x, $y, $c);
                } else {
                    imagesetpixel($newpic, $x, $y, $transparent);
                }
            }
        }

        imagesavealpha($newpic, true); //d
        imagepng($newpic, $dest_path); //d
        imagedestroy($newpic);
        imagedestroy($src);
        //header('Content-Type: image/png');
        unlink($url);
        return $dest_path;
    }
}