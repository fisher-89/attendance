<style scoped>
    @import '../../flatpickr/css/airbnb.css';

    .big_circle_button {
        fill: #5bc0de;
        stroke: #46b8da;
        stroke-width: 1px;
    }

    .big_circle_button:hover {
        fill: #31b0d5;
        stroke: #269abc;
    }

    .big_circle_button_text {
        font-size: 18px;
        fill: #ffffff;
        text-anchor: middle;
        dominant-baseline: middle;
    }

    #calendar {
        float: right;
        margin-top: -34px;
    }
</style>
<style>
    #calendar + .flatpickr-calendar {
        width: 100%;
        max-width: 400px;
        overflow: hidden;
        transition: all .5s;
    }

    #calendar + .flatpickr-calendar:before,
    #calendar + .flatpickr-calendar:after {
        content: none;
    }

    #calendar.calendar-show + .flatpickr-calendar {
        height: 290px;
    }

    #calendar.calendar-hide + .flatpickr-calendar {
        height: 0px;
    }
</style>

<template>
    <div>
        <Alert banner type="error" show-icon v-if="locationErr">
            <template slot="desc">{{locationErr}}</template>
        </Alert>
        <Card :shadow="true">
            <Row style="text-align:center;">
                <h2 style="margin-bottom:2px;">{{date}}</h2>
                <Button id="calendar" :class="showCalendar?'calendar-show':'calendar-hide'"
                        :icon="showCalendar?'close':'calendar'" type="primary" shape="circle"
                        @click="showCalendar = !showCalendar"></Button>
            </Row>
            <br>
            <Timeline>
                <template v-for="clock in clocks">
                    <Timeline-item :color="setIconColor(clock)" :style="clock.is_abandoned?'color:lightgrey;':''">
                        <h3>{{clock.created_at | hourAndMinute}} {{clock.clock_type}} {{clock.shop_sn?clock.shop_sn.toUpperCase():'无店铺'}}</h3>
                        <p>
                            <Icon type="location"/>
                            {{clock.address}}
                        </p>
                        <Button v-if="clockAvailable && inShop && clock.clock_type === '下班' && !clock.is_abandoned"
                                type="ghost" shape="circle" @click="uploadClock">更新打卡
                        </Button>
                    </Timeline-item>
                </template>
                <Timeline-item v-if="clockAvailable && inShop && !hasClockOut && !isTransferring && !hasLeave"
                               :color="inTime?'green':'red'">
                    <Button :type="inTime?'success':'error'" size="large" shape="circle" @click="uploadClock">
                        <h3 style="display:inline;">&nbsp;{{hasClockIn ? '下班打卡' : '上班打卡'}}&nbsp;</h3>
                        <p style="display:inline;">{{curTime}}</p>
                    </Button>
                </Timeline-item>
                <Timeline-item v-else-if="clockAvailable && hasLeave" color="orange">
                    <Button type="warning" size="large" shape="circle" @click="uploadLeaveClock">
                        <h3 style="display:inline;">&nbsp;{{isLeaving ? '请假返回' : '请假外出'}}&nbsp;</h3>
                        <p style="display:inline;">{{curTime}}</p>
                    </Button>
                </Timeline-item>
                <Timeline-item v-if="clockAvailable && hasTransfer && !isLeaving">
                    <Button type="primary" size="large" shape="circle" @click="uploadTransferClock">
                        <h3 style="display:inline;">&nbsp;{{transfer.status == 1 ? '调动到达' : '调动出发'}}&nbsp;</h3>
                        <p style="display:inline;">{{curTime}}</p>
                    </Button>
                    <p>至：{{transfer.arriving_shop_name}}</p>
                </Timeline-item>
            </Timeline>
        </Card>
    </div>
</template>


