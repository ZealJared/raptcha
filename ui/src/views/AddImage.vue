<template>
  <form @submit.prevent="submit" class="card" style="width: 320px;">
    <div class="card-img-top bg-dark" style="padding: 10px;">
      <canvas width="300" height="300" ref="canvas"></canvas>
    </div>
    <div class="card-body">
      <h5 class="card-title">Add an image</h5>
      <div class="form-group">
        <div class="custom-file">
          <input type="file" @change="setImage" id="file" class="custom-file-input">
          <label for="file" class="custom-file-label">Choose Image File</label>
        </div>
      </div>
      <div class="form-group">
        <label for="range">Rotate to Correct Orientation</label>
        <input @input="drawImage" type="range" id="range" class="form-control-range" min="0" max="359" value="0" v-model="rotation">
      </div>
      <button class="btn btn-primary" type="submit" :disabled="!image || saved">Save</button>
    </div>
    <div v-if="saved || error" class="card-footer">
      <div v-if="error" class="alert alert-danger mb-0">
        {{ error }}
      </div>
      <div v-if="saved" class="alert alert-success mb-0">
        Image saved!
      </div>
    </div>
  </form>
</template>

<script>
export default {
  data () {
    return {
      rotation: 0,
      image: null,
      saved: false,
      error: null
    }
  },
  methods: {
    drawImage () {
      this.saved = false
      this.error = null
      const canvas = this.$refs.canvas
      const context = canvas.getContext('2d')
      context.imageSmoothingEnabled = true
      context.clearRect(0, 0, canvas.width, canvas.height)
      const angle = this.rotation * (Math.PI / 180)
      context.translate(150, 150)
      context.rotate(angle)
      context.translate(-150, -150)
      // resize to 300px max
      const scaleFactor = this.image.width > this.image.height ? (300 / this.image.height) : (300 / this.image.width)
      context.drawImage(this.image, 0, 0, this.image.width * scaleFactor, this.image.height * scaleFactor)
      context.globalCompositeOperation = 'destination-in'
      context.beginPath()
      context.arc(150, 150, 150, 0, 360 * (Math.PI / 180))
      context.closePath()
      context.fill()
      context.setTransform(1, 0, 0, 1, 0, 0)
      context.globalCompositeOperation = 'source-over'
    },
    setImage (e) {
      this.rotation = 0
      window.createImageBitmap(e.target.files[0]).then(image => {
        this.image = image
        this.drawImage()
      })
    },
    submit () {
      this.$api.post('/add_image', { imageDataUrl: this.$refs.canvas.toDataURL() }).then(response => {
        console.log(response)
        if (response.message === 'Saved') {
          this.saved = true
        }
      }).catch(error => {
        this.error = error.message
      })
    }
  }
}
</script>
