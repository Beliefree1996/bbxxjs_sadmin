<template>
    <div class="app-container">
        <div class="filter-container">
            <el-col :span="16" class="table_info">
                <!--<span class="info_item">号码表名称：{{table_name}}</span>-->
                <!--<span class="info_item" style="text-align: center;">话费余额：{{table_snum}}</span>-->
            </el-col>

            <!--工具条-->
            <el-col :span="8" class="top_toolbar">
                <!--条件搜索-->
                <el-form :inline="true" :model="filters" style="display: inline-block;padding: 0;">
                    <el-form-item>
                        <el-input v-model="filters.name" placeholder="管理员名字" style="width: 120px"></el-input>
                    </el-form-item>
                    <el-form-item style="margin-left: 10px">
                        <el-button type="primary" v-on:click="getTable">查询</el-button>
                    </el-form-item>
                </el-form>
            </el-col>

            <!--列表-->
            <el-table :data="table_list"  class="auto_table" height="100%" v-loading="tableLoading" border
                      style="width: 100%;" @selection-change="handleSelectionChange">
                <el-table-column align="center" type="selection" width="55"></el-table-column>
                <el-table-column align="center" type="index" label="序号" width="66"></el-table-column>
                <el-table-column align="center" prop="username" label="管理员名字"></el-table-column>
                <el-table-column align="center" prop="amount" label="机器人数量"></el-table-column>
                <el-table-column align="center" prop="limit" label="当前额度"></el-table-column>
                <el-table-column align="center" label="操作" width="120" fixed="right">
                    <template slot-scope="scope">
                        <el-button size="small" @click="distribute_charge(scope.$index, scope.row)">设置额度</el-button>
                    </template>
                </el-table-column>
            </el-table>

            <!--页码条-->
            <el-col :span="24" class="toolbar" style="margin-top: 10px">
                <el-button type="primary" :disabled="this.multipleSelection.length===0" @click="groupDistribute_charge">批量设置
                </el-button>
                <!--<el-pagination layout="prev, pager, next" @current-change="handleCurrentChange" :page-size="pagesize"-->
                               <!--:total="total" style="float:right;"></el-pagination>-->
            </el-col>
            <el-col :span="24" class="selectAdminView" style="margin-top: 20px">
                   <span v-show="multipleSelection.length !== 0" style="color: #555;margin-right: 10px">选中的管理员:
                   </span> <span class="selectAdminName" v-for="item in multipleSelection">
                    {{item.username}}</span>
            </el-col>
            <!--分配弹窗-->
            <el-dialog title="分配话费" :visible.sync="distribute_dialog" center>
                <div class="disnum_box">
                    <span>话费配额：</span>
                    <el-input :max="isBatch?Math.floor(table_snum/multipleSelection.length):table_snum"
                              v-model="disnum" value="number"
                              :placeholder="isBatch?'最大能分配'+Math.floor(table_snum/multipleSelection.length)+'元话费':'请输入话费数额'"></el-input>
                              <!--placeholder="请为该管理员的机器人分配话费数额"></el-input>-->
                    <!--<span style="color: #3c763d">共需：{{zzzz}} 元</span>-->
                </div>
                <div slot="footer" class="dialog-footer">
                    <div :style="'dispaly:'+isDistributing?'block':'none;'"
                         style="text-align: center;font-size: 14px;padding: 5px;">
                        {{isDistributing?'正在加载中...':''}}</div>
                    <el-button type="primary" @click="distribute" :disabled="isDistributing" :loading="isDistributing">确 定</el-button>
                </div>
            </el-dialog>
        </div>
    </div>
</template>

<script>
    import { getToken, setToken, removeToken } from '@/utils/auth'
    import { hfuserlist,lefthf,chargefp } from '@/api/Number'

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
                zzzz:0,
            }
        },
        created(){
            this.table_id = this.$route.params && this.$route.params.id
            this.token = getToken()
            console.log(this.table_id)
            this.getTableInfo();
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
                    console.log(res)
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
            getTableInfo(){
                var _this = this;
                var Params = {
                    token:_this.token,
                    id: _this.table_id,
                };

                lefthf(Params).then(response => {
                    console.log(response);
                    const res = response.data;
                    console.log(res)
                    if (res.code == '0000') {
                        _this.table_snum = res.data.snum;  // url:https://sa.haishunsh.com/api/getgustomer   参数:{"e164s":"AI24"}  Headers:key:HTTP_SA,values:1
                    }
                })
            },
            handleSelectionChange(val){
                console.log(val);
                this.multipleSelection = val;
            },
            // 分配弹窗
            distribute_charge: function (index, row) {
                this.distribute_dialog = true;
                this.diszid = row.id.toString();
            },
            ///批量分配
            groupDistribute_charge(){
                this.distribute_dialog = true;
                this.diszid = "";
                for (var i=0;i<this.multipleSelection.length;i++){
                    if (i!=this.multipleSelection.length-1){
                        this.diszid += this.multipleSelection[i].id.toString() +',';
                    }else{
                        this.diszid += this.multipleSelection[i].id.toString();
                    }
                }
                console.log(this.diszid);
            },
            // bian(){
            //     this.zzzz = this.disnum * 100;
            // },
            distribute(){
                var _this = this;
                if(this.isBatch){
                    // if ((parseInt(this.disnum)*this.multipleSelection.length) > 0) {
                    //     _this.$message.error("请正确输入分配数额！");
                    //     return;
                    // }
                } else {
                    // if (parseInt(this.disnum) > 0) {
                    //     _this.$message.error("请正确输入分配数额！");
                    //     return;
                    // }
                }
                console.log("111111");
                _this.isDistributing = true;
                var Params = {
                    aidlist: this.diszid,
                    num: this.disnum,
                };

                chargefp(Params).then(response => {
                    console.log(response);
                    _this.isDistributing = true;
                    console.log(response);
                    var res = response.data;
                    if (res.code == '0000') {
                        _this.distribute_dialog = false;
                        const h = _this.$createElement;
                        _this.$notify({
                            title: '分配成功',
                            message: h('i', {style: 'color: teal'}, '话费分配成功')
                        });
                        _this.disnum = null;
                        _this.isDistributing = false;
                        _this.getTable();
                    }else{
                        _this.$alert('话费分配失败，请重试', '分配失败', {
                            confirmButtonText: '确定'
                        });
                    }
                }).catch(err => {
                    _this.isDistributing = true;
                    _this.$message.error("网络加载失败！");
                });
            }
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
    .selectAdminView {
        overflow: auto;
        white-space: nowrap;
        vertical-align: middle;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        height: 40px;
    }

    .selectAdminName {
        margin-right: 5px;
        padding: 3px 10px;
        background-color: #3a8ee6;
        color: white;
        border-radius: 5px;
    }

    .filter-container{
        height: calc(100vh - 84px - 40px);
    }

    .filter-container:after{
        content: '';
        height: 0;
        width: 0;
        clear: both;
        display: block;
    }

    .auto_table{
        max-height: calc(100% - 144px);
        overflow: auto;
    }
</style>