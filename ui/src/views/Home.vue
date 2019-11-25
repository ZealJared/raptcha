<template>
  <form class="card" style="width: 320px;" method="post" @submit.prevent="submit">
    <div class="card-img-top bg-dark overflow-hidden" style="padding: 10px;">
      <img :src="imageSrc" @load="imageLoad" :class="{'invisible': !imageLoaded}" :style="'transform: rotate(' + rotation + 'deg);'" width="300" height="300">
    </div>
    <div class="card-body">
      <h5 class="card-title">Rotate To Correct Orientation</h5>
      <input type="hidden" name="challenge_id" :value="challengeId">
      <div class="form-group">
        <label for="rotation">Move the slider to rotate the image.</label>
        <input type="range" class="form-control-range" name="rotation" id="rotation" min="0" max="359" v-model="rotation">
      </div>
      <div class="text-center">
        <button class="btn btn-primary" type="submit" :disabled="!!success">Okay, that looks right!</button>
      </div>
    </div>
    <div v-if="error || success" class="card-footer">
      <div v-if="error" class="alert alert-danger mb-0">
        {{ error }}
      </div>
      <div v-if="success" class="alert alert-success mb-0">
        {{ success }}
      </div>
    </div>
  </form>
</template>

<script>
export default {
  data () {
    return {
      imageLoaded: false,
      success: null,
      error: null,
      imageSrc: '',
      challengeId: '',
      rotation: 0
    }
  },
  methods: {
    submit (e) {
      this.$api.post('/handle_challenge', new FormData(e.target)).then(data => {
        this.error = null
        if (data.result === 'pass') {
          this.success = 'Good job!'
          // submit form and challenge ID, back-end will check Challenge::wasPassed(challenge_id)
          window.parent.postMessage({
            event: 'pass',
            challenge_id: this.challengeId
          }, '*')
        }
      }).catch(error => {
        this.error = error.message
        this.getChallenge()
      })
    },
    getChallenge () {
      this.$api.get('/get_challenge').then(data => {
        this.imageSrc = data.image_src
        this.challengeId = data.challenge_id
        this.rotation = 0
      })
    },
    imageLoad () {
      this.imageLoaded = true
    }
  },
  mounted () {
    this.getChallenge()
    window.addEventListener('message', e => {
      if (e.data === 'reload') {
        this.error = null
        this.getChallenge()
      }
    })
  }
}
</script>
