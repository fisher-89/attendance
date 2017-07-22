<template>
	<div >
	<!-- 	<div class="col-xs-12 col-sm-9 mb60 hidden-xs">
			<div class="col-sm-2">
				<h5 class="bg-primary text-center p10 mt0 mb0">录入时间</h5>
				<label><input type="date" name="aa" class="spacDate"></label>
			</div>
			<div class="col-sm-2">
				<h5 class="bg-primary text-center p10 mt0 mb0">店铺名称</h5>
				<label><input type="input" name="" ></label>
			</div>
			<div class="col-sm-2">
				<h5 class="bg-primary text-center p10 mt0 mb0">店铺代码</h5>
				<label><input type="input" name="" ></label>
			</div>
			<div class="col-sm-2">
				<h5 class="bg-primary text-center p10 mt0 mb0">制表人</h5>
				<label><input type="input" name=""></label>
			</div>
			<div class="col-sm-2">
				<button type="button" class="btn btn-primary btn-lg">查询数据</button>
			</div>
		</div> -->


		<div class="col-xs-12 col-sm-9 mb15 plr10 pt10 li-radius">
			<div class="col-xs-6 col-sm-2 plr05">
				<h5 class="bg-primary text-center p5 mt0 mb0">制表人</h5>
				<label><input type="input" v-model="shopInfo.lister"></label>
			</div>
			<div class="col-xs-6 col-sm-2 plr05">
				<h5 class="bg-primary text-center p5 mt0 mb0">店铺名称</h5>
				<label><input type="input" v-model="shopInfo.name" readonly></label>
			</div>
			<div class="col-xs-6 col-sm-2 plr05">
				<h5 class="bg-primary text-center p5 mt0 mb0">总业绩</h5>
				<!-- <label><input type="input" v-model="shopInfo.achievement"></label> -->
				<label ><input type="text"  v-model="zhong" readonly></label>
			</div>
			<div class="col-xs-6 col-sm-2 plr05">
				<h5 class="bg-primary text-center p5 mt0 mb0">店铺代码</h5>
				<label><input type="input" v-model="shopInfo.shop_sn" readonly></label>
			</div>			
		</div>

		<!-- 考勤信息-->
		<div class="col-xs-12 col-sm-10 o-hide plr10  o-scroll">
			<div class="button-wrap relative" v-for="item,index in staffInfo">
				<div class="liu-wrap o-hide " >
					<p class="col-xs-12  bg-info p10 mb0 mt20 visible-xs" >{{index}}</p>
					<div class="col-sm-9">
						<div class="col-xs-6 col-sm-1 plr05">
							<h5 class="bg-primary text-center p5 mt0 mb0" >工号</h5>
							<label><input type="input" v-model="item.staff_sn" readonly></label>
						</div>
						<div class="col-xs-6 col-sm-1 plr05">
							<h5 class="bg-primary text-center p5 mt0 mb0">姓名</h5>
							<label><input type="input" name="" readonly v-model="item.realname"> </label>
						</div>
						<div class="col-xs-6 col-sm-1 plr05">
							<h5 class="bg-primary text-center p5 mt0 mb0">货品金额</h5>
							<label><input type="number" v-model="item.achievement"></label>
						</div>
						<div class="col-xs-6 col-sm-1 plr05">
							<h5 class="bg-primary text-center p5 mt0 mb0">合作公司货品金额</h5>
							<label><input type="number" v-model="item.cooperate_money"></label>
						</div>
						<div class="col-xs-6 col-sm-1 plr05">
							<h5 class="bg-primary text-center p5 mt0 mb0">公司购买货品金额</h5>
							<label><input type="number" v-model="item.goods_money"></label>
						</div>
						<!-- <div class="col-xs-6 col-sm-1 plr05">
							<h5 class="bg-primary text-center p5 mt0 mb0">当店职务</h5>
							<select v-model="item.selectJob" class="spacSelect">
								<option>店长</option>
								<option>收银</option>
								<option>导购</option>
							</select>
						</div> -->
						<!-- <div class="col-xs-6 col-sm-1 plr05">
							<h5 class="bg-primary text-center p5 mt0 mb0">职位</h5>
							<label><input type="input" name="" v-model="item.zhiwei"></label>
						</div> -->


						<!-- <div class="col-xs-6 col-sm-1 plr05">
							<h5 class="bg-primary text-center p5 mt0 mb0">状态</h5>
							<select v-model="item.job_status" class="spacSelect">
								<option value="1">正常班</option>
								<option value="0">请假</option>
								<option value="2">调动</option>
								<option value="3">学习</option>
								<option value="-1">旷工</option>
								<option value="4">倒班</option>
								<option value="5">协助开业</option>
							</select>
						</div> -->
					<!-- 	<div class="col-xs-6 col-sm-1 plr05">
							<h5 class="bg-primary text-center p5 mt0 mb0">状态天数</h5>
							<select v-model="item.job_val" class="spacSelect">
								<option value="1">1</option>
								<option value="0.5">0.5</option>
								<option value="-1">-1</option>
								<option value="-0.5">-0.5</option>
							</select>
						</div> -->
						<!-- <div class="col-xs-6 col-sm-1 plr05">
							<h5 class="bg-primary text-center p5 mt0 mb0">团队</h5>
							<label><input type="input" ></label>
						</div> -->
						
						<!-- <div class="col-xs-6 col-sm-2 plr05">
							<h5 class="bg-primary text-center p5 mt0 mb0">出发店铺</h5>
							<label><input type="input" name="" v-model="item.go_shop"></label>
						</div>
						<div class="col-xs-6 col-sm-2 plr05">
							<h5 class="bg-primary text-center p5 mt0 mb0">离店时间</h5>
							<label><input type="datetime-local" v-model="item.go_shop_time" class="spacDate"></label>
						</div> -->
					</div>

					<!-- <div class="col-sm-3">
						<div class="col-xs-6 col-sm-6 plr05">
							<h5 class="bg-primary text-center p5 mt0 mb0">到店店铺</h5>
							<label><input type="input" name="" v-model="item.to_shop"></label>
						</div>
						<div class="col-xs-6 col-sm-6 plr05">
							<h5 class="bg-primary text-center p5 mt0 mb0">到店时间</h5>
							<label><input type="datetime-local" name="" v-model="item.to_shop_time"></label>
						</div>
					</div> -->
				</div>
			</div>
			<div class="col-xs-12 mt20 li-uploads">
				<img :src="shopInfo.oimg" class="w100p">
				<input type="file" accept="image/jpg,image/jpeg,image/png" multiple @change="upimg"/>
				<!-- <button type="button" class="btn btn-info" @click="add()">添加新成员</button> -->
				<button type="button" class="btn btn-success w100" @click="save()">提交</button>
			</div>
		</div>

		<!-- <transition name="fade"><p v-if="searchStaffStatus">sdfsd</p></transition> -->
			<search-staff :isShow="searchStaffStatus"></search-staff>		

		

		<div class="col-xs-4 mt20">
			
		</div>
	</div>
