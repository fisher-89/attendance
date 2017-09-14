<template>
	<div>
		<Card>
			<Form ref="leaveRequest" :rules="leaveRequestValidator" :model="leaveRequest" :label-width="80" label-position="left">
				<Form-item label="开始时间" @click.native="openDatetimePicker('start_at')" prop="start_at">
					<Input v-model="leaveRequest.start_at" placeholder="请选择时间日期" icon="ios-calendar-outline" readonly></Input>
				</Form-item>
				<Form-item label="结束时间" @click.native="openDatetimePicker('end_at')" prop="end_at">
					<Input v-model="leaveRequest.end_at" placeholder="请选择时间日期" icon="ios-calendar-outline" readonly></Input>
				</Form-item>
				<Form-item label="请假时长">
					<p>{{leaveRequest.duration}}小时</p>
				</Form-item>
				<Form-item label="请假类型" @click.native="openLeaveTypePicker" prop="type_name">
					<Input v-model="leaveRequest.type_name" placeholder="请选择请假类型" readonly></Input>
					<Input v-model="leaveRequest.type_id" v-show="false"></Input>
				</Form-item>
				<Form-item label="请假原因" prop="reason">
					<Input v-model="leaveRequest.reason" type="textarea" :autosize="{minRows:2,maxRows:10}" placeholder="请输入..."></Input>
				</Form-item>
				<Row>
					<Col span="18" offset="3">
						<Button type="primary" @click="submit" long>提交</Button>
					</Col>
				</Row>
			</Form>
		</Card>
		<mt-popup v-model="bottomPopup" pop-transition="popup-fade" position="bottom" style="width:100%;">
			<mt-picker v-show="activePicker == 'leave_type'" :slots="leaveTypeSlots" value-key="name" @change="changeLeaveType"></mt-picker>
		</mt-popup>
		<mt-datetime-picker cancelText="" confirmText="时间" type="date" :startDate="startDate" ref="startdatepicker" @confirm="confirmDate" ></mt-datetime-picker>
		<mt-datetime-picker cancelText="" confirmText="时间" type="date" :startDate="leaveRequest.start_at==''?startDate:new Date(leaveRequest.start_at)" ref="enddatepicker" @confirm="confirmDate" ></mt-datetime-picker>
		<mt-datetime-picker cancelText="" confirmText="确认" type="time" ref="timepicker" @confirm="confirmTime" ></mt-datetime-picker>

		<Collapse accordion v-infinite-scroll="loadMore"  infinite-scroll-disabled="false" infinite-scroll-immediate-check="true">
			<Panel v-for="(record,key) in records" style="padding:5px 0;">
				<Tag v-if="record.status == 0" type="border" color="blue">审批中</Tag>
				<Tag v-else-if="record.status == 1" type="border" color="green">已通过</Tag>
				<Tag v-else-if="record.status == -1" type="border" color="red">已驳回</Tag>
				<Tag v-else-if="record.status == -2" type="border">已取消</Tag>
				<Tag>{{record.start_at}} - {{record.end_at}}</Tag>
				<Tag>{{record.duration}}小时</Tag>
				<Row slot="content">
					<Col span="22" offset="2">
						<Row v-if="record.status > 0">
							<Col span="10"><h4>{{record.left_at}}</h4></Col>
							<Col span="4"> </Col>
							<Col span="10"><h4>{{record.arrived_at}}</h4></Col>
						</Row>
						<Row>
							<Col span="5"><p>出发日期</p></Col>
							<Col span="1">：</Col>
							<Col span="18"><p>{{record.leaving_date}}</p></Col>
						</Row>
						<Row>
							<Col span="5"><p>开单人</p></Col>
							<Col span="1">：</Col>
							<Col span="18"><p>{{record.maker_name}}({{record.maker_sn}})</p></Col>
						</Row>
						<Row v-if="record.remark">
							<Col span="5"><p>备注</p></Col>
							<Col span="1">：</Col>
							<Col span="18"><p>{{record.remark}}</p></Col>
						</Row>
					</Col>
				</Row>
			</Panel>
		</Collapse>

	</div>
