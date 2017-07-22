<template>
	<div class="people">
	<div class="area" >
			<div class="out_shop col-xs-6" @click="workcard">
				<span >上班卡</span>
			</div>
			<div class="go_shop col-xs-6" @click="downcard">
				<span >下班卡</span>
			</div>
		</div>
	</div>
</template>

<script>
	import { Toast,Indicator  } from 'mint-ui';
	export default{
		data(){
			return {
				uersInfo:{}
			}
		},
		components:{},
		methods:{
			workcard(){				
				// Indicator.open('上传中...');
				let url = '/api/sign/save';
				this.uersInfo.type = 'sign_time';
				this.upsave();
				// axios.post(url,this.uersInfo).then(function(response){
				// 	setTimeout(function(){
				// 		Indicator.close();
				// 		Toast(response.data.status)
				// 	},1500)
				// 	log(response.data)
				// })
			},
			downcard(){
				// Indicator.open('上传中...');
				// let url = '/api/sign/save';
				this.uersInfo.type = 'down_time';
				this.upsave();
				// axios.post(url,this.uersInfo).then(function(response){
				// 	setTimeout(function(){
				// 		Indicator.close();
				// 	},1500)
				// })
			},
			upsave(){
				Indicator.open('上传中...');
				let url = '/api/sign/save';				
				axios.post(url,this.uersInfo).then(function(response){
					setTimeout(function(){
						Indicator.close();
						Toast(response.data.msg)
					},1500)
					log(response.data)
				})
			}
		}
	}
 
</script>

<style>
	
	.go_shop,.out_shop{
		position: relative;
		text-align: center;
		padding:40px 0 20px 0;
	}
	.go_shop span,.out_shop span{
		display: inline-block;
		text-align: center;
		width: 100px;
		height: 100px; line-height:100px;
		border-radius: 50%;
		color:#fff;
		background: #f6b45e;
	}
</style>