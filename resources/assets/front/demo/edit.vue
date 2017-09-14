<template>
	<div>
		<quill-editor ref="myTextEditor"
                v-model="content"
                :config="editorOption">
        </quill-editor>
       <button type="button" @click="bu">sdlkfjsdlkfsd</button>
	</div>

	
</template>

<script>
	import { quillEditor } from 'vue-quill-editor'
	export default {
		data () {
			return {
				name: 'app',
				content: '<h2>Example</h2>',
				editorOption: {}
			}
		},
		methods:{
			bu(){
				log(this.content)
			}
		},
		components: { 
			quillEditor
		}
	}
</script>