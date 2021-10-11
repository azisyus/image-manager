<template>
    <div>

        <b-modal @close="cancelRemoteUrlUpload" @cancel="cancelRemoteUrlUpload" @ok="uploadViaRemoteUrl" ref="remote-url-modal" id="remote-url-modal" title="Remote Url">
            <input v-model="remoteUrl" type="text" name="remoteUrl" id="" value="https://lh3.googleusercontent.com/proxy/bQjUA3bwC5NSCHaUWcrI5sbQcOltmi39Z1Fo0MqWqp6vdLwSsvzueIWfZCGvMFhiE2kenLA-dx9TcI883ehqioUSfHcJ0714cCC8k7ax8wL3i_AEhFxnfAIgT-4vIxaRFhrgqJs4JA">
            <!--            <button @click="uploadViaRemoteUrl">LOAD</button>-->
        </b-modal>
        <button  @click="openImportUrlModal">IMPORT FROM URL</button>



        <hr>
        <div style="display:block; border: 1px solid; width:100%; height:30%;" v-cloak @drop.prevent="addFile" @dragover.prevent>
            <div style="text-align: center;" class="mt-5">
                <h2>Files to Upload (Drag them over)</h2>
            </div>
            <input type="file" multiple="multiple" name="files[]" id="file" @change="filesLoadedFromInput">
            <draggable v-model="images"  :component-data="getComponentData()"  draggable=".img-wrapper">
                <div style="border:1px solid black; display:block;" v-for="(im,index) in images" :key="im.identifier" class="img-wrapper">
                    <image-file-box @chooseSpecialImage="chooseSpecialImage" :specialImageTypes="specialImages.map((x)=>x.image)" :index="index"  :initialImageData="im.imgdata" :initialInputData="im.f"   @newimage="uploadImageData" :uploadUrl="uploadUrl"  @ondelete="deleteImageFromList(im.identifier)"  />
                </div>
            </draggable>
        </div>


        <hr>
        <br>
        <br>
        <br>
        <br>
        SPECIAL IMAGES
        <div style="display:block; float:right;" v-for="(s,index) in specialImages" :key="s.identifier" >
            <span>{{s.image.type}}</span>
            <image-file-box  :initialImageData="s.image.image"   />
        </div>

    </div>
</template>

<script>



export default {
    mounted() {
        if(this.fetchfilesfrom)
            this.filesUrl = this.fetchfilesfrom;

        console.log(this.fetchfilesfrom);
        if(this.uploadfilesto)
            this.uploadUrl = this.uploadfilesto;

        if(this.sortfilesto)
            this.sortFilesUrl = this.sortfilesto;

        this.fetchFiles();
        this.fetchSpecialImages();

    },
    methods:{

        cancelRemoteUrlUpload : function() {
            this.remoteUrl = null;
        },

        openImportUrlModal : function() {

            this.$refs['remote-url-modal'].show();

        },
        chooseSpecialImage : function(type,fileName){

            axios.post(this.choosespecialimageurl,{
                'fileName':fileName,
                'type':type,
                csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            }).then((response) => {
                this.fetchSpecialImages();
            }).catch(function(err){
                alert('couldnt choose image');
            });

        },

        fetchSpecialImages : function(){
            axios.get(this.specialimagesurl).then((response) => {

                this.specialImages = response.data.specialImages.map((x)=>{
                    return {
                        identifier:this.globalIdentifier++,
                        image:x,
                    };
                });
            }).catch(function(error){
                console.log(error)
            });
        },
        uploadViaRemoteUrl:function(e){
            e.preventDefault();
            axios.post(this.remoteuploadto,{
                'url':this.remoteUrl,
                csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            }).then((result) => {
                var newImages = result.data.images;
                newImages.map((im) => {
                    this.pushFile(im,null);
                });
                this.remoteUrl = null;
                this.$refs['remote-url-modal'].hide();
            }).catch(function(error){
                alert(error.response.data.error);
            });
        },
        filesLoadedFromInput:async function(event){
            this.addFile(null,event.target.files);
        },

        fetchFiles: async function()
        {
            axios.get(this.filesUrl).then((response) => {
                response.data.map((item) => {
                    this.pushFile(item,null);
                });
            });
        },
        uploadImageData: function(img,index,isUploading){
            this.images[index].imgdata = img;
            this.images[index].isUploading = isUploading;
            console.log(index);
            console.log('parent model update');
        },
        deleteImageFromList:function (f) {
            this.images = this.images.filter((image,index,whole) =>{
                return image.identifier !== f;
            });
        },

        onStartCallback : function(){
            this.beforeDragDropBuf = this.images;
        },

        handleChange : function()
        {
            let anyUploading = this.images.filter((image)=>{
                return image.isUploading;
            });
            if(anyUploading.length)
            {
                //in case of fail use latest state of sort
                alert('wait until all images uploaded');
                this.images = this.beforeDragDropBuf;
                return false;
            }

            let fileNames = this.images.map(function(image){
                return image.imgdata.fileName;
            });
            axios.post(this.sortFilesUrl,{
                'fileNames':fileNames,
                csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            }).then(function(result){
                console.log(result);
            });
        },
        getComponentData() {
            return {
                on: {
                    start: this.onStartCallback,
                    end: this.handleChange,
                },
                attrs:{
                    wrap: true
                },
            };
        },
        addFile(e=null,files=[]) {
            var droppedFiles;
            if(files.length!==0)
                droppedFiles = files;
            else
                droppedFiles = e.dataTransfer.files;
            if(!droppedFiles) return;
            console.log(droppedFiles);
            ([...droppedFiles]).forEach(f => {
                this.pushFile(null,f);
            });

        },
        pushFile : function(imgdata = null,f = null){
            this.images.push({
                imgdata:imgdata,
                f:f,
                identifier:this.globalIdentifier++,
                isUploading:false,
            });
        },
    },
    props:{
        fetchfilesfrom:null,
        uploadfilesto:null,
        sortfilesto:null,
        remoteuploadto:null,
        specialimagesurl:null,
        choosespecialimageurl:null,
    },
    data(){
        return {
            dragIndexes:null,
            file: null,
            images:[],
            globalIdentifier: 0,
            filesUrl:null,
            uploadUrl:null,
            sortFilesUrl:null,
            showModal:false,
            remoteUrl:null,
            specialImages: [],


            beforeDragDropBuf:[],


        };
    },
}
</script>

<style>
.img-wrapper
{
    float:left;
}
.img-inputer__inputer
{
    display: none !important;
}
</style>
