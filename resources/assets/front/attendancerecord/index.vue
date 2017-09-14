<template>
	<div class="attendanrecord">
		<table class="w100p">
			<tr>
				<th>店铺名称</th>
				<th>提交时间</th>
				<th>状态</th>
				<th>操作</th>
			</tr>
			<tr v-for="item in listdata">
				<td class="p2">{{item.shop_sn}}</td>
				<td>{{item.submit_time}}</td>
				<td>{{item.status | setStatus}}</td>
				<td v-if="!item.status">
					<!-- <a href="">修改</a> -->
					<button type="button" @click="edit(item.id)">修改</button>
				</td>
				<!-- <td v-else>kdj</td> -->
			</tr>
		</table>
	</div>
</template>

<script>
	import { Toast,Indicator } from 'mint-ui';
	export default{
		mounted(){
			// console.log(11)
			this.init();
		},
		data(){
			return {
				listdata:[],
			}
		},
		methods:{
			init(){
				let _this = this;
				let url = '/api/attendance/getrecordlist';
				// Indicator.open('加载中...');
				axios.get(url).then(function(response){
					log(response.data);
					if(response.data){
						response.data.forEach(function(val){
							_this.listdata.push(val);
						})
					}
					// setTimeout(function(){
					// 	Indicator.close();
					// },1500)
				})
			},
			edit(id){
				sessionStorage.setItem('attendedit',id);
				this.$router.push('/f/attendedit');
			}
		}
	}
</script>