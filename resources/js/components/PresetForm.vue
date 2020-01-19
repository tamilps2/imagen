<template>
  <div class="row justify-content-center">
    <div class="col-md-8 mb-2">
      <h3 class="my-3 text-center">Create Preset</h3>
      <!-- Global Options -->
      <div class="card">
        <div class="card-header">Global Options</div>
        <div class="card-body">
          <div class="form-group">
            <label for="name">Preset Name</label>
            <input
                v-model.trim="form.name"
                :disabled="disabled"
                type="text"
                class="form-control"
                :class="errors.name ? 'is-invalid':''"
                id="name"
                required
            />
            <span v-if="errors.name" class="text-danger">{{ errors.name.join(', ') }}</span>
          </div>

          <div class="form-group">
            <label for="filename">Filename Prefix</label>
            <div class="mt-1 mb-2">
              <div class="form-check form-check-inline">
                <input v-model="form.filename_pattern" class="form-check-input" type="radio"
                       name="inlineRadioOptions" id="original" value="original">
                <label class="form-check-label" for="original">Original</label>
              </div>
              <div class="form-check form-check-inline">
                <input v-model="form.filename_pattern" class="form-check-input" type="radio"
                       name="inlineRadioOptions" id="replace" value="replace">
                <label class="form-check-label" for="replace">Replace</label>
              </div>
              <div class="form-check form-check-inline">
                <input v-model="form.filename_pattern" class="form-check-input" type="radio"
                       name="inlineRadioOptions" id="append" value="append">
                <label class="form-check-label" for="append">Append</label>
              </div>
              <div class="form-check form-check-inline">
                <input v-model="form.filename_pattern" class="form-check-input" type="radio"
                       name="inlineRadioOptions" id="prepend" value="prepend">
                <label class="form-check-label" for="prepend">Prepend</label>
              </div>
            </div>
            <input
                v-model="form.filename"
                :disabled="disabled || form.filename_pattern === 'original'"
                type="text"
                class="form-control"
                :class="errors.filename ? 'is-invalid': ''"
                id="filename"
                required
            />
            <span v-if="errors.filename" class="text-danger">{{ errors.filename.join(', ') }}</span>
            <p class="alert alert-info mt-2" v-show="form.filename_pattern === 'replace'">
              <b>Note:</b> This option will append index values to files to prevent duplication. If filename is "image-", then images generated will be image-01.jpg, image-02.jpg etc.
            </p>
          </div>

        </div>
      </div>
      <br/>
      <generic-form
          id="sm"
          :errors="errors"
          title="Small Image"
          :preset="preset"
          :disabled="disabled"
          :pick_list="pick_list"
          :width="form.sm_width"
          :height="form.sm_height"
          @should-generate="handler('generate_small_image', $event)"
          @add-watermark="handler('sm_watermark', $event)"
          @should-upload="handler('sm_should_upload', $event)"
          @width="handler('sm_width', $event)"
          @height="handler('sm_height', $event)"
          @company-id="handler('sm_company_id', $event)"
          @position="handler('sm_wm_position', $event)"
          @unit="handler('sm_wm_unit', $event)"
          @posx="handler('sm_wm_x_axis', $event)"
          @posy="handler('sm_wm_y_axis', $event)"
      />
      <br/>
      <generic-form
          id="lg"
          :errors="errors"
          title="Large Image"
          :preset="preset"
          :disabled="disabled"
          :pick_list="pick_list"
          :width="form.lg_width"
          :height="form.lg_height"
          @should-generate="handler('generate_large_image', $event)"
          @add-watermark="handler('lg_watermark', $event)"
          @should-upload="handler('lg_should_upload', $event)"
          @width="handler('lg_width', $event)"
          @height="handler('lg_height', $event)"
          @company-id="handler('lg_company_id', $event)"
          @position="handler('lg_wm_position', $event)"
          @unit="handler('lg_wm_unit', $event)"
          @posx="handler('lg_wm_x_axis', $event)"
          @posy="handler('lg_wm_y_axis', $event)"
      />
      <br/>
      <generic-form
          id="gif"
          :errors="errors"
          title="Gif Generation"
          :preset="preset"
          :disabled="disabled"
          :pick_list="pick_list"
          :width="form.gif_width"
          :height="form.gif_height"
          @should-generate="handler('generate_gif', $event)"
          @add-watermark="handler('gif_watermark', $event)"
          @should-upload="handler('gif_should_upload', $event)"
          @width="handler('gif_width', $event)"
          @height="handler('gif_height', $event)"
          @company-id="handler('gif_company_id', $event)"
          @position="handler('gif_wm_position', $event)"
          @unit="handler('gif_wm_unit', $event)"
          @posx="handler('gif_wm_x_axis', $event)"
          @posy="handler('gif_wm_y_axis', $event)"
          @gif-interval="handler('gif_interval', $event)"
      >
        <div class="form-group mt-3">
          <label for="gif_interval">Gif Interval</label>
          <input
              v-model="form.gif_interval"
              :disabled="disabled"
              type="text"
              class="form-control"
              id="gif_interval"
          />
          <span v-if="errors.gif_interval" class="text-danger">{{ errors.filename.join(', ') }}</span>
          <span v-else class="form-text text-muted">
            The time interval between each frames (in seconds)
          </span>
        </div>
      </generic-form>
      <br/>
      <generic-form
          id="video"
          :errors="errors"
          title="Video Generation"
          :preset="preset"
          :disabled="disabled"
          :pick_list="pick_list"
          :width="form.video_width"
          :height="form.video_height"
          @should-generate="handler('generate_video', $event)"
          @add-watermark="handler('video_watermark', $event)"
          @should-upload="handler('video_should_upload', $event)"
          @width="handler('video_width', $event)"
          @height="handler('video_height', $event)"
          @company-id="handler('video_company_id', $event)"
          @position="handler('video_wm_position', $event)"
          @unit="handler('video_wm_unit', $event)"
          @posx="handler('video_wm_x_axis', $event)"
          @posy="handler('video_wm_y_axis', $event)"
          @video-interval="handler('video_interval', $event)"
      >
        <div class="form-group mt-3">
          <label for="video_fps">Video Frame Per Second(FPS)</label>
          <input
              v-model="form.video_fps"
              :disabled="disabled"
              type="text"
              class="form-control"
              id="video_fps"
          />
          <span v-if="errors.video_fps" class="text-danger">{{ errors.filename.join(', ') }}</span>
          <span v-else class="form-text text-muted">
            The video frames per second.
          </span>
        </div>
        <div class="form-group form-check mt-3">
          <input
              v-model="form.upload_to_youtube"
              :disabled="disabled"
              type="checkbox"
              class="form-check-input"
          />
          <span v-if="errors.upload_to_youtube" class="text-danger">{{ errors.upload_to_youtube.join(', ') }}</span>
          <label class="form-check-label">Should upload to Youtube ?</label>
        </div>
      </generic-form>
      <div class="alert alert"
      <div class="row my-4">
        <div class="col text-center">
          <p v-show="message.length" class="alert alert-info">{{ message }}</p>
          <button id="save" @click="save" class="btn btn-primary" :disabled="disabled">
            {{ isUpdate ? 'Update' : 'Save'}}
          </button>
          <button class="btn btn-secondary" onclick="window.history.go(-1); return false;" :disabled="disabled">
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import GenericForm from "./GenericForm";
  import lodash from 'lodash';

  const _ = lodash;

  export default {
    name: "PresetForm",
    components: {
      GenericForm
    },
    props: {
      preset: {
        type: Object,
      }
    },
    data() {
      return {
        disabled: false,
        loading: false,
        message: '',
        errors: {},

        pick_list: {
          companies: [],
          positions: [],
          units: []
        },

        form: {
          id: '',
          name: '',
          filename: "",
          filename_pattern: "",
          // small image
          generate_small_image: false,
          sm_width: 600,
          sm_height: 600,
          sm_watermark: false,
          sm_should_upload: false,
          sm_company_id: "",
          sm_wm_position: "",
          sm_wm_unit: "",
          sm_wm_x_axis: "",
          sm_wm_y_axis: "",
          // large image
          generate_large_image: false,
          lg_width: 1200,
          lg_height: 1200,
          lg_watermark: false,
          lg_should_upload: false,
          lg_company_id: "",
          lg_wm_position: "",
          lg_wm_unit: "",
          lg_wm_x_axis: "",
          lg_wm_y_axis: "",
          // gif settings
          generate_gif: false,
          gif_interval: 3,
          gif_width: 500,
          gif_height: 500,
          gif_watermark: false,
          gif_should_upload: false,
          gif_company_id: "",
          gif_wm_position: "",
          gif_wm_unit: "",
          gif_wm_x_axis: "",
          gif_wm_y_axis: "",
          // video settings.
          generate_video: false,
          video_fps: 24,
          video_width: 1920,
          video_height: 1080,
          video_watermark: false,
          video_should_upload: false,
          upload_to_youtube: false,
          video_company_id: "",
          video_wm_position: "",
          video_wm_unit: "",
          video_wm_x_axis: "",
          video_wm_y_axis: "",
          video_sound: "", // Currently not used.
        }
      };
    },
    watch: {
      preset: {
        immediate: true,
        handler(preset, oldPreset) {
          if (preset !== undefined && preset !== null) {
            this.form = preset;
          }
        }
      }
    },
    computed: {
      isUpdate() {
        return (this.preset !== undefined && this.preset !== null &&
          this.form.id !== undefined && this.form.id !== null);
      }
    },
    created() {
      axios
        .get("/api/pick_list")
        .then(res => {
          console.log(res);
          this.pick_list.companies = res.data.companies;
          this.pick_list.positions = res.data.positions;
          this.pick_list.units = res.data.units;
        })
        .catch(err => {
          console.log(err);
        });
    },

    methods: {
      save() {
        let isUpdate = false;
        this.message = '';
        this.disabled = true;
        this.loading = true;
        this.message = 'Saving, please wait...';

        let url = '/api/presets/add';
        if (this.form.id !== undefined && this.form.id !== null && this.form.id !== '') {
          url = '/api/presets/update/' + this.preset.id;
          isUpdate = true;
        }

        axios.post(url, {
          ...this.form
        }).then((res) => {
          this.disabled = false;
          this.loading = false;
          this.message = '';

          if (res.status) {
            if (isUpdate) {
              this.message = 'All changes have been saved';
            } else {
              window.location = '/presets';
            }
          }
        }).catch((error) => {
          this.disabled = false;
          this.loading = false;

          if (error.response) {
            let data = error.response.data;

            if (error.response.status === 422) {
              this.message = data.message;
              this.errors = data.errors;
            } else {
              this.message = 'Failed to save preset.';
            }
          } else {
            this.message = 'Error connecting to server.';
          }

          console.log(error);
        });
      },

      handler(field, value) {
        if (this.form.hasOwnProperty(field)) {
          this.form[field] = value;
        }
      },
    }
  };
</script>

