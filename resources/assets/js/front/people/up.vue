<template>
	<div class="upfile">
		<!-- <p style="width:200px;height:200px;overflow:hidden;"> -->
		<p>
			<input type="file" @change="upfile">
			<img :src="info.imgs" alt="" style="width:100px">
		</p>
		<p>
			<button type="button"  @click="save"> button </button>
		</p>
	</div>

</template>


<script>
	export default{
		data(){
			return {
				info:{
					imgs:null,
				}
			}
		},
		mounted(){
			log(111)
		},
		methods:{
			upfile(e){
				let _this = this;
				let files = e.target.files;
				log(e.target.files)
				let read  = new FileReader();
				read.readAsDataURL(files[0]);
				read.onload = function(e){
					_this.info.imgs = e.target.result;
				}
			},
			save(){
				axios.post('/up',this.info).then(function(response){
					log(response.data)
				})
			}
		}
	}



</script>