<script>
    import Flatpickr from 'flatpickr';

    const Chinese = require('../../flatpickr/l10ns/zh.js').zh;
    /* 高德地图定位 start */
    let aGeolocation = new AMap.Geolocation({
        timeout: 30000,							//超过30秒后停止定位，默认：无穷大
        noIpLocate: 3,
    });
    /* 高德地图定位 end */
    export default {
        data() {
            return {
                clocks: [],		//打卡信息
                transfer: false,		//调动信息
                leave: false,		//请假信息

                hasCommit: false,	//已经提交
                aLocation: false,	//定位信息
                locationErr: false,	//定位失败信息
                date: '',			//选择的日期
                today: false,		//当前的考勤日
                showCalendar: false,	//是否显示日历
                curTime: '12:00:00',
            };
        },
        props: ['currentUser'],
        computed: {
            inShop: function () { //员工是否有所属店铺
                if (this.currentUser) {
                    return this.currentUser.shop_sn.length > 0;
                } else {
                    return false;
                }
            },
            clockAvailable: function () { //是否可以打卡
                return this.date === this.today && !this.locationErr;
            },
            inTime: function () {
                return (this.hasClockIn && this.curTime > this.currentUser.shop.clock_out) || (!this.hasClockIn && this.curTime < this.currentUser.shop.clock_in);
            },
            /* 状态判断 start */
            hasTransfer: function () {
                return this.transfer !== false;
            },
            isTransferring: function () {
                return this.hasTransfer && this.transfer.status == 1;
            },
            hasLeave: function () {
                if (this.today && this.leave) {
                    let current = new Date(this.today.replace(/-/g, "/") + ' ' + this.curTime);
                    let start = new Date(this.leave.start_at.replace(/-/g, "/"));
                    let end = new Date(this.leave.end_at.replace(/-/g, "/"));
                    return (current > start) && (current < end) && this.hasClockIn;
                } else {
                    return false;
                }

            },
            isLeaving: function () {
                return this.leave.has_clock_out && !this.leave.has_clock_in;
            },
            /* 状态判断 end */
            hasClockIn: function () { //是否已经打完上班卡
                let clock;
                for (let i in this.clocks) {
                    clock = this.clocks[i];
                    if (clock.type === 1) {
                        return true;
                    }
                }
                return false;
            },
            hasClockOut: function () { //是否已经打完下班卡
                let latestClock = this.clocks[this.clocks.length - 1];
                return this.hasClockIn && latestClock.type == 2;
            },
        },
        filters:{
            hourAndMinute:function(value){
                return value.substring(11,16);
            }
        },
        beforeMount() {
            let _this = this;
            this.getClockRecord();
            this.getTransferRecord();
            this.getLeaveRecord();
            _this.getCurTime();
            setInterval(function () {
                _this.getCurTime();
            }, 1000);
            /*定位 start*/
            AMap.event.addListener(aGeolocation, 'complete', function (result) {
                _this.getLocation(result);
            });
            AMap.event.addListener(aGeolocation, 'error', function (err) {
                _this.getLocationErr(err);
            });
            Indicator.open('定位中...');
            aGeolocation.getCurrentPosition(function (status, result) {
                Indicator.close();
            });
            /*定位 end*/
        },
        mounted() {
            let _this = this;
            Flatpickr("#calendar", {
                inline: true,
                locale: Chinese,
                defaultDate: 'today',
                maxDate: 'today',
                onChange: function (selectedDates, dateStr) {
                    _this.date = dateStr;
                    _this.getClockRecord(dateStr);
                }
            });
        },
        methods: {
            getClockRecord() {
                let _this = this;
                let dateStr = arguments[0] ? arguments[0] : null;
                let url = '/api/clock/info';
                axios.post(url, {date: dateStr}).then(function (response) {
                    _this.clocks = response.data.record;
                    _this.today = response.data.today;
                    _this.date = !_this.date ? _this.today : _this.date;
                });
            },
            getTransferRecord() {
                let _this = this;
                let url = '/api/transfer/next';
                axios.post(url).then(function (response) {
                    _this.transfer = response.data == '' ? false : response.data;
                });
            },
            getLeaveRecord() {
                let _this = this;
                let url = '/api/leave/next';
                axios.post(url).then(function (response) {
                    _this.leave = response.data == '' ? false : response.data;
                });
            },
            getLocation(result) {
                this.aLocation = result;
                setTimeout(function () {
                    aGeolocation.getCurrentPosition();
                }, 10000);
            },
            getLocationErr(err) {
                let info = err.info;
                let message = err.message;
                if (info === 'NOT_SUPPORTED' || message === 'Get geolocation failed.') {
                    this.locationErr = '浏览器不支持定位功能，请使用店长终端打卡';
                } else if (message === 'Get geolocation time out.') {
                    this.locationErr = '定位超时，请检查网络状态或使用店长终端打卡';
                    aGeolocation.getCurrentPosition();
                } else {
                    this.locationErr = '无法使用定位，请检查是否开启定位或使用店长终端打卡';
                }
            },
            getCurTime() {
                let date = new Date();
                let hour = (Array(2).join(0) + date.getHours()).slice(-2);
                let minute = (Array(2).join(0) + date.getMinutes()).slice(-2);
                let second = (Array(2).join(0) + date.getSeconds()).slice(-2);
                this.curTime = hour + ':' + minute + ':' + second;
            },
            setIconColor(clock) {
                if (clock.is_abandoned) {
                    return "lightgrey";
                }
                switch (clock.attendance_type) {
                    case 1:
                        return "green";
                        break;
                    case 2:
                        break;
                    case 3:
                        return "orange";
                        break;
                }
            },
            uploadClock() {
                let clockType = this.hasClockIn ? 'clock_out' : 'clock_in';
                let _this = this;
                Indicator.open('上传中...');
                let url = '/api/clock/save';
                let params = {
                    type: clockType,
                    lng: this.aLocation.position.lng,
                    lat: this.aLocation.position.lat,
                    address: this.aLocation.formattedAddress
                };
                axios.post(url, params).then((response) => {
                    if (typeof response.data == 'string') {
                        document.write(response.data);
                    }
                    if (response.data.status) {
                        this.$Message.success(response.data.msg);
                    } else {
                        this.$Message.error(response.data.msg);
                    }
                    Indicator.close();
                    _this.getClockRecord();
                }).catch((err) => {
                    document.write(err);
                });
            },
            uploadLeaveClock() {
                let _this = this;
                Indicator.open('上传中...');
                let leaveID = this.leave.id;
                let url = '/api/leave/save';
                let params = {
                    parent_id: leaveID,
                    lng: this.aLocation.position.lng,
                    lat: this.aLocation.position.lat,
                    address: this.aLocation.formattedAddress
                };
                axios.post(url, params).then((response) => {
                    if (typeof response.data == 'string') {
                        document.write(response.data);
                    }
                    if (response.data.status) {
                        this.$Message.success(response.data.msg);
                    } else {
                        this.$Message.error(response.data.msg);
                    }
                    Indicator.close();
                    _this.getClockRecord();
                    _this.getLeaveRecord();
                }).catch((err) => {
                    document.write(err);
                });
            },
            uploadTransferClock() {
                let _this = this;
                Indicator.open('上传中...');
                let transferID = this.transfer.id;
                let url = '/api/transfer/save';
                let params = {
                    parent_id: transferID,
                    lng: this.aLocation.position.lng,
                    lat: this.aLocation.position.lat,
                    address: this.aLocation.formattedAddress
                };
                axios.post(url, params).then((response) => {
                    if (typeof response.data == 'string') {
                        document.write(response.data);
                    }
                    if (response.data.status) {
                        this.$Message.success(response.data.msg);
                    } else {
                        this.$Message.error(response.data.msg);
                    }
                    Indicator.close();
                    _this.getClockRecord();
                    _this.getTransferRecord();
                }).catch(function (err) {
                    document.write(err);
                });
            }
        }
    }
</script>
