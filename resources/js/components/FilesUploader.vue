<template>
  <div>
    <div class="row justify-content-center">
      <div class="col">
        <progress-bar :progress="progress"/>
        <div v-if="message.length" class="alert alert-info alert-dismissible fade show">
          <div v-show="loading" class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
          </div>
          {{ message }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      </div>
    </div>

    <vue-dropzone
        ref="dropZoneFilesUploader"
        id="dropZone"
        :options="dropZoneOptions"
        @vdropzone-file-added="fileAdded"
        @vdropzone-removed-file="fileRemoved"
        @vdropzone-success="fileUploaded"
        @vdropzone-error="fileUploadError"
        @vdropzone-queue-complete="uploadsCompleted"
        @vdropzone-total-upload-progress="uploadProgress"
        :useCustomSlot=true
    >
      <div class="text-center">
        <h3 class="text-primary">Drag and drop folders to upload</h3>
      </div>
    </vue-dropzone>
    <!-- Failed images -->
    <div v-if="failed.length" class="row my-4 justify-content-center">
      <div class="col">
        <h4 class="py-2">Failed Image Uploads <small class="text-muted">following images failed to upload</small></h4>
        <div class="list-group">
          <a v-for="(error, index) in failed" :key="index" class="list-group-item list-group-item-action">
            <div class="d-flex w-100 justify-content-between">
              <h5 class="mb-1 text-danger"><span class="text-secondary">File - </span> {{ error.file.name }}</h5>
            </div>
            <div class="text-secondary">Error - <span class="text-danger">{{ error.message }}</span></div>
          </a>
        </div>
      </div>
    </div>
    <progress-bar :progress="progress"/>
    <!-- Actions -->
    <div class="row my-4">
      <div class="col text-center">
        <button v-show="!canProceed" :disabled="disabled" type="button" class="btn btn-primary" @click.prevent="check">
          Upload
        </button>
        <button v-show="canProceed" :disabled="disabled" type="button" class="btn btn-primary" @click.prevent="process">
          Process
        </button>
        <button :disabled="disabled" type="button" class="btn btn-secondary" @click.prevent="resetQueue">
          Clear all Files
        </button>
      </div>
    </div>
  </div>
</template>

<script>
  import vue2Dropzone from 'vue2-dropzone';
  import 'vue2-dropzone/dist/vue2Dropzone.min.css';
  import ProgressBar from "./ProgressBar";

  export default {
    name: "FilesUploader",
    components: {
      ProgressBar,
      VueDropzone: vue2Dropzone
    },

    props: {
      job: {
        type: Object,
      }
    },

    computed: {
      canProceed() {
        return (this.uploaded);
      }
    },

    data() {
      let vm = this;

      return {
        loading: false,
        disabled: false,
        message: '',
        progress: 0,
        uploaded: false,
        folders: [],
        jobs: [],
        failed: [],

        dropZoneOptions: {
          url: '/api/file/upload',
          createImageThumbnails: true,
          maxFiles: 1000,
          clickable: false,
          addRemoveLinks: true,
          autoProcessQueue: false,

          init: function () {
            this.on("sending", function (file, xhr, formData) {
              console.log('sending event', arguments);
              let folderPath = '',
                folder = '';

              if (file !== undefined && file.webkitRelativePath !== undefined && file.webkitRelativePath !== '') {
                folderPath = file.webkitRelativePath;
              } else if (file !== undefined && file.fullPath !== undefined && file.fullPath !== '') {
                folderPath = file.fullPath;
              }

              if (folderPath !== undefined && folderPath !== null) {
                folder = folderPath.split('/')[0];
              }

              if (folder === '') {
                // Skip any file that does not have a parent folder.
                this.removeFile(file);
              } else {
                formData.append("file_folder", folder);
              }
            });
          },
        }
      }
    },

    methods: {
      fileAdded(file) {
        let folderPath = '',
          folder = '';

        if (file !== undefined && file.webkitRelativePath !== undefined && file.webkitRelativePath !== '') {
          folderPath = file.webkitRelativePath;
        } else if (file !== undefined && file.fullPath !== undefined && file.fullPath !== '') {
          folderPath = file.fullPath;
        }

        folder = folderPath.split('/')[0];
        if (folder !== '' && this.folders.indexOf(folder) < 0) {
          this.folders.push(folder);
        }
      },

      fileUploaded(file, response) {
        // console.log(file, response);
        if (response.status && !this.jobs.includes(response.job_id)) {
          this.jobs.push(response.job_id);
        }
      },

      fileUploadError(file, message, xhr) {
        this.failed.push({
          file: file,
          message: xhr.statusText,
          status: 403
        });
      },

      fileRemoved(file, error, xhr) {
        this.message = 'Removing file - ' + file.name;
        this.loading = true;

        axios.post('/api/file/remove', {
          file_name: file.name,
        }).then((res) => {
          this.message = res.data.message;
          this.loading = false;
        }).catch((err) => {
          this.message = 'Could not connect to server, failed to remove file.';
        });
      },

      process() {
        if (this.failed.length > 0) {
          if (!confirm(this.failed.length + ' files could not be uploaded. Do you wish to proceed ?')) {
            this.message = 'Skipping process...';
            return false;
          }
        }

        window.location = window.location.origin + '/jobs/process?jobs=' + this.jobs.join(',');
      },

      uploadProgress(totalUploadProgress, totalBytes, totalBytesSent) {
        this.progress = Math.round(totalUploadProgress);
        this.message = `Total progress ${totalUploadProgress}, total byes - ${totalBytes}, bytes sent - ${totalBytesSent}`;
      },

      uploadsCompleted() {
        this.message = 'Uploads completed.';
        this.uploaded = true;
      },

      resetQueue() {
        if (!confirm('Are you sure to remove all the files and reset upload state ?')) {
          return;
        }

        this.$refs.dropZoneFilesUploader.removeAllFiles();
        this.uploaded = false;
        this.disabled = false;
        this.failed = [];
        this.folders = [];
        this.jobs = [];
      },

      /**
       * Check if the file folders are already preset in the jobs/storage
       */
      check() {
        if (this.$refs.dropZoneFilesUploader.getQueuedFiles().length === 0) {
          this.message = 'No files to upload';
          return;
        }

        this.message = 'Checking upload...';

        axios.post('/api/file/check', {
          file_folders: this.folders
        }).then((res) => {
          console.log('then', res);
          if (res.data.exists) {
            if (confirm('[' + res.data.folders.join(' ') + '] folders already exists, do you wish to overwrite it?')) {
              this.$refs.dropZoneFilesUploader.processQueue();
            } else {
              this.message = 'Upload aborted.';
            }
          } else {
            this.$refs.dropZoneFilesUploader.processQueue();
          }
        }).catch((error) => {
          if (error.response) {
            // The request was made and the server responded with a status code
            // that falls out of the range of 2xx
            let response = error.response.data;
            this.message = response.message;

            console.log(error.response.data);
            console.log(error.response.status);
            console.log(error.response.headers);
          } else if (error.request) {
            // The request was made but no response was received
            // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
            // http.ClientRequest in node.js
            console.log(error.request);
          } else {
            // Something happened in setting up the request that triggered an Error
            console.log('Error', error.message);
          }
          console.log(error.config);
        });
      },
    }
  }
</script>

<style scoped>

</style>
