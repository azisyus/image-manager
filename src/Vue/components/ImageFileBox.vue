<template>
    <div>
        {{img.fileName}}<br>
        <img v-if="img.variations" :src="img.variations.zoneThumbnail" alt="" style="width:150px; height:150px; object-fit: scale-down;">
        <span v-if="uploading">{{uploadPercent}}</span>
        <br>
        <button v-if="uploadUrl" :disabled="uploading" @click="destroyImage">Delete</button>
        <button @click="cropstart" :disabled="uploading" >Crop</button>

        <button :disabled="uploading" @click="chooseSpecialImage(s)" v-for="s in specialImageTypes">choose as {{s.title}}</button>


        <b-modal @ok="cropped" ref="cropper-modal" id="cropper-modal" title="Crop">
            <vue-cropper
                ref="cropper"
                :src="cropperImage"
                alt="Source Image"
            >
            </vue-cropper>
        </b-modal>
    </div>
</template>

<script>
import 'cropperjs/dist/cropper.css';
import ajax from '../Ajax';
import VueCropper from 'vue-cropperjs';

export default {
    components: { VueCropper },

    mounted() {
        console.log('mounted');

        if(this.initialImageData)
            this.img = this.initialImageData;


        if(this.initialInputData)
        {
            console.log('there is file to upload');
            this.img.variations.zoneThumbnail = URL.createObjectURL(this.initialInputData);
            this.uploadFile(this.initialInputData,this.uploadUrl);
        }
        else if(!this.initialImageData && !this.initialInputData)
            console.error('you should seed this with an image(file)')


    },

    methods: {

        chooseSpecialImage:function(s){
            this.$emit('chooseSpecialImage',s.type,this.img.fileName);
        },

        cropped:function(c){
            var cropData = this.$refs['cropper'].getData();
            console.log(cropData);
            axios.post(this.img.cropUrl,{
                cropData:{
                    "y": cropData.y,
                    "x": cropData.x,
                    "width": cropData.width,
                    "height": cropData.height,
                    csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                fileName:this.img.fileName,
            }).then((response) => {
                console.log(response);
                this.img = response.data;
                this.$refs['cropper-modal'].hide();
            });
            console.log(cropData);
            this.cropperImage = null;
        },
        //opens crop modal
        cropstart:function(){

            this.$refs['cropper-modal'].show();
            this.cropperImage = this.img.originalSrc;

        },
        // cropstart:function(){
        //     this.$emit('cropstart',this.img);
        // },
        uploadFile: function (file,action) {
            this.imageUploadErrors = null;
            this.uploading = true;
            console.log(file);
            var formData = new FormData();
            formData.append('file',file);
            axios.post(action,formData,{
                headers: {
                    'Content-Type': 'multipart/form-data'
                },
                onUploadProgress: e => this.uploadPercent = ~~e.percent
            }).then((response) => {
                this.uploading = false;
                this.img = response.data;
                this.$emit('newimage',this.img,this.index,this.uploading);
            }).catch((error)=>{
                this.imageUploadErrors =  error.response.data.errors;
                this.uploading = false;
            });
            this.$emit('newimage',this.img,this.index,this.uploading);
        },
        onProgress:function(e,file){

        },
        onSuccess:function(res,file){
            if(!res.success)
            {
                alert(res.error);
                this.$emit('ondelete');
            }
            else
            {
                this.img = res;
                this.$emit('newimage',this.img,this.index,this.uploading);
            }
            console.log(res);
        },
        onError:function(err,file){
            this.$emit('ondelete');
            console.log(err);
        },
        destroyImage : function()
        {
            if(this.imageUploadErrors !== null)
                this.$emit('ondelete');
            else
            {
                axios.post(this.img.deleteUrl,{
                    fileName:this.img.fileName,
                    csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),

                }).then((response) => {
                    // var result = response.data;
                    // console.log(result);
                    // console.log('deleting image: '+this.img.fileName);
                    this.$emit('ondelete');
                }).catch(function(error){
                    // alert(error.response.data.error);
                    console.log(error);
                });
            }
        }
    },

    props:{
        initialImageData:null,
        initialInputData:null,
        uploadUrl:null,
        index:null,
        specialImageTypes:[],
    },
    data(){
        return {
            img:{
                variations:{},
            },



            uploading: false,
            uploadPercent: 0,
            uploaded: false,
            uploadFailed: false,
            globalIdentifier:null,
            cropperImage:null,
            showingImage:null,
            imageUploadErrors:null,


        };
    },
    computed:{

    },
}
</script>
