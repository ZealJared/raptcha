export function raptcha (successCallback) {
  const origin = 'http://localhost:8080'
  const iframe = document.createElement('iframe')
  iframe.src = origin
  iframe.frameBorder = 0
  iframe.width = 320
  iframe.height = 580
  const form = document.querySelector('form.raptcha')
  // if form has submit button, place challenge before submit
  const submit = form.querySelector('input[type=submit],button[type=submit]')
  if (submit) {
    form.insertBefore(iframe, submit)
  } else {
    form.appendChild(iframe)
  }
  let challengeIdInput = form.getElementsByClassName('raptchaChallengeId')
  if (challengeIdInput.length > 0) {
    challengeIdInput = challengeIdInput[0]
  } else {
    challengeIdInput = document.createElement('input')
    challengeIdInput.type = 'hidden'
    challengeIdInput.className = 'raptchaChallengeId'
    challengeIdInput.name = 'challenge_id'
    form.appendChild(challengeIdInput)
  }
  window.addEventListener('message', e => {
    if (e.origin === origin && e.data.event === 'pass') {
      challengeIdInput.value = e.data.challenge_id
      successCallback()
    }
  })
}
