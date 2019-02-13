<template>
    <div class="app-container">
        <div class="table_view">

            <el-row>
                <el-col :span="24">
                    <el-date-picker editable="false" size="small" v-model="CallDateTime" type="date"
                                    placeholder="选择日期"
                                    align="right"
                                    :picker-options="pickerOptions">
                    </el-date-picker>
                    <el-button style="margin-left: 30px" type="primary" @click="getCountDataList()">查询</el-button>
                </el-col>
            </el-row>
            <el-row>
                <br/>
            </el-row>
            <el-row>
                <el-col>
                    <el-button type="primary" icon="el-icon-download" @click="clickExportTabel">导出表格</el-button>
                </el-col>
            </el-row>

            <el-table class="tableListView" :data="CountDataList" style="width: 100%" max-height="600"
                      v-loading="tableLoading" :header-cell-style="tableHeaderColor" :cell-style="cellColor" :span-method="objectSpanMethod">
                <el-table-column align="center" :label="titledate">
                    <el-table-column align="center" prop="id" width="66" label="序号" fixed="left"></el-table-column>
                    <el-table-column align="center" prop="bumen" min-width="120" label="部门"></el-table-column>
                    <el-table-column align="center" prop="username" min-width="120" label="业务姓名"></el-table-column>
                    <el-table-column align="center" prop="callnum" min-width="120" label="拨打数"></el-table-column>
                    <el-table-column align="center" prop="billnum" min-width="120" label="接通数"></el-table-column>
                    <el-table-column align="center" prop="billlv" min-width="120" label="接通率"></el-table-column>
                    <el-table-column align="center" prop="fenpeinum" min-width="120" label="分配数"></el-table-column>
                    <el-table-column align="center" prop="addnum" min-width="120" label="成功添加数"></el-table-column>
                    <el-table-column align="center" prop="addlv" min-width="120" label="添加率"></el-table-column>
                </el-table-column>
            </el-table>
        </div>
    </div>
</template>

<script>
    import { getToken, setToken, removeToken } from '@/utils/auth'
    import { countdatalist,exlcountdata } from '@/api/Number'
    import tool from '@/config/tools'

    export default {
        name: "DocDetails",
        components: { tool },
        data(){
            return{
                CountDataList:[],
                tableLoading: false,
                pagesize: 30,
                page: 1,
                total: 0,
                titledate:'',

                pickerOptions: {
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
                        return date.getTime() >= Date.now();
                    }
                },
                CallDateTime: '',
            }
        },
        created(){
            this.getCountDataList();
            // this.timeNow();
            // console.log(this.$route.params.id);
        },
        methods: {
            formatDate(time) {
                if (time) {
                    var date = new Date(time);
                    return tool.formatDate(date, 'yyyy-MM-dd');
                } else {
                    return "";
                }
            },
            // timeNow () {
            //     const myDate = new Date();
            //     var month = myDate.getMonth() + 1;
            //     var strDate = myDate.getDate();
            //     if (month >= 1 && month <= 9) {
            //         month = "0" + month;
            //     }
            //     if (strDate >= 0 && strDate <= 9) {
            //         strDate = "0" + strDate;
            //     }
            //     this.titledate = '白宾机器人数据('+month+'/'+strDate+ ') '
            // },
            timeNow () {
                var Time = this.formatDate(this.CallDateTime);
                if(Time.length == 0){
                    const myDate = new Date();
                    var year = myDate.getFullYear();
                    var month = myDate.getMonth() + 1;
                    var strdate = myDate.getDate();
                    if (month >= 1 && month <= 9) {
                        month = "0" + month;
                    }
                    if (strdate >= 0 && strdate <= 9) {
                        strdate = "0" +strdate;
                    }
                    Time = year + "-" + month + "-" + strdate;
                }
                this.titledate = '白宾机器人数据('+Time+')';
                console.log(Time);
            },
            getCountDataList() {
                this.timeNow();
                var Time = this.formatDate(this.CallDateTime);
                if(Time.length == 0){
                    const myDate = new Date();
                    var year = myDate.getFullYear();
                    var month = myDate.getMonth() + 1;
                    var strdate = myDate.getDate();
                    if (month >= 1 && month <= 9) {
                        month = "0" + month;
                    }
                    if (strdate >= 0 && strdate <= 9) {
                        strdate = "0" +strdate;
                    }
                    Time = year + "-" + month + "-" + strdate;
                }
                var params = {
                    s: this.page,
                    p: this.pagesize,
                    aid: this.$route.params.id,
                    time: Time,
                }
                this.tableLoading = true;
                // console.log(this.formatData(this.CallDateTime));
                countdatalist(params).then(response => {
                    console.log(response);
                    this.tableLoading = false;
                    var res = response.data;
                    if (res.code == "0000") {
                        this.CountDataList = res.data.rows;
                        this.total = res.data.total;
                        console.log(res.data)
                    }
                }).catch(err => {
                    console.log(err);
                });
            },
            // clickExportTabel(){
            //     window.location.href="http://sai.bbxxjs.com/exlcountdata";
            // },
            clickExportTabel(){
                this.timeNow();
                var Time = this.formatDate(this.CallDateTime);
                if(Time.length == 0){
                    const myDate = new Date();
                    var year = myDate.getFullYear();
                    var month = myDate.getMonth() + 1;
                    var strdate = myDate.getDate();
                    if (month >= 1 && month <= 9) {
                        month = "0" + month;
                    }
                    if (strdate >= 0 && strdate <= 9) {
                        strdate = "0" +strdate;
                    }
                    Time = year + "-" + month + "-" + strdate;
                }
                window.location.href="http://sai.bbxxjs.com/exlcountdata?aid="+this.$route.params.id+"&time="+Time;
            },
            objectSpanMethod({row, column, rowIndex, columnIndex}) {
                if (columnIndex === 1) {
                    if (rowIndex % (this.total - 1) === 0) {
                        return {
                            rowspan: this.total - 1,
                            colspan: 1
                        };
                    } else {
                        return {
                            rowspan: 0,
                            colspan: 0
                        };
                    }
                }
                // console.log(rowIndex);
                // console.log("--------------------------------------------------------------");
                console.log(this.total);
                if (rowIndex+1 === this.total) {
                    if (columnIndex === 0) {
                        return [1, 3];
                    }
                }
            },
            // 点击页码
            handleCurrentChange(val) {
                this.page = val;
                this.getTabeleData();
            },
            // 修改table header的背景色
            tableHeaderColor({row, column, rowIndex, columnIndex}) {
                if (rowIndex === 0) {
                    return 'background-color: #f7f7f7;color: #363636;font-weight: 500;'
                }
            },
            cellColor({row, column, rowIndex, columnIndex}){
                if(rowIndex+1 === this.total){
                    return 'background-color: #A4DDA9;color: #D52114;font-weight: 500;'
                }
            },
        }
    }
</script>

<style scoped>
    .DocDetails {
        width: 100%;
        padding: 20px;
    }
    .table_view {
        margin-top: 10px;
        border: 1px solid #dfe6ec;
        padding: 10px;
        border-radius: 5px;
    }
    .tableListView {
        margin-top: 15px;
    }
    .el-table__footer-wrapper tbody td, .el-table__header-wrapper tbody td {
        background-color: #feff66;
        color: #606266;
    }
</style>