<template>
	<div>
		<Row>
			<i-col span="8" v-for="item in uploadList">
				<div class="upload-list">
					<template v-if="item.status === 'finished'">
						<img :src="item.url" @click="handleRemove(item)">
					</template>
					<template v-else>
						<Progress v-if="item.showProgress" :percent="item.percentage" hide-info></Progress>
					</template>
				</div>
			</i-col>
			<i-col span="8">
				<div class="upload-button">
					<Upload
							ref="upload" name="tmp_file"
							:show-upload-list="false"
							:on-success="handleSuccess"
							:format="['jpg','jpeg','png']"
							:max-size="4096"
							:on-format-error="handleFormatError"
							:on-exceeded-size="handleMaxSize"
							:before-upload="handleBeforeUpload"
							:headers="{'X-XSRF-TOKEN':readCookie('XSRF-TOKEN')}"
							type="select" action="/file/upload_tmp_file">
						<Icon type="camera" size="20"></Icon>
					</Upload>
				</div>
			</i-col>
		</Row>
	</div>
</template>
<script>
    export default {
        data() {
            return {
                uploadList: []
            }
        },
        methods: {
            handleRemove(file) {
                const fileList = this.$refs.upload.fileList;
                this.$refs.upload.fileList.splice(fileList.indexOf(file), 1);
                this.$emit('remove', file);
            },
            handleSuccess(res, file) {
                file.url = res;
                file.name = res;
                this.$emit('upload', file);
                setTimeout(Indicator.close, 500);
            },
            handleFormatError(file) {
                this.$Message.error('文件格式不正确, 请上传 jpg 或 png 格式的文件');
                setTimeout(Indicator.close, 500);
            },
            handleMaxSize(file) {
                this.$Message.error('文件过大，不能超过4M');
                setTimeout(Indicator.close, 500);
            },
            handleBeforeUpload() {
                const check = this.uploadList.length < 5;
                if (!check) {
                    this.$Message.error('最多上传5个文件');
                } else {
                    Indicator.open('上传中...');
                }
                return check;
            },
            readCookie(name) {
                var match = document.cookie.match(new RegExp('(^|;\\s*)(' + name + ')=([^;]*)'));
                return (match ? decodeURIComponent(match[3]) : null);
            }
        },
        mounted() {
            this.uploadList = this.$refs.upload.fileList;
        }
    }
</script>
<style>
	.upload-list {
		width: 60px;
		height: 60px;
		text-align: center;
		line-height: 60px;
		border: 1px solid transparent;
		border-radius: 4px;
		overflow: hidden;
		background: #fff;
		position: relative;
		box-shadow: 0 1px 1px rgba(0, 0, 0, .2);
		margin-right: 4px;
	}

	.upload-button {
		width: 60px;
		height: 60px;
		text-align: center;
		line-height: 60px;
		border: 1px dotted #999;
		border-radius: 4px;
	}

	.upload-list img {
		width: 100%;
		height: 100%;
	}

</style>