</template>

<script>
	import { Toast,Indicator } from 'mint-ui';
	import searchStaff from '../searchStaff.vue';
	export default{
		data(){
			return {
				shopInfo:{
					// achievement:18181,
					name:'',
					shop_sn:'',
					lister:'',
					oimg:'',
					achievement:0,
					cooperate_money:0,
					goods_money:0,
				},
				staffInfo:[
					// {staff_sn:111966,realname:'刘灰灰',achievement:233},
					// {staff_sn:111963,realname:'刘飞飞',achievement:344}
				],
				searchStaffStatus:false,
			}
		},
		mounted(){
			this.initFn();
			// axios.post(oaurl+'get_shop_info').then(function(response){
			// 	log(response.data);
			// }) 
		},
		methods:{
			initFn(){
				Indicator.open('数据加载...');
				let _this = this;
				let url = '/api/attendance/getshopinfo';
				setTimeout(function(){
					Indicator.close();
				},1500)
				axios.post(url).then(function(response){
					if(response.data.status){
						let staff = response.data.message[0].staff;
						_this.shopInfo.name=response.data.message[0].name;
						_this.shopInfo.lister=response.data.message[0].manager_name;
						_this.shopInfo.shop_sn=response.data.message[0].shop_sn;
						staff.forEach(function(val){
							val.achievement = null;
							val.cooperate_money = null;
							val.goods_money = null;
							_this.staffInfo.push(val);
						})
						
					}else{
						Toast('error');
					}
				})
			},
			add(){
				let _this = this;
				this.searchStaffStatus = true;
				// Indicator.open('数据提交...');
				// this.staffInfo.push({staff_sn:111963,realname:'刘飞飞',achievement:355});
			},
			save(){
				let _this = this;
				let url = '/api/attendance/save';
				let data = {
					shop:this.shopInfo,
					staff:this.staffInfo,
				}
				Indicator.open('数据提交...');
				axios.post(url,data).then(function(response){
					Toast(response.data.msg);
					Indicator.close();
					setTimeout(function(){
						_this.$router.push('/f');
					},3000)
				})
			},
			delStaff(index){
				this.staffInfo.splice(index,1);
			},
			upimg(e){
				let _this = this;
				let file = e.target.files[0];
				let fileread = new FileReader();
				fileread.readAsDataURL(file); 
				fileread.onload = function(s){
					_this.shopInfo.oimg = s.target.result;
				}
			}
		},
		computed:{
			zhong(){
				let total = 0;
				let _this = this;
				this.staffInfo.forEach(function(val){
					total = Number(total) + Number(val.achievement) + Number(val.cooperate_money) + Number(val.goods_money);
				})

				_this.shopInfo.achievement = total;
					// log(_this.total);
				return total;
			}
		},
		components:{
			searchStaff
		}
	}
</script>

<style scoped>
	
</style>