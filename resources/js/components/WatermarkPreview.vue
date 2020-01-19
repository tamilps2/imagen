<template>
  <div class="row my-3">
    <div class="col">
      <div class="placeholder text-center">
        <img :src="'/placeholders/' + position + '.png'" class="img-thumbnail shadow" />
        <span class="d-block pt-2">Watermark position preview</span>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'WatermarkPreview',
    props: {
      position: {
        required: true
      }
    }
  }
</script>