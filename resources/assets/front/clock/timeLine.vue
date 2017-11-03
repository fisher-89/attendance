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
		<Timeline style="margin-top:20px;">
			<Timeline-item v-for="(clock,key) in clocks" :key="clock.id" :color="setIconColor(clock)"
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
				<Button v-if="clockAvailable && inShop && clock.attendance_type == 1 && clock.type == 2 && !clock.is_abandoned && (key+1) == clocks.length"
				        type="ghost" shape="circle" @click="uploadClock">更新打卡
				</Button>
			</Timeline-item>
			<Timeline-item v-if="locating == true && date == today" color="lightgrey">
				<p style="padding:5px;">
					<mt-spinner type="fading-circle" :size="28"></mt-spinner>
				</p>
				<p style="color:lightgrey">定位中...	</p>
			</Timeline-item>
			<Timeline-item
					v-else-if="clockAvailable && inShop && !hasClockOut && !isTransferring && !hasLeave && currentUser.shop.lng"
					:color="inTime?'green':'red'">
				<Button :type="inTime?'success':'error'" size="large" shape="circle" @click="uploadClock">
					<h3 style="display:inline;">&nbsp;{{hasClockIn ? '下班打卡' : '上班打卡'}}&nbsp;</h3>
					<p style="display:inline;">{{curTime}}</p>
				</Button>
				<p>
					<Icon type="location"/>
					{{aLocation.formattedAddress}}
				</p>
			</Timeline-item>
			<Timeline-item v-else-if="clockAvailable && hasLeave && currentUser.shop.lng" color="orange">
				<Button type="warning" size="large" shape="circle" @click="uploadLeaveClock">
					<h3 style="display:inline;">&nbsp;{{isLeaving ? '请假结束' : '请假开始'}}&nbsp;</h3>
					<p style="display:inline;">{{curTime}}</p>
				</Button>
				<p>
					<Icon type="location"/>
					{{aLocation.formattedAddress}}
				</p>
			</Timeline-item>
			<Timeline-item v-if="clockAvailable && !isLeaving && hasTransfer ">
				<Button type="primary" size="large" shape="circle" @click="uploadTransferClock">
					<h3 style="display:inline;">&nbsp;{{transfer.status == 1 ? '到达店铺' : '调动出发'}}&nbsp;</h3>
					<p style="display:inline;">{{curTime}}</p>
				</Button>
				<p>
					<Icon type="location"/>
					{{aLocation.formattedAddress}}
				</p>
				<p>至：{{transfer.arriving_shop_name}}</p>
			</Timeline-item>
		</Timeline>
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
    import {Spinner} from 'mint-ui';

    var components = {};
    components[Spinner.name] = Spinner;

    export default {
        data() {
            return {
                clocks: [],		        //打卡信息
                transfer: false,	    //调动信息
                leave: false,		    //请假信息

                locating: false,        //定位中提示
                aLocation: false,	    //定位信息
                locationErr: false,	    //定位失败信息
                today: false,		    //当前的考勤日
                curTime: '12:00:00',    //当前时间
                bigPhoto: false,
            };
        },
        props: ['currentUser', 'date', 'refresh'],
        components: components,
        watch: {
            refresh(newValue) {
                if (newValue == true) {
                    this.getRecord();
                }
            }
        },
        computed: {
            inShop() { //员工是否有所属店铺;
                if (this.currentUser) {
                    return this.currentUser.shop_sn.length > 0;
                } else {
                    return false;
                }
            },
            clockAvailable() { //是否可以打卡
                let avaliableError = true;
                if (this.locationErr &&
                    this.locationErr.match('error message = 取消') == null &&
                    this.locationErr.match('error message = 定位错误') == null) {
                    avaliableError = false;
                }
                return this.date === this.today && this.aLocation !== false && avaliableError;
            },
            inTime() {
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
                    if (this.leave.clock_in_at) {
                        let current = new Date(this.today.replace(/-/g, "/") + ' ' + this.curTime);
                        let clockIn = new Date(this.leave.clock_in_at.replace(/-/g, "/"));
                        return this.leave.clock_out_at && (clockIn > current);
                    } else {
                        return this.leave.clock_out_at;
                    }
                }
                return false;

            },
            /* 状态判断 end */
            hasClockIn: function () { //是否已经打完上班卡
                let clock;
                for (let i in this.clocks) {
                    clock = this.clocks[i];
                    if (clock.type === 1 && clock.shop_sn == this.currentUser.shop_sn) {
                        return true;
                    }
                }
                return false;
            },
            hasClockOut: function () { //是否已经打完下班卡
                let clock;
                for (let i in this.clocks) {
                    clock = this.clocks[i];
                    if (clock.type == 2 && clock.attendance_type == 1) {
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
            this.getRecord();
            this.getCurTime();
            setInterval(this.getCurTime, 1000);
        },
        mounted() {
            this.dingtalkInit();
        },
        methods: {
            getRecord() {
                Indicator.open('加载中...');
                this.getTransferRecord();
                this.getLeaveRecord();
                this.getClockRecord();
                this.$emit('update:refresh', false);
            },
            getClockRecord() {
                let dateStr = this.date ? this.date : null;
                let url = '/clock/info';
                axios.post(url, {date: dateStr, staff_sn: this.currentUser.staff_sn}).then((response) => {
                    this.clocks = response.data.record;
                    this.today = response.data.today;
                    if (!this.date) {
                        this.$emit('update:date', this.today);
                    }
                    Indicator.close();
                });
            },
            getTransferRecord() {
                let url = '/transfer/next';
                axios.post(url, {staff_sn: this.currentUser.staff_sn}).then((response) => {
                    this.transfer = response.data == '' ? false : response.data;
                });
            },
            getLeaveRecord() {
                let url = '/leave/next';
                axios.post(url, {staff_sn: this.currentUser.staff_sn}).then((response) => {
                    this.leave = response.data == '' ? false : response.data;
                });
            },
            getLocation() {
                dd.device.geolocation.get({
                    targetAccuracy: 200,
                    coordinate: 1,
                    withReGeocode: true,
                    useCache: false,
                    onSuccess: (result) => {
                        this.locating = false;
                        try {
                            if (result.location) {
                                result = result.location;
                            }
                            this.aLocation = {
                                position: {
                                    lng: result.longitude,
                                    lat: result.latitude
                                },
                                formattedAddress: result.address ? result.address : '获取地址信息失败，可正常打卡',
                                accuracy: result.accuracy
                            };
                            this.locationErr = false;
                        } catch (e) {
                            this.locationErr = e.message;
                        }

                    },
                    onFail: (err) => {
                        this.locationErr = err.errorMessage;
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
                        staff_sn: this.currentUser.staff_sn,
                        type: clockType,
                        lng: this.aLocation.position.lng,
                        lat: this.aLocation.position.lat,
                        address: this.aLocation.formattedAddress,
                        accuracy: this.aLocation.accuracy,
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
                        this.getRecord();
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
                        staff_sn: this.currentUser.staff_sn,
                        parent_id: leaveID,
                        lng: this.aLocation.position.lng,
                        lat: this.aLocation.position.lat,
                        address: this.aLocation.formattedAddress,
                        accuracy: this.aLocation.accuracy,
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
                        this.getRecord();
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
                        staff_sn: this.currentUser.staff_sn,
                        parent_id: transferID,
                        lng: this.aLocation.position.lng,
                        lat: this.aLocation.position.lat,
                        address: this.aLocation.formattedAddress,
                        accuracy: this.aLocation.accuracy,
                        photo: picPath[0]
                    };
                    axios.post(url, params).then((response) => {
                        if (typeof response.data == 'string') {
                            document.write(response.data);
                        }
                        if (response.data.status) {
                            this.$Message.success(response.data.msg);
                            this.reLogin();
                        } else {
                            this.$Message.error(response.data.msg);
                        }
                        Indicator.close();
                        this.getRecord();
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
                        this.locating = true;
                        this.getLocation();
                        setInterval(this.getLocation, 10000);
                    });
                    dd.error(function (error) {
                        let html = JSON.stringify(error);
                        html += '<h2 onClick="location.reload()" style="text-align:center;margin-top:20px;color:#333;">点此刷新</h2>';
                        document.write(html);
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
            },
            reLogin() {
                Indicator.open('重新登录中...');
                sessionStorage.clear();
                axios('/re_login').then((response) => {
                    sessionStorage.setItem('staff', JSON.stringify(response.data));
                    this.$emit('update:currentUser', response.data);
                    Indicator.close();
                });
            },
        }
    }
</script>
