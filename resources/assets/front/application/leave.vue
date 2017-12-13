<template>
	<div>
		<Card>
			<Form ref="leaveRequest" :rules="leaveRequestValidator" :model="leaveRequest" :label-width="80"
			      label-position="left">
				<Form-item label="开始时间" @click.native="openDatetimePicker('start_at')" prop="start_at">
					<Input v-model="leaveRequest.start_at" placeholder="请选择时间日期" icon="ios-calendar-outline"
					       readonly></Input>
				</Form-item>
				<Form-item label="结束时间" @click.native="openDatetimePicker('end_at')" prop="end_at">
					<Input v-model="leaveRequest.end_at" placeholder="请选择时间日期" icon="ios-calendar-outline"
					       readonly></Input>
				</Form-item>
				<Form-item label="请假时长">
					<p>{{leaveRequest.duration}}小时</p>
				</Form-item>
				<Form-item label="请假类型" @click.native="openLeaveTypePicker" prop="type_name">
					<Input v-model="leaveRequest.type_name" placeholder="请选择请假类型" readonly></Input>
					<Input v-model="leaveRequest.type_id" v-show="false"></Input>
				</Form-item>
				<Form-item label="请假原因" prop="reason">
					<Input v-model="leaveRequest.reason" type="textarea" :autosize="{minRows:2,maxRows:10}"
					       placeholder="请输入..."></Input>
				</Form-item>
				<Form-item label="附件">
					<upload-img @upload="uploadAttachment" @remove="removeAttachment"></upload-img>
				</Form-item>
				<Row>
					<i-col span="18" offset="3">
						<Button type="primary" @click="submit" long>提交</Button>
					</i-col>
				</Row>
			</Form>
		</Card>
		<mt-popup v-model="bottomPopup" pop-transition="popup-fade" position="bottom" style="width:100%;">
			<mt-picker v-show="activePicker == 'leave_type'" :slots="leaveTypeSlots" value-key="name"
			           @change="changeLeaveType"></mt-picker>
		</mt-popup>
		<!-- startDateTimePicker -->
		<mt-datetime-picker v-model="start_at_picker.date" cancelText="" confirmText="时间" type="date"
		                    :startDate="startDate" :endDate="endDate"
		                    ref="startDatePicker" @confirm="confirmDate"></mt-datetime-picker>
		<mt-datetime-picker v-model="start_at_picker.time" cancelText="" confirmText="确认" type="time"
		                    ref="startTimePicker"></mt-datetime-picker>
		<!-- endDateTimePicker -->
		<mt-datetime-picker v-model="end_at_picker.date" cancelText="" confirmText="时间" type="date"
		                    :startDate="start_at_picker.date" :endDate="endDate"
		                    ref="endDatePicker" @confirm="confirmDate"></mt-datetime-picker>
		<mt-datetime-picker v-model="end_at_picker.time" cancelText="" confirmText="确认" type="time"
		                    ref="endTimePicker"></mt-datetime-picker>
	</div>
