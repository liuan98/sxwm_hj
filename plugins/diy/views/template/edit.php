<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/23
 * Time: 9:53
 */
$_currentPluginBaseUrl = \app\helpers\PluginHelper::getPluginBaseAssetsUrl(Yii::$app->plugin->currentPlugin->getName());
Yii::$app->loadViewComponent('diy/diy-bg');
?>
<script>
    const _currentPluginBaseUrl = '<?=$_currentPluginBaseUrl?>';
</script>
<?php
$diyPath = \Yii::$app->viewPath . '/components/diy';
$currentDir = opendir($diyPath);
$mallComponents = [];
while (($file = readdir($currentDir)) !== false) {
    if (stripos($file, 'diy-') === 0) {
        $mallComponents[] = substr($file, 4, (stripos($file, '.php') - 4));
    }
}
closedir($currentDir);
foreach ($mallComponents as $component) {
    Yii::$app->loadViewComponent("diy-{$component}", $diyPath);
}
$currentDir = opendir(__DIR__);
$diyComponents = [];
while (($file = readdir($currentDir)) !== false) {
    if (stripos($file, 'diy-') === 0) {
        $temp = substr($file, 4, (stripos($file, '.php') - 4));
        if (!in_array($temp, $mallComponents)) {
            $diyComponents[] = $temp;
        }
    }
}
closedir($currentDir);
foreach ($diyComponents as $component) {
    Yii::$app->loadViewComponent("diy-{$component}", __DIR__);
}
$components = array_merge($diyComponents, $mallComponents);
Yii::$app->loadViewComponent('app-hotspot');
Yii::$app->loadViewComponent('app-rich-text');
Yii::$app->loadViewComponent('app-radio');
?>
<style>
    .all-components {
        background: #fff;
        padding: 20px;
    }

    .all-components .component-group {
        border: 1px solid #eeeeee;
        width: 300px;
        margin-bottom: 20px;
    }

    .all-components .component-group:last-child {
        margin-bottom: 0;
    }

    .all-components .component-group-name {
        height: 35px;
        line-height: 35px;
        background: #f7f7f7;
        padding: 0 20px;
        border-bottom: 1px solid #eeeeee;
    }

    .all-components .component-list {
        margin-right: -2px;
        margin-top: -2px;
        flex-wrap: wrap;
    }

    .all-components .component-list .component-item {
        width: 100px;
        height: 100px;
        border: 0 solid #eeeeee;
        border-width: 0 1px 1px 0;
        text-align: center;
        padding: 15px 0 0;
        cursor: pointer;
    }

    .all-components .component-list .component-icon {
        width: 40px;
        height: 40px;
        /*border: 1px solid #eee;*/
    }

    .all-components .component-list .component-name {

    }

    .mobile-framework {
        width: 375px;
        height: 100%;
    }

    .mobile-framework-header {
        height: 60px;
        line-height: 60px;
        background: #333;
        color: #fff;
        text-align: center;
        background: url('statics/img/mall/head.png') no-repeat;
    }

    .mobile-framework-body {
        min-height: 645px;
        border: 1px solid #e2e2e2;
        /* background: #f5f7f9; */
    }

    .mobile-framework .diy-component-preview {
        cursor: pointer;
        position: relative;
        zoom: 0.5;
        -moz-transform:scale(0.5);
        -moz-transform-origin:top left;
        font-size: 28px;
    }

    @-moz-document url-prefix(){
        .mobile-framework .diy-component-preview {
            cursor: pointer;
            position: relative;
            -moz-transform:scale(0.5);
            -moz-transform-origin:top left;
            font-size: 28px;
            width: 200% !important;
            height:100%;
            margin-bottom:auto;
        }
        .mobile-framework .active .diy-component-preview {
            border: 2px dashed #409EFF;
            left: -2px;
            right: -2px;
            width: calc(200% + 4px) !important;
        }
    }

    .mobile-framework .diy-component-preview:hover {
        box-shadow: inset 0 0 10000px rgba(0, 0, 0, .03);
    }

    .mobile-framework .diy-component-edit {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 465px;
        right: 0;
        background: #fff;
        padding: 20px;
        display: none;
        overflow: auto;
    }

    .diy-component-options {
        position: relative;
    }

    .diy-component-options .el-button {
        height: 25px;
        line-height: 25px;
        width: 25px;
        padding: 0;
        text-align: center;
        border: none;
        border-radius: 0;
        position: absolute;
        margin-left: 0;
    }

    .mobile-framework .active .diy-component-preview {
        border: 2px dashed #409EFF;
        left: -2px;
        right: -2px;
        width: calc(100% + 4px);
    }

    .mobile-framework .active .diy-component-edit {
        display: block;
        padding-right: 20%;
        min-width: 650px;
    }

    .all-components {
        max-height: 725px;
        overflow-y: auto;
    }

    .bottom-menu {
        text-align: center;
        height: 54px;
        width: 100%;
    }

    .bottom-menu .el-card__body {
        padding-top: 10px;
    }

    .el-dialog {
        min-width: 800px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="margin-bottom: 10px">
        <el-breadcrumb separator="/">
            <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'plugin/diy/mall/template/index'})">模版管理</span></el-breadcrumb-item>
            <el-breadcrumb-item v-if="id > 0">详情</el-breadcrumb-item>
            <el-breadcrumb-item v-else>新增</el-breadcrumb-item>
        </el-breadcrumb>
    </el-card>
    <div v-loading="loading">
        <div flex="box:first" style="margin-bottom: 10px;min-width: 1280px;height: 725px;">
            <div class="all-components">
                <el-form @submit.native.prevent label-width="80px">
                    <el-form-item label="模板名称">
                        <el-input size="small" v-model="templateName"></el-input>
                    </el-form-item>
                </el-form>
                <el-form label-width="80px">
                    <el-form-item label="背景设置">
                        <el-button size="small" @click="openBgSetting">设置</el-button>
                    </el-form-item>
                </el-form>
                <div class="component-group" v-for="group in allComponents">
                    <div class="component-group-name">{{group.groupName}}</div>
                    <div class="component-list" flex="">
                        <template v-for="item in group.list">
                            <div class="component-item" @click="selectComponent(item)">
                                <img class="component-icon" :src="item.icon">
                                <div class="component-name">{{item.name}}</div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            <div style="padding-left: 2px;position: relative;overflow-y: auto">
                <div style="overflow-y: auto;padding: 0 25px;width: 435px;height: 705px;">
                    <div class="mobile-framework" style="height: 705px;">
                        <div class="mobile-framework-header"></div>
                        <div class="mobile-framework-body" :style="'background-color:'+ bg.backgroundColor+';background-image:url('+bg.backgroundPicUrl+');background-size:'+bg.backgroundWidth+'% '+bg.backgroundHeight+'%;background-repeat:'+repeat+';background-position:'+position">
                            <draggable v-model="components" :options="{filter:'.active',preventOnFilter:false}" v-if="components && components.length">
                                <div v-for="(component, index) in components" :key="component.key"
                                     @click="showComponentEdit(component,index)"
                                     :class="(component.active?'active':'')">
                                    <div class="diy-component-options" v-if="component.active">
                                        <el-button type="primary"
                                                   icon="el-icon-delete"
                                                   @click.stop="deleteComponent(index)"
                                                   style="left: -25px;top:0;"></el-button>
                                        <el-button type="primary"
                                                   icon="el-icon-document-copy"
                                                   @click.stop="copyComponent(index)"
                                                   style="left: -25px;top:30px;"></el-button>
                                        <el-button v-if="index > 0 && components.length > 1"
                                                   type="primary"
                                                   icon="el-icon-arrow-up"
                                                   @click.stop="moveUpComponent(index)"
                                                   style="right: -25px;top:0;"></el-button>
                                        <el-button v-if="components.length > 1 && index < components.length-1"
                                                   type="primary"
                                                   icon="el-icon-arrow-down"
                                                   @click.stop="moveDownComponent(index)"
                                                   style="right: -25px;top:30px;"></el-button>
                                    </div>
                                    <?php foreach ($components as $component) : ?>
                                        <diy-<?= $component ?> v-if="component.id === '<?= $component ?>'"
                                                               :active="component.active"
                                                               v-model="component.data"></diy-<?= $component ?>>
                                    <?php endforeach; ?>
                                </div>
                            </draggable>
                            <div v-else flex="main:center cross:center"
                                 style="height: 200px;color: #adb1b8;text-align: center;">
                                <div>
                                    <i class="el-icon-folder-opened" style="font-size: 32px;margin-bottom: 10px"></i>
                                    <div>空空如也</div>
                                    <div>请从左侧组件库添加组件</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <el-dialog title="背景设置" :visible.sync="bgVisible">
        <el-form @submit.native.prevent label-width="100px">
            <diy-bg :data="bgSetting" :background="bgVisible" :hr="!bgVisible" @update="updateData" @toggle="toggleData" @change="changeData"></diy-bg>
        </el-form>
        <div slot="footer">
            <el-button size="small" @click="bgVisible = false">取消</el-button>
            <el-button size="small" @click="beSettingBg" type="primary">确定</el-button>
        </div>
    </el-dialog>
    <el-card class="bottom-menu" shadow="never">
        <div>
            <el-button size="small" @click="save(false)" type="primary" :loading="submitLoading">保存</el-button>
            <el-button size="small" @click="saveAs" :loading="submitLoading">另存为</el-button>
        </div>
    </el-card>
