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
								￥{{total}}
							</i-col>
							<template v-if="attendanceData.status == -1 && attendanceData.auditor_remark">
								<i-col span="6">
									驳回备注：
								</i-col>
								<i-col span="13">
									{{attendanceData.auditor_remark}}
								</i-col>
							</template>
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
								<i-col span="4">
									<h4 style="line-height:26px;white-space:nowrap;over-flow:hidden;text-overflow:ellipsis;">
										{{staffAttendance.staff_name}}
									</h4>
								</i-col>
								<i-col span="4">
									<small style="color:#999;line-height:26px;">{{staffAttendance.staff_sn}}</small>
								</i-col>
								<i-col span="8">
									<!--<Button :type="staffAttendance.shop_duty_id==1? 'success' :staffAttendance.shop_duty_id==2?'info': 'ghost'"-->
									<!--size="small"-->
									<!--@click="showSheet(index)">-->
									<!--{{staffAttendance.shop_duty.name}}-->
									<!--</Button>-->
									<Tag :color="staffAttendance.shop_duty_id == 1?'green':staffAttendance.shop_duty_id==2?'blue':'default'"
									     @click.native="showSheet(index)">
										{{staffAttendance.shop_duty.name}}
									</Tag>
									<Tag v-if="staffAttendance.is_assistor == 1" color="blue"
									     @click.native="showSheet(index)">协助
									</Tag>
									<Tag v-if="staffAttendance.is_assistor == 2" color="blue"
									     @click.native="showSheet(index)">巡店
									</Tag>
									<Tag v-if="staffAttendance.is_shift == 1" color="yellow"
									     @click.native="showSheet(index)">倒班
									</Tag>
								</i-col>
								<i-col span="8">
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
							           :workingStartAt="staffAttendance.working_start_at"
							           :workingEndAt="staffAttendance.working_end_at"></ClockLine>
						</template>
						<template v-if="staffAttendance.is_missing">
							<Alert type="error">
								<h4 style="text-align:center">请先补签，下拉考勤表刷新</h4>
							</Alert>
						</template>
						<template v-else>
							<!--<Row>-->
							<!--<i-col span="20" offset="1">-->
							<!--<h4>销售业绩</h4>-->
							<!--</i-col>-->
							<!--</Row>-->
							<Row>
								<i-col span="9" style="line-height:24px;" offset="1">
									<small>利鲨货品</small>
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
								<i-col span="9" style="line-height:24px;" offset="1">
									<small>GO货品</small>
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
								<i-col span="9" style="line-height:24px;" offset="1">
									<small>公司购买货品</small>
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
								<i-col span="9" style="line-height:24px;" offset="1">
									<small>合作方货品</small>
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
					<Card>
						<p slot="title">备注</p>
						<i-input v-model="attendanceData.manager_remark" type="textarea"
						         :autosize="{minRows: 2,maxRows: 5}" :maxlength="200"
						         placeholder="备注（不超过200字）" size="small">
						</i-input>
					</Card>
					<div style="margin:10px 20px;">
						<Button v-if="attendanceData.status <= 0" type="primary" long size="large" @click="submit">
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
		<mt-actionsheet
				:actions="shopDutyActions"
				v-model="shopDutySheetVisible">
		</mt-actionsheet>
	</div>
</template>

