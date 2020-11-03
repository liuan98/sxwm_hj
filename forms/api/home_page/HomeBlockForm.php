<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\forms\api\home_page;


use app\models\HomeBlock;
use app\models\Model;

class HomeBlockForm extends Model
{
    /**
     * 处理图片魔方
     * @param $blockIds
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getBlock($blockIds)
    {

        $blocks = HomeBlock::find()->where([
            'id' => $blockIds,
            'is_delete' => 0
        ])->asArray()->all();

        $newList = [];
        foreach ($blocks as $block) {
            $block['value'] = $other = \Yii::$app->serializer->decode($block['value']);

            // 样式一
            if (count($block['value']) == 1 && $block['type'] == 1) {
                $block['style'] = 360;
                $block['status'] = 0;
                $block['value'] = [
                    [
                        'width' => '100%',
                        'height' => 'auto',
                        'left' => 0,
                        'top' => 0,
                    ]
                ];
            }

            if (count($block['value']) == 2 && $block['type'] == 1) {
                $block['style'] = 360;
                $block['status'] = 1;
                $block['value'] = [
                    [
                        'width' => (300 * 100 / 750) . '%',
                        'height' => '100%',
                        'left' => 0,
                        'top' => 0
                    ],
                    [
                        'width' => (450 * 100 / 750) . '%',
                        'height' => '100%',
                        'left' => (300 * 100 / 750) . '%',
                        'top' => 0
                    ],
                ];
            }
            if (count($block['value']) == 3 && $block['type'] == 1) {
                $block['style'] = 360;
                $block['status'] = 2;
                $block['value'] = [
                    [
                        'width' => (300 * 100 / 750) . '%',
                        'height' => '100%',
                        'left' => 0,
                        'top' => 0
                    ],
                    [
                        'width' => (450 * 100 / 750) . '%',
                        'height' => '50%',
                        'left' => (300 * 100 / 750) . '%',
                        'top' => 0
                    ],
                    [
                        'width' => (450 * 100 / 750) . '%',
                        'height' => '50%',
                        'left' => (300 * 100 / 750) . '%',
                        'top' => '50%'
                    ],
                ];
            }
            if (count($block['value']) == 4 && $block['type'] == 1) {
                $block['style'] = 360;
                $block['status'] = 3;
                $block['value'] = [
                    [
                        'width' => (300 * 100 / 750) . '%',
                        'height' => '100%',
                        'left' => 0,
                        'top' => 0
                    ],
                    [
                        'width' => (450 * 100 / 750) . '%',
                        'height' => '50%',
                        'left' => (300 * 100 / 750) . '%',
                        'top' => 0
                    ],
                    [
                        'width' => (225 * 100 / 750) . '%',
                        'height' => '50%',
                        'left' => (300 * 100 / 750) . '%',
                        'top' => '50%'
                    ],
                    [
                        'width' => (225 * 100 / 750) . '%',
                        'height' => '50%',
                        'left' => (525 * 100 / 750) . '%',
                        'top' => '50%'
                    ],
                ];
            }

            // 样式二
            if (count($block['value']) == 2 && $block['type'] == 2) {
                $block['style'] = 240;
                $block['status'] = 4;
                $block['value'] = [
                    [
                        'width' => (375 * 100 / 750) . '%',
                        'height' => '100%',
                        'left' => 0,
                        'top' => 0
                    ],
                    [
                        'width' => (375 * 100 / 750) . '%',
                        'height' => '100%',
                        'left' => (375 * 100 / 750) . '%',
                        'top' => 0
                    ],
                ];
            }
            if (count($block['value']) == 3 && $block['type'] == 2) {
                $block['style'] = 240;
                $block['status'] = 5;
                $block['value'] = [
                    [
                        'width' => (250 * 100 / 750) . '%',
                        'height' => '100%',
                        'left' => 0,
                        'top' => 0
                    ],
                    [
                        'width' => (250 * 100 / 750) . '%',
                        'height' => '100%',
                        'left' => (250 * 100 / 750) . '%',
                        'top' => 0
                    ],
                    [
                        'width' => (250 * 100 / 750) . '%',
                        'height' => '100%',
                        'left' => (500 * 100 / 750) . '%',
                        'top' => 0
                    ],
                ];
            }
            if (count($block['value']) == 4 && $block['type'] == 2) {
                $block['style'] = 187.5;
                $block['status'] = 6;
                $block['value'] = [
                    [
                        'width' => '25%',
                        'height' => '100%',
                        'left' => 0,
                        'top' => 0
                    ],
                    [
                        'width' => '25%',
                        'height' => '100%',
                        'left' => '25%',
                        'top' => 0
                    ],
                    [
                        'width' => '25%',
                        'height' => '100%',
                        'left' => '50%',
                        'top' => 0
                    ],
                    [
                        'width' => '25%',
                        'height' => '100%',
                        'left' => '75%',
                        'top' => 0
                    ],
                ];
            }

            // 样式三
            if (count($block['value']) == 4 && $block['type'] == 3) {
                $block['style'] = 372;
                $block['status'] = 7;
                $block['value'] = [
                    [
                        'width' => (375 * 100 / 750) . '%',
                        'height' => '50%',
                        'left' => 0,
                        'top' => 0
                    ],
                    [
                        'width' => (375 * 100 / 750) . '%',
                        'height' => '50%',
                        'left' => (375 * 100 / 750) . '%',
                        'top' => 0
                    ],
                    [
                        'width' => (375 * 100 / 750) . '%',
                        'height' => '50%',
                        'left' => 0,
                        'top' => '50%',
                    ],
                    [
                        'width' => (375 * 100 / 750) . '%',
                        'height' => '50%',
                        'left' => (375 * 100 / 750) . '%',
                        'top' => '50%',
                    ],
                ];
            }
            foreach ($block['value'] as $key => $item) {
                if (isset($other[$key])) {
                    $block['value'][$key] = array_merge($block['value'][$key], $other[$key]);
                }
            }

            $newList[] = $block;
        }

        return $newList;
    }

    public function getNewBlocks($homePage, $blocks)
    {
        foreach ($blocks as $block) {
            if ($block['id'] == $homePage['relation_id']) {
                $homePage['block'] = $block;
                return $homePage;
            }
        }
        return $homePage;
    }
}
