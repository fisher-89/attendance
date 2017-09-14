<template>
	<div class="exl">
		<p>
			<input type="file" @change="upfs">
		</p>
		<button type="button" @click="save"> button </button>
	</div>
</template>

<script>
	export default{
		data(){
			return {
				info:{}
			}
		},
		mounted(){
			log('upfs')
		},
		methods:{
			upfs(e){
				let _this = this;
				let files = e.target.files[0];				
				let info = new FormData();
				info.append("files", files);
				this.info = info;
			},
			save(){
				axios.post('/exl',this.info).then(function(response){
					log(response.data);
				})
			}
		}
	}


</script>