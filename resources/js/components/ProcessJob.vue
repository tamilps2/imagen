<template>
  <div class="card">
    <h5 class="card-header text-center">
      Choose the Jobs and their Presets
    </h5>
    <div class="card-body">
      <div class="form-group">
        <label>Jobs</label>
        <v-select
            v-model="chosenJobs"
            :options="jobs"
            :disabled="disabled"
            :multiple=true
            :get-option-label="job => `${job.id} - ${job.title}`"
            :get-option-key="job => job.id"
            :reduce="job => job.id"
        />
        <span class="form-text text-secondary"><b>Note:</b> Only jobs that are currently <b>unprocessed</b> and are <b>not in processing</b> are listed here.</span>
      </div>
      <div class="form-group">
        <label>Presets</label>
        <v-select
            v-model="chosenPresets"
            :options="presets"
            :disabled="disabled"
            :multiple=true
            :get-option-label="preset => preset.name"
            :get-option-key="preset => preset.id"
            :reduce="preset => preset.id"
        />
      </div>
      <div class="card">
        <div class="card-header">Youtube Details</div>
        <div class="card-body">
          <div class="form-group">
            <label for="yt_title">Title</label>
            <input v-model="form.title" type="text" name="yt_title" class="form-control" id="yt_title">
          </div>
          <div class="form-group">
            <label for="yt_description">Description</label>
            <input v-model="form.description" type="text" name="yt_description" class="form-control"
                   id="yt_description">
          </div>
          <div class="form-group">
            <label for="yt_tags">Tags</label>
            <input v-model="form.tags" type="text" name="yt_tags" class="form-control" id="yt_tags">
            <small>Tags are separated by ,</small>
          </div>
          <div class="form-group">
            <label for="yt_visibility">Visibility</label>
            <select v-model="form.visibility" name="yt_visibility" id="yt_visibility" class="form-control">
              <option value="private">Private</option>
              <option value="public">Public</option>
              <option value="unlisted">Unlisted</option>
            </select>
          </div>
          <p class="alert alert-info">Since the Youtube details can be applied to multiple jobs, this will be persisted
            only for this login session.</p>
        </div>
      </div>
      <!-- Progress -->
      <div v-if="progress.total > 0 && progress.total < 100" class="row my-3 justify-content-center">
        <div class="col border rounded bg-light p-3">
          <div class="overall-progress">
            <h5 class="text-center">Overall Progress</h5>
            <progress-bar :progress="progress.total" />
          </div>
          <div v-if="progress.section_total > 0 && progress.section_total < 100" class="section-progress">
              <h5 class="text-center">Section Progress</h5>
              <progress-bar :progress="progress.section_total" background="bg-info" />
          </div>
          <div v-show="(progress.total < 100) && progress.message" class="progress-text">
            <h5 class="text-center">Current status</h5>
            <div class="alert alert-info my-2 text-center">
              {{ progress.message }} {{ progress.total > 90 && progress.total < 100 ? 'Finishing up...' : '' }}
            </div>
          </div>
        </div>
      </div>
      <!-- Actions -->
      <div v-if="loading || message" class="text-center my-3">
        <div v-show="loading" class="text-center my-3">
          <img width="35" height="35" src="/assets/loading.gif"/>
        </div>
        <p class="alert alert-info">{{ message }}</p>
      </div>
      <div class="row my-5">
        <div class="col text-center">
          <button
              :disabled="!canProcess || disabled"
              @click="startProcess"
              type="button"
              class="btn btn-primary"
          >Start Process
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import lodash from 'lodash';
  import ProgressBar from "./ProgressBar";

  const _ = lodash;


  export default {
    name: 'ProcessJob',
    props: {
      jobs: {
        type: Array,
        required: true
      },
      presets: {
        type: Array,
        required: true
      },
      selectedJobs: {
        type: Array
      },
      metaInfo: {
        type: Object
      }
    },

    components: {
      ProgressBar
    },

    watch: {
      selectedJobs: {
        immediate: true,
        handler(jobs, old) {
          if (jobs !== undefined && jobs !== null && jobs.length) {
            _.each(jobs, (job) => {
              if (this.chosenJobs.indexOf(job.id) < 0) {
                this.chosenJobs.push(job.id);
              }
            });
          }
        }
      },

      metaInfo: {
        immediate: true,
        handler(meta, old) {
          this.form = {
            ...this.form,
            ...meta
          };
        }
      }
    },

    computed: {
      canProcess() {
        return (this.chosenJobs.length > 0 && this.chosenPresets.length > 0);
      }
    },

    data() {
      return {
        disabled: false,
        loading: false,
        message: '',
        chosenJobs: [],
        chosenPresets: [],

        progress: {
          total: 0,
          section_total: 0,
          message: '',
          intervalRef: null
        },

        form: {
          title: '',
          description: '',
          tags: '',
          visibility: ''
        }
      }
    },

    methods: {

      startProcess() {
        this.loading = true;
        this.disabled = true;
        this.message = 'Processing please wait...';

        this.initProgressTracker();

        axios.post('/jobs/process', {
          jobs: this.chosenJobs.join(','),
          presets: this.chosenPresets.join(','),
          ...this.form
        }).then((res) => {
          this.loading = false;
          this.disabled = false;
          this.message = 'Processed';
          this.progress.total = 100;
          this.progress.section_total = 100;
          clearInterval(this.progress.intervalRef);
        }).catch((error) => {
          this.loading = false;
          this.disabled = false;
          clearInterval(this.progress.intervalRef);

          if (error.response) {
            this.message = error.response.data.message;
          } else if (error.request) {
            this.message = 'Server is not responding.';
            console.log(error.request);
          } else {
            this.message = 'Error connecting to server.';
            console.log('Error', error.message);
          }
        });
      },

      initProgressTracker() {
        this.progress.total = 1;

        this.progress.intervalRef = setInterval(() => {
          axios.get('/jobs/progress', {
            params: {
              jobs: this.chosenJobs.join(',')
            }
          }).then((res) => {
            let progress = Math.round(res.data.progress);
            let sectionProgress = Math.round(res.data.section_progress);
            // Initial loading buffer
            if (progress === 0) {
              this.progress.total = 1;
            } else {
              this.progress.total = progress;
            }

            if (sectionProgress !== 0) {
              this.progress.section_total = sectionProgress;
            }
            this.progress.message = res.data.message;
          }).catch((error) => {
            clearInterval(this.progress.intervalRef);

            if (error.response) {
              this.progress.message = 'Failed to get progress. ' + error.response.message;
            } else if (error.request) {
              this.progress.message = 'No response from server.';
            } else {
              this.progress.message = error.message;
            }
          });
        }, 2000);
      },
    }
  }
</script>

<style lang="scss">
  @import "vue-select/src/scss/vue-select.scss";
</style>