</template>
<script>
	import { DatetimePicker,Popup,Picker } from 'mint-ui';
	let component = {};
	component[DatetimePicker.name] = DatetimePicker;
	component[Popup.name] = Popup;
	component[Picker.name] = Picker;
	export default{
		data(){
			let _this = this;
			let startDate = new Date();
			startDate.setDate(startDate.getDate()-5);
			return {
				records : [],
				loading:false,
				leaveRequest:{
					start_at:'',
					end_at:'',
					duration:0,
					type_name:'',
					type_id:'',
					reason:''
				},
				leaveRequestValidator:{
					start_at:[
					{required:true,message:'开始时间不能为空',trigger:'change'},
					{validator:(rule, value, callback)=>{
						let start = new Date(value).getTime();
						let current = new Date().getTime();
						let diff = current-start;
						if(diff>7*24*60*60*1000){
							callback(new Error('开始时间不能超过当前时间一周前'));
						}else{
							callback();
						}
					},trigger:'change'}
					],
					end_at:[
					{required:true,message:'结束时间不能为空',trigger:'change'},
					{validator:(rule, value, callback)=>{
						let start = new Date(this.leaveRequest.start_at);
						let end = new Date(value);
						if(start>end){
							callback(new Error('结束时间必须在开始时间之后'));
						}else{
							callback();
						}
					},trigger:'change'}
					],
					type_name:[{required:true,message:'请选择请假类型',trigger:'change'}],
					reason:[
					{required:true,message:'请输入请假原因',trigger:'change'},
					{type:'string',max:200,message:'原因不能超过200字',trigger:'change'}
					]
				},
				bottomPopup:false,
				activePicker:false,
				leaveTypeSlots:[],
				tmpDate:'',
				startDate:startDate
			};
		},
		components:component,
		props:['currentUser'],
		computed:{},
		watch:{
			leaveRequest:{
				handler:function(value,oldValue){
					if(value.start_at && value.end_at){
						let start = new Date(value.start_at);
						let end = new Date(value.end_at);
						value.duration = ((end-start)/3600/1000).toFixed(2) || 0;
					}
				},
				deep:true
			}
		},
		beforeMount(){
			axios.get('/api/leave/get_type').then((response)=>{
				this.leaveTypeSlots = [{flex:1,values:response.data}];
			});
		},
		mounted(){
			this.getLeaveRecord();
		},
		methods:{
			openDatetimePicker(column){
				this.activeInput = column;
				switch(column){
					case 'start_at':
					this.$refs.startdatepicker.open();
					break;
					case 'end_at':
					this.$refs.enddatepicker.open();
					break;
				}

			},
			confirmDate(date){
				let year = date.getFullYear();
				let month = (Array(2).join(0) + (date.getMonth()+1)).slice(-2);
				let day = (Array(2).join(0) + date.getDate()).slice(-2);
				this.tmpDate = year+'-'+month+'-'+day;
				this.$refs.timepicker.open();
			},
			confirmTime(time){
				this.leaveRequest[this.activeInput] = this.tmpDate+' '+time;
			},
			openLeaveTypePicker(){
				this.activePicker = 'leave_type';
				this.openPopup('type_name');
			},
			changeLeaveType(picker, values){
				this.leaveRequest.type_id = values[0].id;
				this.leaveRequest[this.activeInput] = values[0].name;
			},
			openPopup(column){
				this.activeInput = column;
				this.bottomPopup = true;
			},
			submit(){
				this.$refs['leaveRequest'].validate((valid) => {
					if (valid) {
						axios.post('/api/leave/submit',this.leaveRequest).then((response)=>{
							if(typeof response.data == 'string'){
								document.write(response.data);
							}
							if(response.data.status){
								this.$Message.success(response.data.msg);
							}else{
								this.$Message.error(response.data.msg);
							}
						});
					} else {
						this.$Message.error('表单验证失败!');
					}
				})
			},
			getLeaveRecord(){
				let _this = this;
				axios.post('/api/leave/record',{take:10}).then(function(response){
					_this.records = response.data;
				})
			},
			loadMore(){
				if(this.loading){
					return false;
				}
				this.loading = true;
				Indicator.open('加载更多...');
				let _this = this;
				axios.post('/api/leave/record',{take:10,skip:this.records.length}).then(function(response){
					if(response.data.length>0){
						_this.records = _this.records.concat(response.data);
					}else{
						Toast('已经是最后了');
					}
					Indicator.close();
					setTimeout(()=>{
						_this.loading = false;
					},1000)
				})
			}
		}
	}
</script>
