<template>
  <div v-if="progress > 0 && progress < 100" class="progress my-3">
    <div class="progress-bar progress-bar-striped progress-bar-animated" :class="background" role="progressbar"
         :aria-valuenow="progress"
         aria-valuemin="0" aria-valuemax="100" :style="'width: ' + progress + '%'">
      {{ progress }}%
    </div>
  </div>
</template>

<script>
  export default {
    name: 'ProgressBar',
    props: {
      progress: {
        type: Number,
        default: 0
      },

      background: {
        type: String,
        default: ''
      }
    }
  }
</script>