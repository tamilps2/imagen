<template>
  <div class="card">
    <div v-show="title.length" class="card-header">{{ title }} Options</div>
    <div class="card-body">
      <div class="form-group form-check">
        <input
          v-model="form.generate"
          @change="$emit('should-generate', $event.target.checked)"
          :disabled="disabled"
          type="checkbox"
          class="form-check-input"
        />
        <label class="form-check-label">Generate {{ title }} ?</label>
      </div>
      <!-- Width Height -->
      <div class="form-row">
        <div class="col">
          <label>Width</label>
          <input
            v-model="form.width"
            @input="$emit('width', $event.target.value)"
            :disabled="disabled"
            type="text"
            class="form-control"
            :class="errors[id + '_width'] ? 'is-invalid':''"
            placeholder="Width in px"
          />
          <span v-if="errors[id + '_width']" class="text-danger">{{ errors[id + '_width'].join(', ') }}</span>
        </div>
        <div class="col">
          <label>Height</label>
          <input
            v-model="form.height"
            @input="$emit('height', $event.target.value)"
            :disabled="disabled"
            type="text"
            class="form-control"
            :class="errors[id + '_height'] ? 'is-invalid':''"
            placeholder="Height in px"
          />
          <span v-if="errors[id + '_height']" class="text-danger">{{ errors[id + '_height'].join(', ') }}</span>
        </div>
      </div>
      <!-- Other slot elements -->
      <slot></slot>
      <!-- Add watermark -->
      <div class="form-group form-check mt-3">
        <input
          v-model="form.watermark"
          @change="$emit('add-watermark', $event.target.checked)"
          :disabled="disabled"
          type="checkbox"
          class="form-check-input"
        />
        <label class="form-check-label">Add watermark ?</label>
        <span v-if="errors[id + '_watermark']" class="text-danger">{{ errors[id + '_watermark'].join(', ') }}</span>
      </div>

      <!-- Should upload to ftp server -->
      <div class="form-group form-check mt-3">
        <input
            v-model="form.should_upload"
            @change="$emit('should-upload', $event.target.checked)"
            :disabled="disabled"
            type="checkbox"
            class="form-check-input"
        />
        <label class="form-check-label">Should upload to FTP Server ?</label>
        <span v-if="errors[id + '_should_upload']" class="text-danger">{{ errors[id + '_should_upload'].join(', ') }}</span>
      </div>

      <div class="form-group">
        <label>Company Logo</label>
        <v-select
          v-model="form.company"
          @input="companyHandler"
          :options="pick_list.companies"
          :disabled="disabled"
          :get-option-label="company => company.name"
          :get-option-key="company => company.id"
          :reduce="company => company.id"
          placeholder="Select a company"
        />
        <span v-if="errors[id + '_company_id']" class="text-danger">{{ errors[id + '_company_id'].join(', ') }}</span>
      </div>
      <h5 class="mb-3 mt-4 border-bottom border-light pb-2">Placement</h5>
      <div class="form-row">
        <div class="col">
          <label>Position</label>
          <v-select
            v-model="form.position"
            @input="positionHandler"
            :options="pick_list.positions"
            :disabled="disabled"
            placeholder="Select a position"
          />
          <span v-if="errors[id + '_wm_position']"
                class="text-danger">{{ errors[id + '_wm_position'].join(', ') }}</span>
        </div>
        <div class="col">
          <label>unit</label>
          <v-select
            v-model="form.unit"
            @input="unitHanlder"
            :options="pick_list.units"
            :disabled="disabled"
            placeholder="Select a unit"
          />
          <span v-if="errors[id + '_wm_unit']" class="text-danger">{{ errors[id + '_wm_unit'].join(', ') }}</span>
        </div>
      </div>
      <div ref="watermarkPreview" class="my-4 text-center"></div>
