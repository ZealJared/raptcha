export default class Api {
  constructor () {
    this.baseUrl = 'https://raptcha-api.zealht.ml'
  }

  request (method, url, data) {
    const options = {
      method: method
    }
    if (data) {
      if (data instanceof window.FormData) {
        options.body = data
      } else {
        options.body = JSON.stringify(data)
      }
    }
    return window.fetch(this.baseUrl + url, options).then(response => {
      return response.text()
    }).then(text => {
      try {
        const json = JSON.parse(text)
        return json
      } catch (e) {
        throw new Error('Received the following non-JSON: ' + text)
      }
    }).then(data => {
      if (data.error) {
        throw new Error(data.error)
      }
      return data
    })
  }

  get (url) {
    return this.request('GET', url)
  }

  post (url, data) {
    return this.request('POST', url, data)
  }
}
