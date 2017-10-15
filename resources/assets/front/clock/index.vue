<style>
	@import '../../flatpickr/css/airbnb.css';

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
		<Alert banner type="error" show-icon v-if="!inShop">
			没有所属店铺
			<template slot="desc">请联系人事添加店铺归属</template>
		</Alert>
		<Alert banner type="error" show-icon v-else-if="!currentUser.shop.lng">
			店铺尚未定位
			<template slot="desc">请联系店长登录系统完成定位</template>
		</Alert>
		<Alert banner type="error" show-icon v-else-if="!aLocation" v-show="locationErr != false">
			定位失败
			<template slot="desc">请检查是否开启定位或使用店长代签打卡<br>
				<span v-if="locationErr != false">错误信息：{{locationErr}}</span>
			</template>
		</Alert>
		<Alert banner type="error" show-icon v-else-if="!clockAvailable && date == today">
			无法打卡，未知原因
			<template slot="desc">请联系IT部<br>
				<span v-if="locationErr != false">错误信息：{{locationErr}}</span>
			</template>
		</Alert>
		<Card :shadow="true">
			<Row>
				<i-col span="12" offset="6">
					<h2 style="text-align:center;">{{date}}</h2>
				</i-col>
				<Button id="calendar" :class="showCalendar?'calendar-show':'calendar-hide'"
				        :icon="showCalendar?'close':'calendar'" type="primary" shape="circle"
				        style="float:right"
				        @click="showCalendar = !showCalendar"></Button>
			</Row>
			<br>
			<Timeline>
				<template v-for="clock in clocks">
					<Timeline-item :color="setIconColor(clock)"
					               :style="clock.is_abandoned?'color:lightgrey;':''">
						<h3>
							{{clock.clock_at | hourAndMinute}} {{clock.clock_type}} {{clock.shop_sn ? clock.shop_sn.toUpperCase() : '无店铺'}}</h3>
						<p v-if="clock.lng > 0">
							<Icon type="location"/>
							{{clock.address}}
						</p>
						<p v-if="clock.thumb != '' && !clock.is_abandoned">
							<img :src="clock.thumb" @click="showPhoto(clock)">
						</p>
						<Button v-if="clockAvailable && inShop && clock.attendance_type == 1 && clock.type == 2 && !clock.is_abandoned"
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
		<Modal v-model="bigPhoto" :styles="{top:'20px'}">
			<img :src="bigPhoto.photo" width="100%">
			<div slot="footer" style="text-align:left;">
				<p style="padding-left:15px;">
					<Icon type="clock" style="margin-left:-13px;"/>
					<span>{{bigPhoto.clock_at}}</span>
				</p>
				<p style="padding-left:15px;">
					<Icon type="location" style="margin-left:-10px;margin-right:1px;"/>
					<span>{{bigPhoto.address}}</span>
				</p>
			</div>
		</Modal>
	</div>
</template>


