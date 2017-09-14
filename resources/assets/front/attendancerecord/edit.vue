<template>
	<div >

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
				<button class="derecbtn hidden-xs" @click="delStaff(item)">----</button>
				<div class="liu-wrap o-hide " >
					<p class="col-xs-12  bg-info p10 mb0 mt20 visible-xs" >{{index}} <button class="fr" @click="delStaff(index)">----</button></p>
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
					</div>
				</div>
			</div>
			<div class="col-xs-12 mt20 li-uploads">
				<img :src="shopInfo.oimg" class="w100p">
				<input type="file" accept="image/jpg,image/jpeg,image/png" multiple @change="upimg"/>
				<button type="button" class="btn btn-info" @click="add()">添加新成员</button>
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
				],
				searchStaffStatus:false,
			}
		},
		mounted(){
			this.initFn();
		},
		methods:{
			initFn(){
				let attendid = sessionStorage.getItem('attendedit');


				Indicator.open('数据加载...');
				let _this = this;
				// let url = '/api/attendance/getshopinfo';
				let url = '/api/attendance/getshopattendinfo';

				let params = {attendid:attendid};
				setTimeout(function(){
					Indicator.close();
				},1500)
				axios.post(url,params).then(function(response){
					log(response.data)
						let staff = response.data.staffData;
						_this.shopInfo.name=response.data.shopInfo.name;
						_this.shopInfo.lister=response.data.shopInfo.manager_name;
						_this.shopInfo.shop_sn=response.data.shopInfo.shop_sn;
						_this.shopInfo.oimg=response.data.shopData.attachment;
						_this.shopInfo.id=response.data.shopData.id;
						staff.forEach(function(val){
							val.realname = val.staff_name;
							_this.staffInfo.push(val);
						})										
					
				})
			},
			add(){
				let _this = this;
				this.searchStaffStatus = true;
			},
			save(){
				let _this = this;
				let url = '/api/attendance/updata';
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
