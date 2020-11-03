<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/25
 * Time: 16:49
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */
$pluginUrl = \app\helpers\PluginHelper::getPluginBaseAssetsUrl();
?>
<style>
    .diy-link {
        width: 100%;
        height: 72px;
    }

    .diy-link > div {
        width: 100%;
        height: 36px;
        background-repeat: no-repeat;
        background-size: contain;
    }

    .diy-link .title {
        flex-grow: 1;
        overflow-x: hidden;
        white-space: nowrap;
    }

    .diy-link .arrow {
        width: 12px;
        height: 22px;
        margin: 0 24px;
    }

    .diy-component-edit .link-page .el-input-group__append {
        background-color: #fff
    }
</style>
<template id="diy-link">
    <div>
        <div class="diy-component-preview">
            <div class="diy-link" :style="cStyle" flex="cross:center">
                <div :style="style" flex="dir:left cross:center">
                    <div class="title" :style="{marginLeft: data.textLeft + 'px'}">{{data.title}}</div>
                    <img class="arrow" src="<?= $pluginUrl ?>/images/icon-jiantou-r.png" v-if="data.arrowsSwitch">
                </div>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form label-width="100px" @submit.native.prevent>
                <el-form-item label="标题">
                    <el-input size="small" v-model="data.title"></el-input>
                </el-form-item>
                <el-form-item label="文本左边距">
                    <el-input size="small" v-model.number="data.textLeft" type="number">
                        <template slot="append">px</template>
                    </el-input>
                </el-form-item>
                <el-form-item label="链接页面">
                    <el-input size="small" class="link-page" v-model="data.link.name" :disabled="true">
                        <template slot="append">
                            <app-pick-link @selected="selectLink">
                                <el-button>选择链接</el-button>
                            </app-pick-link>
                        </template>
                    </el-input>
                </el-form-item>
                <el-form-item label="文字颜色">
                    <el-color-picker size="small" v-model="data.color"></el-color-picker>
                    <el-input size="small" style="width: 80px;margin-right: 25px;" v-model="data.color"></el-input>
                </el-form-item>
                <el-form-item label="背景颜色">
                    <el-color-picker size="small" v-model="data.background"></el-color-picker>
                    <el-input size="small" style="width: 80px;margin-right: 25px;" v-model="data.background"></el-input>
                </el-form-item>
                <el-form-item label="图标开关">
                    <el-switch v-model="data.picSwitch"></el-switch>
                </el-form-item>
                <template v-if="data.picSwitch">
                    <el-form-item label="图标">
                        <label slot="label">图标
                            <el-tooltip class="item" effect="dark"
                                        content="最大宽度750px，最大高度36px，图标等比例缩放"
                                        placement="top">
                                <i class="el-icon-info"></i>
                            </el-tooltip>
                        </label>
                        <app-attachment title="选择图标" :multiple="false" :max="1" type="image"
                                        @selected="pickPicUrl">
                            <el-button size="mini">选择图标</el-button>
                        </app-attachment>
                        <app-gallery :list="[{url:data.picUrl}]" :show-delete="false"
                                     @deleted="deletePic('picUrl')"></app-gallery>
                    </el-form-item>
                    <el-form-item label="图标左边距">
                        <el-input size="small" v-model.number="data.picLeft" type="number">
                            <template slot="append">px</template>
                        </el-input>
                    </el-form-item>
                </template>
                <el-form-item label="箭头开关">
                    <el-switch v-model="data.arrowsSwitch"></el-switch>
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>
<script>
    Vue.component('diy-link', {
        template: '#diy-link',
        props: {
            value: Object
        },
        data() {
            return {
                data: {
                    title: '',
                    textLeft: 0,
                    link: {},
                    picSwitch: true,
                    arrowsSwitch: true,
                    picUrl: '',
                    picLeft: 0,
                    color: '#353535',
                    background: '#ffffff'
                }
            }
        },
        created() {
            if (!this.value) {
                this.$emit('input', JSON.parse(JSON.stringify(this.data)))
            } else {
                this.data = JSON.parse(JSON.stringify(this.value));
                if (!this.data.link) {
                    this.data.link = {};
                }
            }
        },
        watch: {
            data: {
                deep: true,
                handler(newVal, oldVal) {
                    this.$emit('input', newVal, oldVal)
                },
            },
        },
        computed: {
            cStyle() {
                if(this.data.background) {
                    return `color: ${this.data.color};`
                        + `background: ${this.data.background};`
                }else {
                    return `color: ${this.data.color};`
                }
            },
            style() {
                if (this.data.picSwitch) {
                    return `background-image: url('${this.data.picUrl}');`
                        + `background-position: ${this.data.picLeft}px 0;`;
                } else {
                    return ``;
                }
            }
        },
        methods: {
            selectLink(e) {
                this.data.link = e[0];
            },
            pickPicUrl(e) {
                if (e) {
                    this.data.picUrl = e[0].url;
                }
            }
        }
    });
</script>