<script>
    import Flatpickr from 'flatpickr';

    const Chinese = require('../../flatpickr/l10ns/zh.js').zh;
    export default {
        data() {
            return {
                clocks: [],		        //打卡信息
                transfer: false,	    //调动信息
                leave: false,		    //请假信息

                workingStart: false,
                workingEnd: false,
                hasCommit: false,	    //已经提交
                aLocation: false,	    //定位信息
                locationErr: false,	    //定位失败信息
                date: '',			    //选择的日期
                today: false,		    //当前的考勤日
                showCalendar: false,	//是否显示日历
                curTime: '12:00:00',    //当前时间
                bigPhoto: false,
            };
        },
        props: ['currentUser'],
        computed: {
            inShop: function () { //员工是否有所属店铺;
                if (this.currentUser) {
                    return this.currentUser.shop_sn.length > 0;
                } else {
                    return false;
                }
            },
            clockAvailable: function () { //是否可以打卡
                return this.date === this.today && this.aLocation !== false && !this.locationErr;
            },
            inTime: function () {
                return (this.hasClockIn && this.curTime > this.currentUser.working_end_at) || (!this.hasClockIn && this.curTime < this.currentUser.working_start_at);
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
                    return (current > start) && (this.hasClockIn || this.leave.clock_out_at != null);
                } else {
                    return false;
                }
            },
            isLeaving: function () {
                if (this.hasLeave) {
                    let current = new Date(this.today.replace(/-/g, "/") + ' ' + this.curTime);
                    let clockIn = new Date(this.leave.clock_in_at.replace(/-/g, "/"));
                    return this.leave.clock_out_at && (!this.leave.clock_in_at || clockIn > current);
                } else {
                    return false;
                }
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
                let clock;
                for (let i in this.clocks) {
                    clock = this.clocks[i];
                    if (clock.type === 2 && clock.attendance_type === 1) {
                        return true;
                    }
                }
                return false;
            },
        },
        filters: {
            hourAndMinute: function (value) {
                return value.substring(11, 16);
            }
        },
        beforeMount() {
            let _this = this;
            this.getClockRecord();
            this.getTransferRecord();
            this.getLeaveRecord();
            this.getCurTime();
            setInterval(this.getCurTime, 1000);
        },
        mounted() {
            this.dingtalkInit();
            Flatpickr("#calendar", {
                inline: true,
                locale: Chinese,
                defaultDate: 'today',
                maxDate: 'today',
                onChange: (selectedDates, dateStr) => {
                    this.date = dateStr;
                    this.getClockRecord(dateStr);
                }
            });
        },
        methods: {
            getClockRecord() {
                let _this = this;
                let dateStr = arguments[0] ? arguments[0] : null;
                let url = '/clock/info';
                axios.post(url, {date: dateStr}).then(function (response) {
                    _this.clocks = response.data.record;
                    _this.today = response.data.today;
                    _this.date = !_this.date ? _this.today : _this.date;
                });
            },
            getTransferRecord() {
                let _this = this;
                let url = '/transfer/next';
                axios.post(url).then(function (response) {
                    _this.transfer = response.data == '' ? false : response.data;
                });
            },
            getLeaveRecord() {
                let _this = this;
                let url = '/leave/next';
                axios.post(url).then(function (response) {
                    _this.leave = response.data == '' ? false : response.data;
                });
            },
            getLocation(first) {
                if (!arguments[0]) first = false;
                dd.device.geolocation.get({
                    targetAccuracy: 15,
                    coordinate: 1,
                    withReGeocode: true,
                    useCache: false,
                    onSuccess: (result) => {
                        try {
                            this.aLocation = {
                                position: {
                                    lng: result.longitude,
                                    lat: result.latitude
                                },
                                formattedAddress: result.address
                            };
                        } catch (e) {
                            this.locationErr = e.message;
                        } finally {
                            this.locationErr = false;
                        }
                        if (first) {
                            Indicator.close();
                        }
                    },
                    onFail: (err) => {
                        this.locationErr = err.errorMessage;
                        if (first) {
                            Indicator.close();
                        }
                    }
                });
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
                        if ((clock.type == 1 && clock.clock_at > clock.punctual_time)
                            || (clock.type == 2 && clock.clock_at < clock.punctual_time)) {
                            return "red";
                        }
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
                this.takePhoto((picPath) => {
                    Indicator.open('处理中...');
                    let clockType = this.hasClockIn ? 'clock_out' : 'clock_in';
                    let url = '/clock/save';
                    let params = {
                        type: clockType,
                        lng: this.aLocation.position.lng,
                        lat: this.aLocation.position.lat,
                        address: this.aLocation.formattedAddress,
                        photo: picPath[0]
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
                        this.getClockRecord();
                    }).catch((err) => {
                        document.write(err);
                    });
                });
            },
            uploadLeaveClock() {
                this.takePhoto((picPath) => {
                    Indicator.open('处理中...');
                    let leaveID = this.leave.id;
                    let url = '/leave/save';
                    let params = {
                        parent_id: leaveID,
                        lng: this.aLocation.position.lng,
                        lat: this.aLocation.position.lat,
                        address: this.aLocation.formattedAddress,
                        photo: picPath[0]
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
                        this.getClockRecord();
                        this.getLeaveRecord();
                    }).catch((err) => {
                        document.write(err);
                    });
                });
            },
            uploadTransferClock() {
                this.takePhoto((picPath) => {
                    Indicator.open('处理中...');
                    let transferID = this.transfer.id;
                    let url = '/transfer/save';
                    let params = {
                        parent_id: transferID,
                        lng: this.aLocation.position.lng,
                        lat: this.aLocation.position.lat,
                        address: this.aLocation.formattedAddress,
                        photo: picPath[0]
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
                        this.getClockRecord();
                        this.getTransferRecord();
                    }).catch(function (err) {
                        document.write(err);
                    });
                });
            },
            dingtalkInit() {
                let url = '/js_config';
                axios.post(url, {'current_url': location.href}).then((response) => {
                    let jsConfig = response.data;
                    jsConfig['jsApiList'] = ['biz.util.uploadImageFromCamera', 'device.geolocation.get'];
                    dd.config(jsConfig);
                    dd.ready(() => {
                        Indicator.open('定位中...');
                        this.getLocation(true);
                        setInterval(this.getLocation, 10000);
                    });
                    dd.error(function (error) {
                        document.write(JSON.stringify(error));
                    });
                });
            },
            takePhoto(callback) {
                dd.ready(() => {
                    dd.biz.util.uploadImageFromCamera({
                        onSuccess: (picPath) => {
                            callback(picPath);
                        }
                    });
                })
            },
            showPhoto(clock) {
                this.bigPhoto = clock;
            }
        }
    }
</script>
