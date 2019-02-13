<template>
    <div class="app-container">
        <div class="filter-container">
            <el-col :span="16" class="table_info">
                <!--<span class="info_item">号码表名称：{{table_name}}</span>-->
                <!--<span class="info_item" style="text-align: center;">话费余额：{{table_snum}}</span>-->
            </el-col>

            <!--工具条-->
            <el-row :span="8" class="top_toolbar">
                <!--条件搜索-->
                <el-form :inline="true" :model="filters" style="display: inline-block;padding: 0;">
                    <el-form-item>
                        <el-input v-model="filters.name" placeholder="管理员名字" style="width: 120px"></el-input>
                    </el-form-item>
                    <el-form-item style="margin-left: 10px">
                        <el-button type="primary" v-on:click="getTable">查询</el-button>
                    </el-form-item>
                </el-form>
            </el-row>

            <el-row :span="80" class="top_toolbar">
                <el-col :span="24">
                    <span style="color: #555;font-size: 16px;margin-left: 10px;">通话日期：</span>
                    <el-date-picker editable="false" size="small" v-model="startCallDateTime" type="date"
                                    placeholder="通话开始日期"
                                    align="right"
                                    :picker-options="pickerOptions1">
                    </el-date-picker>
                    <span style="color: #aaa;"> ～</span>
                    <el-date-picker editable="false" size="small" v-model="endCallDateTime" type="date"
                                    placeholder="通话结束日期"
                                    align="right" :picker-options="pickerOptions2"></el-date-picker>
                </el-col>
            </el-row>
            <el-row>
                <br/>
            </el-row>

            <el-row :span="80" class="top_toolbar">
                <!--导出号码簿-->
                <el-form :inline="true" :model="filters" style="display: inline-block;padding: 0;">
                    <el-form-item style="margin-left: 10px">
                        <span style="color: #555;font-size: 17px;">客户类型分类导出：</span>
                    </el-form-item>
                    <el-form-item style="margin-left: 10px">
                        <el-button type="primary" v-on:click="extoexl('a')">导出A类</el-button>
                    </el-form-item>
                    <el-form-item style="margin-left: 10px">
                        <el-button type="primary" v-on:click="extoexl('b')">导出B类</el-button>
                    </el-form-item>
                    <el-form-item style="margin-left: 10px">
                        <el-button type="primary" v-on:click="extoexl('c')">导出C类</el-button>
                    </el-form-item>
                    <el-form-item style="margin-left: 10px">
                        <el-button type="primary" v-on:click="extoexl('d')">导出空号</el-button>
                    </el-form-item>
                    <el-form-item style="margin-left: 10px">
                        <el-button type="primary" v-on:click="extoexl('e')">导出未接</el-button>
                    </el-form-item>
                    <el-form-item style="margin-left: 10px">
                        <el-button type="primary" v-on:click="extoexl('f')">除去推送(A-B)</el-button>
                    </el-form-item>
                </el-form>
            </el-row>

            <!--列表-->
            <el-table :data="table_list" max-height="640" highlight-current-row  v-loading="tableLoading" border
                      style="width: 100%;" @selection-change="handleSelectionChange">
                <el-table-column align="center" type="selection" width="55"></el-table-column>
                <el-table-column align="center" type="index" label="序号" width="66"></el-table-column>
                <el-table-column align="center" prop="username" label="管理员名字"></el-table-column>
                <el-table-column align="center" prop="amount" label="机器人数量"></el-table-column>
                <el-table-column align="center" label="操作" width="120" fixed="right">
                    <template slot-scope="scope">
                        <router-link :to="'/table/DocDetails/'+scope.row.id">
                            <el-button type="text" size="small">查看报表</el-button>
                        </router-link>
                    </template>
                </el-table-column>
            </el-table>
            <!--页码条-->
            <el-col :span="24" class="toolbar" style="margin-top: 10px">
                <!--<el-pagination layout="prev, pager, next" @current-change="handleCurrentChange" :page-size="pagesize"-->
                               <!--:total="total" style="float:right;"></el-pagination>-->
            </el-col>
        </div>
    </div>
</template>