</template>
<script>
    import {DatetimePicker, Popup, Picker} from 'mint-ui';
    import uploadImgComponent from './upload_img.vue';

    //@TODO 时间的计算，赋值由watcher改为computed

    let component = {};
    component[DatetimePicker.name] = DatetimePicker;
    component[Popup.name] = Popup;
    component[Picker.name] = Picker;
    component['upload-img'] = uploadImgComponent;
    export default {
        data() {

            let startDate = new Date();
            let endDate = new Date();
            let curDate = startDate.getDate();
            startDate.setDate(curDate - 40);
            endDate.setDate(curDate + 30);

            return {
                leaveRequest: {},
                start_at_picker: {},
                end_at_picker: {},
                leaveRequestValidator: {
                    start_at: [
                        {required: true, message: '开始时间不能为空', trigger: 'change'},
                        {
                            validator: (rule, value, callback) => {
                                let start = new Date(value.replace(/-/g, '/')).getTime();
                                let current = new Date().getTime();
                                let diff = current - start;
                                if (diff > 40 * 24 * 60 * 60 * 1000) {
                                    callback(new Error('开始时间不能早于40天前'));
                                } else {
                                    callback();
                                }
                            }, trigger: 'change'
                        }
                    ],
                    end_at: [
                        {required: true, message: '结束时间不能为空', trigger: 'change'},
                        {
                            validator: (rule, value, callback) => {
                                let start = new Date(this.leaveRequest.start_at.replace(/-/g, '/'));
                                let end = new Date(value.replace(/-/g, '/'));
                                if (start > end) {
                                    callback(new Error('结束时间必须在开始时间之后'));
                                } else {
                                    callback();
                                }
                            }, trigger: 'change'
                        }
                    ],
                    type_name: [{required: true, message: '请选择请假类型', trigger: 'change'}],
                    reason: [
                        {required: true, message: '请输入请假原因', trigger: 'change'},
                        {type: 'string', max: 200, message: '原因不能超过200字', trigger: 'change'}
                    ]
                },
                bottomPopup: false,
                activePicker: false,
                leaveType: {},
                leaveTypeSlots: [],
                startDate: startDate,
                endDate: endDate
            };
        },
        components: component,
        props: ['currentUser'],
        computed: {},
        watch: {
            start_at_picker: {
                handler: function (value) {
                    let year = value.date.getFullYear();
                    let month = (Array(2).join(0) + (value.date.getMonth() + 1)).slice(-2);
                    let day = (Array(2).join(0) + value.date.getDate()).slice(-2);
                    this.leaveRequest.start_at = year + '-' + month + '-' + day + ' ' + value.time;
                },
                deep: true
            },
            end_at_picker: {
                handler: function (value) {
                    let year = value.date.getFullYear();
                    let month = (Array(2).join(0) + (value.date.getMonth() + 1)).slice(-2);
                    let day = (Array(2).join(0) + value.date.getDate()).slice(-2);
                    this.leaveRequest.end_at = year + '-' + month + '-' + day + ' ' + value.time;
                },
                deep: true
            },
            leaveRequest: {
                handler: function (value) {
                    let startDate = new Date(value.start_at.replace(/^(\d{4})\-(\d{2})\-(\d{2}).*$/, '$1/$2/$3'));
                    let endDate = new Date(value.end_at.replace(/^(\d{4})\-(\d{2})\-(\d{2}).*$/, '$1/$2/$3'));
                    let dayBetween = (endDate - startDate) / 3600 / 24 / 1000;

                    let startTimeStr = value.start_at.replace(/^.*(\d{2}):(\d{2})$/, '$1:$2');
                    let endTimeStr = value.end_at.replace(/^.*(\d{2}):(\d{2})$/, '$1:$2');
                    if (this.currentUser.shop) {
                        if (startTimeStr > this.currentUser.working_end_at) {
                            startTimeStr = this.currentUser.working_end_at;
                        } else if (startTimeStr < this.currentUser.working_start_at) {
                            startTimeStr = this.currentUser.working_start_at;
                        }
                        if (endTimeStr > this.currentUser.working_end_at) {
                            endTimeStr = this.currentUser.working_end_at;
                        } else if (endTimeStr < this.currentUser.working_start_at) {
                            endTimeStr = this.currentUser.working_start_at;
                        }
                    }
                    let startTime = new Date('2000/01/01 ' + startTimeStr);
                    let endTime = new Date('2000/01/01 ' + endTimeStr);
                    let hourDiff = (endTime - startTime) / 3600 / 1000;
                    let workingHours = this.currentUser.shop ? this.currentUser.working_hours : 12;
                    value.duration = workingHours * dayBetween + hourDiff;
                },
                deep: true
            }
        },
        beforeMount() {
            this.init();
            axios.get('/leave/get_type').then((response) => {
                this.leaveTypeSlots = [{flex: 1, values: response.data}];
            });
        },
        filters: {
            withoutSeconds: function (value) {
                if (value) {
                    return value.substring(0, 16);
                } else {
                    return '';
                }
            }
        },
        methods: {
            init() {
                let defaultDate = new Date();

                this.leaveRequest = {
                    start_at: '',
                    end_at: '',
                    duration: 0,
                    type_name: '',
                    type_id: '',
                    reason: '',
                    attachments: [],
                };
                this.start_at_picker = {
                    date: defaultDate,
                    time: this.currentUser.shop ? this.currentUser.working_start_at : '9:00'
                };
                this.end_at_picker = {
                    date: defaultDate,
                    time: this.currentUser.shop ? this.currentUser.working_end_at : '21:00'
                };
            },
            openDatetimePicker(column) {
                this.activeInput = column;
                switch (column) {
                    case 'start_at':
                        this.$refs.startDatePicker.open();
                        break;
                    case 'end_at':
                        this.$refs.endDatePicker.open();
                        break;
                }

            },
            confirmDate(date) {
                switch (this.activeInput) {
                    case 'start_at':
                        this.$refs.startTimePicker.open();
                        break;
                    case 'end_at':
                        this.$refs.endTimePicker.open();
                        break;
                }
            },
            openLeaveTypePicker() {
                this.activePicker = 'leave_type';
                this.openPopup('type_name');
            },
            changeLeaveType(picker, values) {
                this.leaveRequest.type_id = values[0].id;
                this.leaveRequest[this.activeInput] = values[0].name;
            },
            openPopup(column) {
                this.activeInput = column;
                this.bottomPopup = true;
            },
            uploadAttachment(file) {
                this.leaveRequest.attachments.push(file.url);
            },
            removeAttachment(file) {
                let attachments = this.leaveRequest.attachments;
                for (let i in attachments) {
                    if (attachments[i] == file.url) {
                        this.leaveRequest.attachments.splice(i, 1);
                        break;
                    }
                }
            },
            submit() {
                this.$refs['leaveRequest'].validate((valid) => {
                    if (valid) {
                        Indicator.open('提交中...');
                        axios.post('/leave/submit', this.leaveRequest).then((response) => {
                            try {
                                if (typeof response.data == 'string') {
                                    document.write(response.data);
                                } else if (response.data.status) {
                                    this.$Message.success(response.data.msg);
                                    this.init();
                                } else if (response.data.msg) {
                                    this.$Message.error(response.data.msg);
                                }
                            } catch (e) {
                                document.write(JSON.stringify(response.data));
                            }
                            Indicator.close();
                        }).catch((error) => {
                            if (error.response) {
                                document.write(error.response.data);
                            } else {
                                document.write(error.message);
                            }
                        });
                    } else {
                        this.$Message.error('表单验证失败!');
                    }
                });
            }
        }
    }
</script>
