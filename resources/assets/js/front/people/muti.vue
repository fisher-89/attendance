<template>
	<div class="mutiple">
		<p>
			<input type="text" v-model="info.uname">
		</p>
		<script id="editor" type="text/plain" style="width:1024px;height:500px;"></script>

		<button @click="up">button</button>
	</div>

</template>

<script>

import '../../uedit/ueditor.config.js';
import '../../uedit/ueditor.all.min.js';
import '../../uedit/lang/zh-cn/zh-cn.js';
// var ue = UE.getEditor('editor');
// window.UEDITOR_HOME_URL || '/uedit/';

export default{
	data(){
		return {
			uedit:null,
			info:{
				uname:'bajibjak'
			}
		}
	},
	mounted(){
		this.uedit = UE.getEditor('editor');
	},
	methods:{
		up(){
			this.info.contents = this.uedit.getContent();
			axios.post('/up',this.info).then(function(response){
				log(response.data);
			})
		}
	},
	destoryed(){
		this.uedit.destory();
	}
}
</script>