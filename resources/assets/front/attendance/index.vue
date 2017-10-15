<template>
	<div>
		<template v-if="currentUser.shop.lng == null">
			<Alert banner type="error" show-icon>
				店铺未定位
				<template slot="desc">
					请在店铺中点击下方按钮设置店铺坐标
				</template>
			</Alert>
			<div style="margin-top:50px;text-align:center;">
				<Button style="width:150px;height:150px;font-size:30px;"
				        type="info" size="large" shape="circle" icon="location"
				        @click="locate"></Button>
			</div>
		</template>
		<template v-else>
			<mt-loadmore :top-method="refreshAttendanceRecord" ref="loadmore" disabled>
				<Alert banner :type="statusColor[attendanceData.status]">
					<h4>
						{{attendanceData.shop_name}}&nbsp;
						<span style="color:#999;">{{attendanceData.shop_sn}}</span>
					</h4>
					<p>
						<Tag v-if="attendanceData.status == 0" color="blue">未提交</Tag>
						<Tag v-else-if="attendanceData.status == 1" color="yellow">已提交</Tag>
						<Tag v-else-if="attendanceData.status == 2" color="green">已通过</Tag>
						<Tag v-else-if="attendanceData.status == -1" color="red">已驳回</Tag>
						<Tag v-if="attendanceData.is_missing == 1" color="red">漏签</Tag>
						<Tag v-if="attendanceData.is_late == 1" color="red">迟到</Tag>
						<Tag v-if="attendanceData.is_early_out == 1" color="red">早退</Tag>
					</p>

					<h4 slot="desc">
						<Row>
							<Button icon="calendar" type="primary" shape="circle" style="float:right"
							        @click="showCalendar = true"></Button>
							<i-col span="6">
								考勤日期：
							</i-col>
							<i-col span="13">
								{{attendanceData.attendance_date}}
							</i-col>
							<i-col span="6">
								店铺业绩：
							</i-col>
							<i-col span="13">
								￥{{attendanceData.sales_performance}}
							</i-col>
						</Row>

					</h4>
				</Alert>
				<template v-if="typeof attendanceData.details == 'string' ">
					<Alert type="error">
						<h4 style="text-align:center">{{attendanceData.details}}</h4>
					</Alert>
				</template>
				<template v-else>
					<Card v-for="(staffAttendance,index) in attendanceData.details" style="margin:5px 2px;">
						<template slot="title">
							<Row>
								<i-col span="10">
									<p style="line-height:26px;">
										{{staffAttendance.staff_name}}&nbsp;
										<span style="color:#999;">{{staffAttendance.staff_sn}}</span>
									</p>
								</i-col>
								<i-col span="5">
									<Tag :color="staffAttendance.shop_duty_id==1? 'green' : null">
										{{staffAttendance.shop_duty.name}}
									</Tag>
								</i-col>
								<i-col span="9">
									<Tag v-if="staffAttendance.is_leaving" color="yellow">请假</Tag>
									<Tag v-if="staffAttendance.is_transferring > 0" color="blue">调动</Tag>
								</i-col>
							</Row>
							<Row>
								<i-col span="24">
									<Tag v-if="staffAttendance.is_missing" color="red">漏签</Tag>
									<template v-else>
										<Tag v-if="staffAttendance.late_time > 0" color="red">迟到</Tag>
										<Tag v-if="staffAttendance.early_out_time > 0" color="red">早退</Tag>
									</template>
								</i-col>
							</Row>
							<ClockLine v-if="!staffAttendance.is_missing"
							           :clockLog="staffAttendance.clock_log"
							           :workingStartAt="currentUser.working_start_at"
							           :workingEndAt="currentUser.working_end_at"></ClockLine>
						</template>
						<template v-if="staffAttendance.is_missing">
							<Alert type="error">
								<h4 style="text-align:center">请先补签</h4>
							</Alert>
						</template>
						<template v-else>
							<Row>
								<i-col span="10" style="line-height:24px;">
									销售业绩(利鲨)：
								</i-col>
								<i-col span="12">
									<i-input v-model="attendanceData.details[index].sales_performance_lisha"
									         type="number"
									         placeholder="请填写业绩"
									         size="small" @on-focus="clearContent">
										<span slot="prepend">￥</span>
									</i-input>
								</i-col>
							</Row>
							<Row>
								<i-col span="10" style="line-height:24px;">
									销售业绩(GO)：
								</i-col>
								<i-col span="12">
									<i-input v-model="attendanceData.details[index].sales_performance_go"
									         type="number"
									         placeholder="请填写业绩"
									         size="small" @on-focus="clearContent">
										<span slot="prepend">￥</span>
									</i-input>
								</i-col>
							</Row>
							<Row>
								<i-col span="10" style="line-height:24px;">
									销售业绩(公司)：
								</i-col>
								<i-col span="12">
									<i-input v-model="attendanceData.details[index].sales_performance_group"
									         type="number"
									         placeholder="请填写业绩"
									         size="small" @on-focus="clearContent">
										<span slot="prepend">￥</span>
									</i-input>
								</i-col>
							</Row>
							<Row>
								<i-col span="10" style="line-height:24px;">
									销售业绩(合作方)：
								</i-col>
								<i-col span="12">
									<i-input v-model="attendanceData.details[index].sales_performance_partner"
									         type="number"
									         placeholder="请填写业绩"
									         size="small" @on-focus="clearContent">
										<span slot="prepend">￥</span>
									</i-input>
								</i-col>
							</Row>
						</template>
					</Card>
					<div style="margin:10px 20px;">
						<Button v-if="attendanceData.status == 0" type="primary" long size="large" @click="submit">
							<!--:disabled="attendanceData.is_missing == 1">-->
							提交
						</Button>
						<Button v-else-if="attendanceData.status == 1" type="warning" long size="large"
						        @click="withdraw">
							撤回
						</Button>
					</div>
				</template>
			</mt-loadmore>
		</template>
		<mt-popup v-model="showCalendar" style="width:100%;" position="top">
			<div>
				<span id="calendar"></span>
			</div>
		</mt-popup>
	</div>
