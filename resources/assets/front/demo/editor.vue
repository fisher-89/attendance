<template>
	<div class="wrap">
		<div id="editor" v-html="inputContent" @input="outputContent"></div>
		<button @click="sub()">button</button>
	</div>
   
</template>

<script>
// import WangEditor from 'wangeditor'
import WangEditor from '../../wangedit/js/wangEditor.js'
export default {
    props: ['inputContent', 'uploadUrl'],
    data() {
        return {
            content: '',
            puyu:''
        }
    },
    computed: {

    },
    mounted() {
        this.createEditor()
        this.inputContent = 'sdlfksdj';
    },
    methods: {
        createEditor() {
            const self = this
            const editor = new WangEditor('editor')
            editor.config.menus = ['source', '|', 'bold', 'underline', 'italic', 'strikethrough', 'eraser', 'forecolor', 'bgcolor', '|', 'quote', 'fontfamily', 'fontsize', 'head', 'unorderlist', 'orderlist', 'alignleft', 'aligncenter', 'alignright','|', 'link', 'unlink', 'table', 'img', 'video', 'insertcode', '|', 'undo', 'redo', 'fullscreen'
            ]
            editor.config.uploadImgUrl = '/api/upfile';
            editor.config.uploadImgFileName = 'upfilename';

            editor.onchange = function() {
                self.formatContent(this.$txt.html())
            }
            editor.create()
            console.log(editor)
        },
        formatContent(content) {
            this.content = content
            this.outputContent()
        },
        outputContent() {
            this.$emit('input', this.content)
        },
        sub(){
        	log( this.content);
        }
    },
    components: {}
}
</script>

<style lang="css" scoped>

    #editor{
        height: 500px;
    }
    .wangEditor-container{
        border-radius: 2px;
        overflow: hidden;
        border: 1px solid #CCC;
    }
</style>
