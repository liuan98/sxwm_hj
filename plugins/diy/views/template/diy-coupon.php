<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/25
 * Time: 20:09
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */
$pluginUrl = \app\helpers\PluginHelper::getPluginBaseAssetsUrl();
$mallUrl = Yii::$app->request->hostInfo
    . Yii::$app->request->baseUrl
    . '/statics/img/app';
?>
<style>
    .diy-coupon {
        width: 100%;
        padding: 16px;
        min-height: 150px;
        overflow-x: auto;
        padding-left: 24px;
    }

    .diy-coupon .diy-coupon-one {
        width: 256px;
        height: 130px;
        background-repeat: no-repeat;
        background-size: cover;
        background-position: center;
        margin-right: 24px;
        flex: none;
    }

    .diy-coupon .diy-coupon-one .right {
        width: 1.6rem;
        font-size: 26px;
        line-height: 1.25;
        text-align: center;
        margin-right: 2px;
    }
</style>
<template id="diy-coupon">
    <div>
        <div class="diy-component-preview">
            <div class="diy-coupon" flex="dir:left" :style="cListStyle">
                <div class="diy-coupon-one" flex="dir:left" :style="cStyle1" v-for="item in 2">
                    <div style="text-align: center;width: 215px">
                        <div style="height: 80px;line-height: 80px;font-size: 28px">￥1000</div>
                        <div style="height: 50px;line-height: 50px;font-size: 24px">满200元可用</div>
                    </div>
                    <div class="right" flex="main:center cross:center">立即领取</div>
                </div>
                <div class="diy-coupon-one" flex="dir:left" :style="cStyle2" v-for="item in 2">
                    <div style="text-align: center;width: 215px">
                        <div style="height: 80px;line-height: 80px;font-size: 28px">￥1000</div>
                        <div style="height: 50px;line-height: 50px;font-size: 24px">满200元可用</div>
                    </div>
                    <div class="right" flex="main:center cross:center">已领取</div>
                </div>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form label-width="100px" @submit.native.prevent>
                <el-form-item label="字体颜色">
                    <el-color-picker size="small" v-model="data.textColor"></el-color-picker>
                </el-form-item>
                <el-form-item label="不可领取">
                    <app-attachment title="选择图片" :multiple="false" :max="1" type="image"
                                    v-model="data.receiveBg">
                        <el-tooltip class="item" effect="dark"
                                    content="建议尺寸256*130"
                                    placement="top">
                            <el-button size="mini">选择图片</el-button>
                        </el-tooltip>
                    </app-attachment>
                    <app-gallery :url="data.receiveBg" :show-delete="true"
                                 @deleted="deletePic('receiveBg')"></app-gallery>
                </el-form-item>
                <el-form-item label="可领取">
                    <app-attachment title="选择图片" :multiple="false" :max="1" type="image"
                                    v-model="data.unclaimedBg">
                        <el-tooltip class="item" effect="dark"
                                    content="建议尺寸256*130"
                                    placement="top">
                            <el-button size="mini">选择图片</el-button>
                        </el-tooltip>
                    </app-attachment>
                    <app-gallery :url="data.unclaimedBg" :show-delete="true"
                                 @deleted="deletePic('unclaimedBg')"></app-gallery>
                </el-form-item>
                <diy-bg :data="data" @update="updateData" @toggle="toggleData" @change="changeData"></diy-bg>
            </el-form>
        </div>
    </div>
</template>
<script>
    Vue.component('diy-coupon', {
        template: '#diy-coupon',
        props: {
            value: Object,
        },
        data() {
            return {
                data: {
                    textColor: '#ffffff',
                    receiveBg: '<?= $mallUrl?>/coupon/icon-coupon-no.png',
                    unclaimedBg: '<?= $mallUrl?>/coupon/icon-coupon-index.png',
                    showImg: false,
                    backgroundColor: '#fff',
                    backgroundPicUrl: '',
                    position: 5,
                    mode: 1,
                    backgroundHeight: 100,
                    backgroundWidth: 100,
                },
                position: 'center center',
                repeat: 'no-repeat',
                defaultData: {}
            };
        },
        created() {
            let data = JSON.parse(JSON.stringify(this.data));
            this.defaultData = data;
            if (!this.value) {
                this.$emit('input', data)
            } else {
                this.data = JSON.parse(JSON.stringify(this.value));
            }
        },
        computed: {
            cListStyle() {
                if(this.data.backgroundColor) {
                    return `background-color:${this.data.backgroundColor};background-image:url(${this.data.backgroundPicUrl});background-size:${this.data.backgroundWidth}% ${this.data.backgroundHeight}%;background-repeat:${this.repeat};background-position:${this.position}`
                }else {
                    return `background-image:url(${this.data.backgroundPicUrl});background-size:${this.data.backgroundWidth}% ${this.data.backgroundHeight}%;background-repeat:${this.repeat};background-position:${this.position}`
                }
            },
            cStyle1() {
                return `background-image: url('${this.data.unclaimedBg}');`
                    + `color: ${this.data.textColor}`;
            },
            cStyle2() {
                return `background-image: url('${this.data.receiveBg}');`
                    + `color: ${this.data.textColor}`;
            },
        },
        watch: {
            data: {
                deep: true,
                handler(newVal, oldVal) {
                    this.$emit('input', newVal, oldVal)
                },
            }
        },
        methods: {
            updateData(e) {
                this.data = e;
            },
            toggleData(e) {
                this.position = e;
            },
            changeData(e) {
                this.repeat = e;
            },
            deletePic(param) {
                this.data[param] = this.defaultData[param]
            }
        }
    });
</script>
