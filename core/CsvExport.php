<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\core;


use yii\base\Component;

class CsvExport extends Component
{
    /**
     * 导出excel(csv)
     * @data 导出数据
     * @headlist 第一行,列名
     * @fileName 输出Excel文件名
     */
    public function export($data = array(), $headlist = array(), $fileName)
    {
        try {
            $fileName = urlencode($fileName);

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename*=utf-8' . "'zh_cn'" . iconv('utf-8', 'gbk', $fileName) . '.csv');
            header('Cache-Control: max-age=0');

            //打开PHP文件句柄,php://output 表示直接输出到浏览器
            $fp = fopen('php://output', 'a');

            //输出Excel列名信息
            foreach ($headlist as $key => $value) {
                //CSV的Excel支持GBK编码，一定要转换，否则乱码
                $headlist[$key] = iconv('utf-8', 'gbk', $value);
            }

            //将数据通过fputcsv写到文件句柄
            fputcsv($fp, $headlist);

            //计数器
            $num = 0;

            //每隔$limit行，刷新一下输出buffer，不要太大，也不要太小
            $limit = 100000;

            //逐行取出数据，不浪费内存
            $count = count($data);
            for ($i = 0; $i < $count; $i++) {
                $num++;

                //刷新一下输出buffer，防止由于数据过多造成问题
                if ($limit == $num) {
                    ob_flush();
                    flush();
                    $num = 0;
                }

                $row = $this->handleRowData($data[$i]);
                fputcsv($fp, $row);
            }

            exit();
        } catch (\Exception $exception) {
            dd($exception);
        }
    }

    /**
     * 同时导出多个csv文件时，需要弄成压缩包一起下载
     * @data 导出数据
     * @headlist 第一行,列名
     */
    public function exportMultiple($data = array(), $headlist = array())
    {
        try {
            // create a temporary file
            $fp = fopen('php://temp/maxmemory:1048576', 'w');
            if (false === $fp) {
                die('Failed to create temporary file');
            }

            //输出Excel列名信息
            foreach ($headlist as $key => $value) {
                //CSV的Excel支持GBK编码，一定要转换，否则乱码
                $headlist[$key] = iconv('utf-8', 'gbk', $value);
            }

            //将数据通过fputcsv写到文件句柄
            fputcsv($fp, $headlist);

            //计数器
            $num = 0;

            //每隔$limit行，刷新一下输出buffer，不要太大，也不要太小
            $limit = 100000;

            //逐行取出数据，不浪费内存
            $count = count($data);
            for ($i = 0; $i < $count; $i++) {
                $num++;

                //刷新一下输出buffer，防止由于数据过多造成问题
                if ($limit == $num) {
                    ob_flush();
                    flush();
                    $num = 0;
                }

                $row = $this->handleRowData($data[$i]);
                fputcsv($fp, $row);
            }

            rewind($fp);
            return $fp;
        } catch (\Exception $exception) {
            dd($exception);
        }
    }

    /**
     * 异步处理超大csv数据
     * @data 导出数据
     * @headlist 第一行,列名
     * @fileName 输出Excel文件名
     */
    public function ajaxExport($data = array(), $headlist = array(), $fileName)
    {
        try {
            $fileName = $fileName . '.csv';
            $newFilePath = \Yii::$app->basePath . '/web/temp/goods/';

            if (!file_exists($newFilePath . $fileName)) {
                if (!is_dir($newFilePath)) {
                    mkdir($newFilePath, 0777, true);
                }
                $fp = fopen($newFilePath . $fileName, 'a+');
                //输出Excel列名信息
                foreach ($headlist as $key => $value) {
                    //CSV的Excel支持GBK编码，一定要转换，否则乱码
                    $headlist[$key] = iconv('utf-8', 'gbk', $value);
                }

                //将数据通过fputcsv写到文件句柄
                fputcsv($fp, $headlist);
            }

            $fp = fopen($newFilePath . $fileName, 'a+');

            //计数器
            $num = 0;

            //每隔$limit行，刷新一下输出buffer，不要太大，也不要太小
            $limit = 100000;

            //逐行取出数据，不浪费内存
            $count = count($data);
            for ($i = 0; $i < $count; $i++) {
                $num++;

                //刷新一下输出buffer，防止由于数据过多造成问题
                if ($limit == $num) {
                    ob_flush();
                    flush();
                    $num = 0;
                }

                $row = $this->handleRowData($data[$i]);
                fputcsv($fp, $row);
            }

            return $fileName;
        } catch (\Exception $exception) {
            dd($exception);
        }
    }

    //判断是否含有英文逗号，英文引号
    public function check($value)
    {
        if (!$this->checkJson($value)) {
            $isInt = is_int($value) ? true : false;
            $isFloat = is_float($value) ? true : false;

            $value = rtrim($value, "\r\n");
            $value = str_replace(",", "，", $value);

            $value = is_string($value) ? $value . "\t" : $value;
            $value = $isInt ? intval($value) : $value;
            $value = $isFloat ? floatval($value) : $value;
        }
        return $value;
    }

    public function handleRowData($data)
    {
        $row = $data;
        foreach ($row as $key => $value) {
            $value = $this->check($value);
            $row[$key] = mb_convert_encoding($value, 'GBK', 'UTF-8');
        }
        return $row;
    }

    // 判断是否为json数据
    private function checkJson($value) {
        $data = json_decode($value);
        if ($data && (is_object($data)) || is_array($data)) {
            return true;
        }

        return false;
    }
}