<!--      <watermark-preview v-show="form.position" :position="form.position"/>-->
      <br/>
      <div class="form-row">
        <div class="col">
          <label>X Axis</label>
          <input
            v-model="form.xAxis"
            @change="xAxisHandler"
            :disabled="disabled || form.unit === 'auto'"
            name="posx"
            type="text"
            class="form-control"
            :class="errors[id + '_wm_x_axis'] ? 'is-invalid':''"
          />
          <span v-if="errors[id + '_wm_x_axis']" class="text-danger">{{ errors[id + '_wm_x_axis'].join(', ') }}</span>
        </div>
        <div class="col">
          <label>Y Axis</label>
          <input
            v-model="form.yAxis"
            @change="yAxisHandler"
            :disabled="disabled || form.unit === 'auto'"
            name="posy"
            type="text"
            class="form-control"
            :class="errors[id + '_wm_y_axis'] ? 'is-invalid':''"
          />
          <span v-if="errors[id + '_wm_y_axis']" class="text-danger">{{ errors[id + '_wm_y_axis'].join(', ') }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import WatermarkPreview from "./WatermarkPreview";
  import jQuery from 'jquery';

  const $ = jQuery;

  export default {
    name: 'GenericForm',
    components: {WatermarkPreview},
    props: {
      title: {
        type: String,
      },
      disabled: {
        type: Boolean
      },
      pick_list: {
        type: Object
      },
      width: {
        type: Number | String
      },
      height: {
        type: Number | String
      },
      errors: {
        type: Object
      },
      id: {
        type: String
      },
      preset: {
        type: Object | null
      }
    },

    watch: {
      width: {
        immediate: true,
        handler(width, oldWidth) {
          this.form.width = width;
        }
      },

      height: {
        immediate: true,
        handler(height, oldHeight) {
          this.form.height = height;
        }
      },

      preset: {
        immediate: true,
        handler(preset, oldPreset) {
          if (preset !== null && preset !== undefined) {
            if (this.id === 'sm') {
              this.form.generate = preset.generate_small_image;
              this.form.width = preset.sm_width;
              this.form.height = preset.sm_height;
              this.form.watermark = preset.sm_watermark;
              this.form.should_upload = preset.sm_should_upload;
              this.form.company = preset.sm_company_id;
              this.form.position = preset.sm_wm_position;
              this.form.unit = preset.sm_wm_unit;
              this.form.xAxis = preset.sm_wm_x_axis;
              this.form.yAxis = preset.sm_wm_y_axis;
            } else if (this.id === 'lg') {
              this.form.generate = preset.generate_large_image;
              this.form.width = preset.lg_width;
              this.form.height = preset.lg_height;
              this.form.watermark = preset.lg_watermark;
              this.form.should_upload = preset.lg_should_upload;
              this.form.company = preset.lg_company_id;
              this.form.position = preset.lg_wm_position;
              this.form.unit = preset.lg_wm_unit;
              this.form.xAxis = preset.lg_wm_x_axis;
              this.form.yAxis = preset.lg_wm_y_axis;
            } else if (this.id === 'gif') {
              this.form.generate = preset.generate_gif;
              this.form.width = preset.gif_width;
              this.form.height = preset.gif_height;
              this.form.watermark = preset.gif_watermark;
              this.form.should_upload = preset.gif_should_upload;
              this.form.company = preset.gif_company_id;
              this.form.position = preset.gif_wm_position;
              this.form.unit = preset.gif_wm_unit;
              this.form.xAxis = preset.gif_wm_x_axis;
              this.form.yAxis = preset.gif_wm_y_axis;
            } else if (this.id === 'video') {
              this.form.generate = preset.generate_video;
              this.form.width = preset.video_width;
              this.form.height = preset.video_height;
              this.form.watermark = preset.video_watermark;
              this.form.should_upload = preset.video_should_upload;
              this.form.company = preset.video_company_id;
              this.form.position = preset.video_wm_position;
              this.form.unit = preset.video_wm_unit;
              this.form.xAxis = preset.video_wm_x_axis;
              this.form.yAxis = preset.video_wm_y_axis;
            }
          }
        }
      }

    },

    mounted() {
      console.log(this.id);
    },

    data() {
      return {
        form: {
          generate: false,
          width: '',
          height: '',
          watermark: false,
          should_upload: false,
          company: '',
          position: '',
          unit: '',
          xAxis: '',
          yAxis: ''
        },
        loading: false,
      }
    },

    methods: {
      companyHandler(val) {
        this.$emit('company', val);
        this.generatePreviewImage();
      },
      positionHandler(val) {
        this.$emit('position', val);
        this.generatePreviewImage();
      },
      unitHanlder(val) {
        this.$emit('unit', val);
        this.generatePreviewImage();
      },
      xAxisHandler(event) {
        this.$emit('posx', event.target.value);
        this.generatePreviewImage();
      },
      yAxisHandler(event) {
        this.$emit('posy', event.target.value);
        this.generatePreviewImage();
      },
      generatePreviewImage() {
        if (
          this.form.width == '' &&
          this.form.height == '' &&
          this.form.company == '' &&
          this.form.position == '' &&
          this.form.unit == ''
        ) {
          $(this.$refs.watermarkPreview).html('');
          return;
        }

        if (
          (this.form.unit === 'px' || this.form.unit === 'percent') &&
          (this.form.xAxis === null || this.form.yAxis === null)
        ) {
          $(this.$refs.watermarkPreview).html('<small>Provide axis</small>');
          return;
        }

        axios.get('/presets/preview', {
          params: {
            ...this.form
          },
          responseType: 'arraybuffer'
        }).then((res) => {
          let imageString = new Buffer(res.data, 'binary').toString('base64');
          let img = document.createElement("img");
          img.style = 'max-width: 100%;height: auto;';
          img.src = 'data:image/png;base64, ' + imageString;
          $(this.$refs.watermarkPreview).html(img);
        }).catch((error) => {
          console.log(error);
        });
      }
    }
  }
</script>