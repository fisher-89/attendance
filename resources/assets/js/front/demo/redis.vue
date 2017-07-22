<template>
	<div class="wrap">
		<label>uname <input type="text" v-model="inpuvar" placeholder="input your uname"></label>
		<p><button @click="sub">button</button></p>
	</div>

</template>

<script>
	
	export default{
		mounted(){
			this.init();
		},
		data(){
			return {
				inpuvar:''
			}
		},
		methods:{
			init(){
				log('a redis demo');
			},
			sub(){
				let url = '/redis';
				let params ={
					uname:this.inpuvar
				}
				axios.post(url,params).then(function(response){
					log(response.data)
				})
				// axios.post(url,params).then(function(response){
				// 	log(response.data);
				// })
			}
		}
	}

</script>