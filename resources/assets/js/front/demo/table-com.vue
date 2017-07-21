<template>
	<div class="table2">
		<!-- <table-com :tableTh="thdata" :tableTr="trdata"></table-com> --> 
		<!-- <table-com :tableData="tabledata"></table-com> -->
		<!-- <editor :inputContent="shtml"></editor> -->
		<input type="text" v-model="indata">
		<button @click="save">save</button>
	</div>

</template>
<script>
	import tableCom from './table.vue'
	import editor from './editor.vue'
	export default{
		data(){
			return {
				tabledata:{
					th:['标题1','标题2','标题3','标题4','操作'],
					tr:[
						{dd:'s1data',ds:'s2dst',fei:'s3sdklfj',s4:'s1sdf'},
						{dd:'s2data',ds:'s2dst',fei:null,s4:'s2sdf'},
						{dd:'s3data',ds:'s2dst',fei:'s3sdklfj',s4:'s3sdf'}
					]
				},
				shtml:'<span>askldjasld</span>',
				indata:''
			}
		},
		components:{
			tableCom,
			editor,
		},
		methods:{
			save(){
				axios.post('/valisave',{indata:this.indata}).then(function(response){
					log(response.data);
				}).catch(function (error) {
					log('出错了');
				});
				// log(111)
			}
		}
	}
</script>