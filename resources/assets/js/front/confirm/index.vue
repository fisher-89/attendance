<template>
	<div class="confirm_wrap">
		<ul class="confirm_ul">
			<li class="p10 o-hide borb_eee" v-for="item in listdata">
				<div class="toshopinfo fl">
					<p>
						{{item.staff_name}}
						
					</p>
					<p>{{item.out_shop_name}}</p>
					<p>
						<span class="">调至</span>
					</p>
					<p>{{item.go_shop_name}}</p>
				</div>
			<!-- 	<div class="relative">
					<span class="outshop" v-if="item.out_shop_time">到店</span>
					
				</div> -->
				<div class="fr relative">
					<button class="h60" @click="save(item,'out_shop_time')" v-if="item.out_shop_sn == shopsn">
						<span>确认离店</span>
					</button>
					<button class="h60" @click="save(item,'go_shop_time')" v-else>
						<span>确认到店</span>
					</button>
					<div class="confirmbtn">
						<span class="cf22" v-if="item.go_shop_time && item.out_shop_time">
							已确认
						</span>
						<span v-else class="cf22">
							未确认
						</span>
					</div>
					
				</div>
			</li>
		</ul>
	</div>
</template>
<script>
	import { Toast,Indicator  } from 'mint-ui';
	export default{
		data(){
			return {
				shopsn:sessionStorage.getItem('shopsn'),
				listdata:null,
			}
		},
		mounted(){
			this.getTransferShop();
		},
		methods:{
			save(item,type){
				axios.post('/api/transfer/confirm',{id:item.id,type:type}).then(function(response){
					log(response.data);
					if(response.data.status){
						item[type] = 123;
					}
					Toast(response.data.msg);
				})
			},
			getTransferShop(){
				let _this = this;
				axios.post('/api/transfer/getTransferShop').then(function(response){
					log(response.data);
					_this.listdata = response.data.data;
				})
			}
		}
	}
</script>
<style scoped>
	.outshop{
		display: block;
		position: absolute;
		left:50%;
		top:10px;
		color: red;
	}
	.confirmbtn{
		position: absolute;
		top: 26px;
		left: -60px;
		background: #eee;
		color: #000;
	}
	.toshopinfo>p{
		padding-bottom: 5px;
	}
</style>