</template>

<script>
    import {Field, Loadmore, Popup} from 'mint-ui';
    import clockLineComponent from './clockLine.vue';
    import Flatpickr from 'flatpickr';

    const Chinese = require('../../flatpickr/l10ns/zh.js').zh;

    let components = {};
    components[Field.name] = Field;
    components[Loadmore.name] = Loadmore;
    components[Popup.name] = Popup;
    components['ClockLine'] = clockLineComponent;

    export default {
        data() {
            return {
                attendanceData: {
                    status: 0
                },
                date: null,
                statusColor: {'0': 'info', '1': 'warning', '2': 'success', '-1': 'error'},
                searchStaffStatus: false,
                showCalendar: false,
            }
        },
        props: ['currentUser'],
        components: components,
        beforeMount() {
            this.dingtalkInit();
            this.getAttendanceRecord();
        },
        mounted() {
            Flatpickr("#calendar", {
                inline: true,
                locale: Chinese,
                defaultDate: 'today',
                maxDate: 'today',
                onChange: (selectedDates, dateStr) => {
                    this.date = dateStr;
                    this.getAttendanceRecord();
                    this.showCalendar = false;
                }
            });
        },
        watch: {
            attendanceData: {
                handler: (value) => {
                    let sales_performance_lisha = 0;
                    let sales_performance_go = 0;
                    let sales_performance_group = 0;
                    let sales_performance_partner = 0;
                    for (let i in value.details) {
                        let staffAttendance = value.details[i];
                        sales_performance_lisha += parseFloat(staffAttendance.sales_performance_lisha) || 0;
                        sales_performance_go += parseFloat(staffAttendance.sales_performance_go) || 0;
                        sales_performance_group += parseFloat(staffAttendance.sales_performance_group) || 0;
                        sales_performance_partner += parseFloat(staffAttendance.sales_performance_partner) || 0;
                    }
                    value.sales_performance_lisha = sales_performance_lisha;
                    value.sales_performance_go = sales_performance_go;
                    value.sales_performance_group = sales_performance_group;
                    value.sales_performance_partner = sales_performance_partner;
                    value.sales_performance = (sales_performance_lisha +
                        sales_performance_go +
                        sales_performance_group +
                        sales_performance_partner).toFixed(2);
                },
                deep: true
            }
        },
        methods: {
            locate() {
                dd.ready(() => {
                    Indicator.open('定位中...');
                    dd.device.geolocation.get({
                        targetAccuracy: 15,
                        coordinate: 1,
                        withReGeocode: true,
                        useCache: false,
                        onSuccess: (result) => {
                            let position = {
                                lng: result.longitude,
                                lat: result.latitude
                            };
                            this.getAttendanceRecord();
                            axios.post('/attendance/locate', position).then((response) => {
                                Indicator.close();
                                if (response.data.status == 1) {
                                    this.$Message.success('定位成功');
                                } else if (response.data.status == -1) {
                                    this.$Message.error(response.data.message);
                                    position = {
                                        lng: response.data.lng,
                                        lat: response.data.lat
                                    };
                                } else {
                                    document.write(response.data);
                                    return false;
                                }
                                this.currentUser.shop.lng = position.lng;
                                this.currentUser.shop.lat = position.lat;
                                sessionStorage.setItem('staff', JSON.stringify(this.currentUser));
                                axios.get('/re_login');
                            });
                        },
                        onFail: (err) => {
                            document.write(JSON.stringify(err));
                        }
                    });
                });
            },
            getAttendanceRecord() {
                Indicator.open('加载中...');
                axios.post('/attendance/sheet', {date: this.date}).then((response) => {
                    this.attendanceData = response.data;
                    Indicator.close();
                }).catch((error) => {
                    document.write(error);
                });
            },
            refreshAttendanceRecord() {
                if (this.attendanceData.status > 0) {
                    axios.post('/attendance/sheet', {date: this.date}).then((response) => {
                        this.attendanceData = response.data;
                        this.$refs.loadmore.onTopLoaded();
                    }).catch((error) => {
                        document.write(error);
                    });
                } else {
                    axios.post('/attendance/refresh', {date: this.date}).then((response) => {
                        this.attendanceData = response.data;
                        this.$refs.loadmore.onTopLoaded();
                    }).catch((error) => {
                        document.write(error);
                    });
                }
            },
            dingtalkInit() {
                let url = '/js_config';
                axios.post(url, {'current_url': location.href}).then((response) => {
                    let jsConfig = response.data;
                    jsConfig['jsApiList'] = ['biz.util.uploadImageFromCamera', 'device.geolocation.get'];
                    dd.config(jsConfig);

                    dd.error(function (error) {
                        document.write(JSON.stringify(error));
                    });
                });
            },
            submit() {
                Indicator.open('处理中...');
                let url = '/attendance/submit';
                axios.post(url, this.attendanceData).then((response) => {
                    this.attendanceData = response.data;
                    Indicator.close();
                    this.$Message.success('提交成功');
                }).catch((error) => {
                    document.write(error);
                });
            },
            withdraw() {
                Indicator.open('处理中...');
                let url = '/attendance/withdraw';
                axios.post(url, this.attendanceData).then((response) => {
                    this.attendanceData = response.data;
                    Indicator.close();
                    this.$Message.success('撤回成功');
                }).catch((error) => {
                    document.write(error);
                });
            },
            clearContent(e) {
                if (e.target.value == 0) e.target.value = '';
            }
        },
        computed: {}
    }
</script>