<script>
  import { Field, Loadmore, Popup, Actionsheet, MessageBox } from 'mint-ui';
  import clockLineComponent from './clockLine.vue';
  import Flatpickr from 'flatpickr';

  const Chinese = require('../../flatpickr/l10ns/zh.js').zh;

  let components = {};
  components[Field.name] = Field;
  components[Loadmore.name] = Loadmore;
  components[Popup.name] = Popup;
  components[Actionsheet.name] = Actionsheet;
  components['ClockLine'] = clockLineComponent;

  export default {
    data() {
      return {
        attendanceRecords: {},
        attendanceData: {
          status: 0,
          details: [],
          manager_remark: ''
        },
        originalAttendanceData: {
          status: 0,
          details: [],
          manager_remark: ''
        },
        date: null,
        statusColor: { '0': 'info', '1': 'warning', '2': 'success', '-1': 'error' },
        searchStaffStatus: false,
        showCalendar: false,
        shopDutyActions: [
          { name: '设为店助', method: this.setShopDutyToAssistant }
        ],
        shopDutySheetVisible: false,
        shopDutyStaffKey: false,
        calandar: false
      }
    },
    props: ['currentUser'],
    components: components,
    computed: {
      total: function () {
        let total = 0;
        let staffAttendance;
        for (let i in this.attendanceData.details) {
          staffAttendance = this.attendanceData.details[i];
          total += parseFloat(staffAttendance.sales_performance_lisha) || 0;
          total += parseFloat(staffAttendance.sales_performance_go) || 0;
          total += parseFloat(staffAttendance.sales_performance_group) || 0;
          total += parseFloat(staffAttendance.sales_performance_partner) || 0;
        }
        return total.toFixed(2);
      }
    },
    beforeMount() {
      this.getAllAttendanceRecords();
      this.getAttendanceRecord();
    },
    mounted() {
      this.calandar = Flatpickr("#calendar", {
        inline: true,
        locale: Chinese,
        defaultDate: 'today',
        maxDate: 'today',
        minDate: new Date().fp_incr(-40),
        onChange: (selectedDates, dateStr) => {
          this.renderCalendar();
          this.date = dateStr;
          this.getAttendanceRecord();
          this.showCalendar = false;
        },
        onReady: () => {
          setTimeout(this.renderCalendar, 500);
        },
        onMonthChange: (selectedDates, dateStr, instance) => {
          Indicator.open('加载中...');
          setTimeout(this.renderCalendar, 500);
        },
        onYearChange: (selectedDates, dateStr, instance) => {
          Indicator.open('加载中...');
          setTimeout(this.renderCalendar, 500);
        },
        onDayCreate: (dObj, dStr, fp, dayElem) => {
          let year = dayElem.dateObj.getFullYear();
          let month = (Array(2).join(0) + (dayElem.dateObj.getMonth() + 1)).slice(-2);
          let date = (Array(2).join(0) + dayElem.dateObj.getDate()).slice(-2);
          dayElem.id = 'calandar-' + year + '-' + month + '-' + date;
        }
      });
    },
    watch: {},
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
      getAllAttendanceRecords() {
        axios.post('/attendance/all').then((response) => {
          this.attendanceRecords = response.data;
        });
      },
      renderCalendar() {
        let cell;
        let date;
        let attendance;
        let html;
        let color;
        let dayContainer = this.calandar.days.getElementsByTagName('span');

        for (let i = 0; i < dayContainer.length; i++) {
          cell = dayContainer[i];
          if (!cell.className.match(/(MonthDay|disabled)/)) {
            date = cell.id.replace(/calandar\-(.*)/, '$1');
            attendance = this.attendanceRecords[date];
            if (attendance) {
              switch (attendance.status) {
                case 0:
                  color = '#2d8cf0';
                  break;
                case 1:
                  color = '#ff9900';
                  break;
                case 2:
                  color = '#19be6b';
                  break;
                case -1:
                  color = '#ed3f14';
                  break;
                default:
                  color = '#999999';
                  break;
              }
            } else {
              color = '#999999';
            }
            html = '<div style="position:absolute;top:0;left:0;border-top:10px solid ' + color +
              ';border-right:10px solid transparent;"></div>';
            cell.innerHTML += html;
          }
        }
        Indicator.close();
      },
      getAttendanceRecord() {
        Indicator.open('加载中...');
        axios.post('/attendance/sheet', { date: this.date }).then((response) => {
          this.attendanceData = response.data;
          this.originalAttendanceData = JSON.parse(JSON.stringify(response.data));
          Indicator.close();
        }).catch((error) => {
          if (error.response) {
            document.write(error.response.data);
          } else {
            document.write(error.message);
          }
        });
      },
      refreshAttendanceRecord() {
        if (this.attendanceData.status > 0) {
          axios.post('/attendance/sheet', { date: this.date }).then((response) => {
            this.attendanceData = response.data;
            this.$refs.loadmore.onTopLoaded();
          }).catch((error) => {
            if (error.response) {
              document.write(error.response.data);
            } else {
              document.write(error.message);
            }
          });
        } else {
          axios.post('/attendance/refresh', this.attendanceData).then((response) => {
            this.attendanceData = response.data;
            this.$refs.loadmore.onTopLoaded();
          }).catch((error) => {
            if (error.response) {
              document.write(error.response.data);
            } else {
              document.write('其他错误:' + error.message);
            }
          });
        }
      },
      submit(skipCheck = false) {
        if (this.attendanceData.is_missing) {
          this.$Message.error('存在漏签，请补全签卡后再提交');
        } else {
          Indicator.open('处理中...');
          let url = '/attendance/submit' + (skipCheck == true ? '?skip_check=1' : '');
          axios.post(url, this.attendanceData).then((response) => {
            if (response.data.status == 1) {
              this.attendanceData = response.data.msg;
              Indicator.close();
              this.$Message.success('提交成功');
            } else if (response.data.status == 0) {
              this.$Message.error(response.data.msg);
              Indicator.close();
            } else if (response.data.status == -1) {
              Indicator.close();
              MessageBox.confirm(response.data.msg, '确认提交')
                .then((action) => {
                  if (action == 'confirm') {
                    this.submit(true);
                  }
                });
            }
          }).catch((error) => {
            document.write(error);
          });
        }
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
      },
      setShopDutyToAssistant() {
        let staffAttendance;
        let originalStaffAttendance;
        for (let i in this.attendanceData.details) {
          staffAttendance = this.attendanceData.details[i];
          originalStaffAttendance = this.originalAttendanceData.details[i];
          if (staffAttendance.shop_duty_id == 2) {
            staffAttendance.shop_duty_id = originalStaffAttendance.shop_duty_id;
            staffAttendance.shop_duty.id = originalStaffAttendance.shop_duty.id;
            staffAttendance.shop_duty.name = originalStaffAttendance.shop_duty.name;
          }
        }
        this.attendanceData.details[this.shopDutyStaffKey].shop_duty_id = 2;
        this.attendanceData.details[this.shopDutyStaffKey].shop_duty.id = 2;
        this.attendanceData.details[this.shopDutyStaffKey].shop_duty.name = '店助';
      },
      setShopDutyToSalesperson() {
        this.attendanceData.details[this.shopDutyStaffKey].shop_duty_id = 3;
        this.attendanceData.details[this.shopDutyStaffKey].shop_duty.id = 3;
        this.attendanceData.details[this.shopDutyStaffKey].shop_duty.name = '导购';
      },
      setShopDutyToManager() {
        this.attendanceData.details[this.shopDutyStaffKey].shop_duty_id = 1;
        this.attendanceData.details[this.shopDutyStaffKey].shop_duty.id = 1;
        this.attendanceData.details[this.shopDutyStaffKey].shop_duty.name = '店长';
      },
      toggleAssistor() {
        let is_assistor = this.attendanceData.details[this.shopDutyStaffKey].is_assistor;
        this.attendanceData.details[this.shopDutyStaffKey].is_assistor = is_assistor ? 0 : 1;
      },
      togglePatrol() {
        let is_assistor = this.attendanceData.details[this.shopDutyStaffKey].is_assistor;
        this.attendanceData.details[this.shopDutyStaffKey].is_assistor = is_assistor ? 0 : 2;
      },
      toggleShift() {
        let is_shift = this.attendanceData.details[this.shopDutyStaffKey].is_shift;
        this.attendanceData.details[this.shopDutyStaffKey].is_shift = is_shift ? 0 : 1;
      },
      showSheet(key) {
        if (this.attendanceData.status <= 0) {
          let staffAttendance = this.attendanceData.details[key];
          let originalAttendanceData = this.originalAttendanceData.details[key];
          this.shopDutyStaffKey = key;
          this.shopDutyActions = [];
          if ([1, 3].indexOf(staffAttendance.shop_duty_id) != -1 && assistantActive.indexOf(this.attendanceData.shop_sn) != -1) {
            this.shopDutyActions.push({ name: '设为店助', method: this.setShopDutyToAssistant });
          } else if (staffAttendance.shop_duty_id == 2 && originalAttendanceData.shop_duty_id !== 1) {
            this.shopDutyActions.push({ name: '设为导购', method: this.setShopDutyToSalesperson });
          } else if (staffAttendance.shop_duty_id == 2 && originalAttendanceData.shop_duty_id == 1) {
            this.shopDutyActions.push({ name: '设为店长', method: this.setShopDutyToManager });
          }
          if (staffAttendance.is_assistor == 0) {
            this.shopDutyActions.push({ name: '协助', method: this.toggleAssistor });
            this.shopDutyActions.push({ name: '巡店', method: this.togglePatrol });
          } else if (staffAttendance.is_assistor == 1) {
            this.shopDutyActions.push({ name: '取消协助', method: this.toggleAssistor });
          } else if (staffAttendance.is_assistor == 2) {
            this.shopDutyActions.push({ name: '取消巡店', method: this.togglePatrol });
          }
          if (staffAttendance.is_shift == 0) {
            this.shopDutyActions.push({ name: '倒班', method: this.toggleShift });
          } else {
            this.shopDutyActions.push({ name: '取消倒班', method: this.toggleShift });
          }
          this.shopDutySheetVisible = true;
        }
      }
    }
  }
</script>