</div>
<script src="//cdnjs.cloudflare.com/ajax/libs/Vue.Draggable/2.18.1/vuedraggable.umd.min.js"></script>
<script>
    new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                bgVisible: false,
                allComponents: [],
                id: null,
                templateName: '',
                components: [],
                submitLoading: false,
                model: '',
                bg: {
                    showImg: false,
                    backgroundColor: '#f5f7f9',
                    backgroundPicUrl: '',
                    position: 5,
                    mode: 1,
                    backgroundHeight: 100,
                    backgroundWidth: 100,
                },
                bgSetting: {
                    showImg: false,
                    backgroundColor: '#f5f7f9',
                    backgroundPicUrl: '',
                    position: 5,
                    mode: 1,
                    backgroundHeight: 100,
                    backgroundWidth: 100,
                    positionText: 'center center',
                    repeatText: 'no-repeat',
                },
                position: 'center center',
                repeat: 'no-repeat',
                overrun: null
            };
        },
        created() {
            this.id = getQuery('id');
            this.model = getQuery('model');
            this.loadData();
        },
        methods: {
            beSettingBg() {
                this.bg = JSON.parse(JSON.stringify(this.bgSetting));
                this.position = this.bgSetting.positionText;
                this.repeat = this.bgSetting.repeatText;
                this.bgVisible = false;
            },
            openBgSetting() {
                this.bgSetting = JSON.parse(JSON.stringify(this.bg));
                this.bgSetting.positionText = this.position;
                this.bgSetting.repeatText = this.repeat;
                this.bgVisible = true;
            },
            updateData(e) {
                this.bgSetting = e;
            },
            toggleData(e) {
                this.bgSetting.positionText = e;
            },
            changeData(e) {
                this.bgSetting.repeatText = e;
            },
            loadData() {
                this.loading = true;
                this.$request({
                    params: {
                        r: 'plugin/diy/mall/template/edit',
                        id: this.id,
                    }
                }).then(response => {
                    this.loading = false;
                    if (response.data.code === 0) {
                        this.allComponents = response.data.data.allComponents;
                        this.overrun = response.data.data.overrun;
                        this.templateName = response.data.data.name;
                        const components = JSON.parse(response.data.data.data);
                        console.log(components)
                        for (let i in components) {
                            components[i].active = false;
                            components[i].key = randomString();
                            console.log(components[i])
                            if(components[i].id == 'background') {
                                this.bg = components[i].data;
                                this.bgSetting = components[i].data;
                                this.position = components[i].data.positionText;
                                this.repeat = components[i].data.repeatText;
                            } 
                        }
                        this.components = components;
                    } else {
                    }
                }).catch(e => {
                });
            },
            selectComponent(e) {
                if (this.overrun && !this.overrun.is_diy_module_overrun
                    && this.components.length >= this.overrun.diy_module_overrun) {
                    this.$message.error('最多添加' + this.overrun.diy_module_overrun + '个组件');
                    return ;
                }
                if (e.single) {
                    for (let i in this.components) {
                        if (this.components[i].id === e.id) {
                            this.$message.error('该组件只允许添加一个。');
                            return;
                        }
                    }
                }
                let currentIndex = this.components.length;
                for (let i in this.components) {
                    if (this.components[i].active) {
                        currentIndex = i + 1;
                        break;
                    }
                }
                const component = {
                    id: e.id,
                    data: null,
                    active: false,
                    key: randomString(),
                };
                this.components.splice(currentIndex, 0, component);
            },
            showComponentEdit(component, index) {
                for (let i in this.components) {
                    if (i == index) {
                        this.components[i].active = true;
                    } else {
                        this.components[i].active = false;
                    }
                }
            },
            deleteComponent(index) {
                this.components.splice(index, 1);
            },
            copyComponent(index) {
                if (this.overrun && !this.overrun.is_diy_module_overrun
                    && this.components.length >= this.overrun.diy_module_overrun) {
                    this.$message.error('最多添加' + this.overrun.diy_module_overrun + '个组件');
                    return ;
                }
                for (let i in this.allComponents) {
                    for (let j in this.allComponents[i].list) {

                        if (this.allComponents[i].list[j].id === this.components[index].id) {
                            if (this.allComponents[i].list[j].single) {
                                this.$message.error('该组件只允许添加一个。');
                                return;
                            }
                        }
                    }
                }
                let json = JSON.stringify(this.components[index]);
                let copy = JSON.parse(json);
                copy.active = false;
                copy.key = randomString();
                this.components.splice(index + 1, 0, copy);
            },
            moveUpComponent(index) {
                this.swapComponents(index, index - 1);
            },
            moveDownComponent(index) {
                this.swapComponents(index, index + 1);
            },
            swapComponents(index1, index2) {
                this.components[index2] = this.components.splice(index1, 1, this.components[index2])[0];
            },
            save(isSaveAs, saveAsName) {
                this.submitLoading = true;
                if(this.components.filter(item => item['id']==='background').length !== 0) {
                    for(let i in this.components) {
                        if(this.components[i].id == 'background') {
                            this.components[i].data = this.bg;
                        }
                    }
                }else {
                    let bg = {};
                    bg.data = this.bg;
                    bg.id = 'background';
                    this.components.push(bg);
                }
                const postComponents = [];
                for (let i in this.components) {
                    postComponents.push({
                        id: this.components[i].id,
                        data: this.components[i].data,
                    });
                }
                this.$request({
                    params: {
                        r: 'plugin/diy/mall/template/edit',
                        id: isSaveAs ? null : this.id,
                    },
                    method: 'post',
                    data: {
                        name: isSaveAs ? saveAsName : this.templateName,
                        data: JSON.stringify(postComponents),
                    },
                }).then(response => {
                    this.submitLoading = false;
                    if (response.data.code === 0) {
                        this.id = response.data.data.id;
                        location.reload();
                        this.$message.success(response.data.msg);
                        // this.loadData();
                    } else {
                        this.$message.error(response.data.msg);
                    }
                }).catch(e => {
                });
            },
            saveAs() {
                this.$prompt('请输入新模板名称:', '另存为').then(({value}) => {
                    if (value) {
                        this.save(true, value);
                    }
                }).catch(() => {
                });
            },
        },
    });
</script>