<script>
    import { getToken, setToken, removeToken } from '@/utils/auth'
    import { hfuserlist,lefthf,chargefp,extoexl } from '@/api/Number'
    import tool from '@/config/tools'

    export default {
        name: "CallCharge",
        data(){
            return{
                table_snum: 0,
                distribute_dialog: false,
                table_list: [],
                tableLoading: false,
                // total: 0,
                // page: 1,
                // pagesize: 10,
                filters: {name: ''},
                disnum: null,
                diszid: '',
                multipleSelection:[],
                isDistributing:false,
                token:"",

                pickerOptions1: {
                    shortcuts: [{
                        text: '今天',
                        onClick(picker) {
                            picker.$emit('pick', new Date());
                        }
                    }, {
                        text: '昨天',
                        onClick(picker) {
                            const date = new Date();
                            date.setTime(date.getTime() - 3600 * 1000 * 24);
                            picker.$emit('pick', date);
                        }
                    }, {
                        text: '一周前',
                        onClick(picker) {
                            const date = new Date();
                            date.setTime(date.getTime() - 3600 * 1000 * 24 * 7);
                            picker.$emit('pick', date);
                        }
                    }],
                    disabledDate(date) { //disabledDate 文档上：设置禁用状态，参数为当前日期，要求返回 Boolean
                        return date.getTime() >= Date.now() || date.getTime() <= Date.now() - 3600 * 1000 * 24 * 8;
                    }
                },
                pickerOptions2: {
                    disabledDate(date) { //disabledDate 文档上：设置禁用状态，参数为当前日期，要求返回 Boolean
                        return date.getTime() >= Date.now() || date.getTime() <= Date.now() - 3600 * 1000 * 24 * 8;
                    }
                },
                startCallDateTime: '',
                endCallDateTime: '',
            }
        },

        created(){
            this.table_id = this.$route.params && this.$route.params.id;
            this.token = getToken();
            console.log(this.table_id);
            this.getTable();
        },

        methods:{
            // 点击页码
            // handleCurrentChange(val) {
            //     this.page = val;
            //     this.getTable();
            // },
            // 请求表格数据
            getTable() {
                var _this = this;
                var params = {
                    token:_this.token,
                    // s: _this.page,
                    // p: _this.pagesize,
                    name: _this.filters.name
                };
                this.tableLoading = true;
                console.log(params);

                hfuserlist(params).then(response => {
                    console.log(response);
                    const res = response.data;
                    console.log(res);
                    if (res.code == '0000') {
                        this.table_list = res.data.rows;
                        // this.total = res.data.total;
                    } else {
                        this.$message.error(res.msg);
                    }
                    this.tableLoading = false
                }).catch(err => {
                    console.log(err);
                    this.tableLoading = false;
                    this.$message.error("网络错误");
                });
            },
            // 查看详情
            handleDistribute: function (index, row) {
                // window.location.href = "/index/index/distribute?id=" + row.id;
                this.$emit("distributeBack",row.id);
            },
            handleSelectionChange(val){
                console.log(val);
                this.multipleSelection = val;
            },
            //获取号码簿
            extoexl(t){
                console.log(t);
                var startTime = this.formatDate(this.startCallDateTime);
                var endTime = this.formatDate(this.endCallDateTime);
                var token = this.token;
                console.log(startTime);
                console.log(endTime);
                if(startTime < endTime) {
                    window.location.href = "http://sai.bbxxjs.com/extoexl?token=" + token + "&t=" + t + "&st="+startTime+"&et="+endTime;
                }else {
                    this.$message.error("日期选择错误");
                }
            },
            formatDate(time) {
                if (time) {
                    var date = new Date(time);
                    return tool.formatDate(date, 'yyyy-MM-dd');
                } else {
                    return "";
                }
            },
        },
        computed:{
            isBatch(){
                if (this.diszid.toString().indexOf(",") >= 0){
                    return true;
                }
                return false;
            }
        }
    }
</script>

<style scoped>
    .edit-input {
        padding-right: 100px;
    }
    .cancel-btn {
        position: absolute;
        right: 15px;
        top: 10px;
    }
</style>