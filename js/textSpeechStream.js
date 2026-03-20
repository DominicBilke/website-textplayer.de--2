/*Simple text-stream wrapper around the WebSpeech SpeechSynthesizer API for Google Chrome.

Usage
TextToSpeechStream.getVoices() // Return all voice objects
var voice = TextToSpeechStream.getVoiceByName("Alex")
 
var synthesizer = new TextToSpeechStream({
  voice: voice,
  pitch: 1,
  rate: 1
})
 
synthesizer.write("Hello world!")
*/

//https://www.npmjs.com/package/text-to-speech-stream?activeTab=code
// ------------------------------------------------------ START t2ss
/* global speechSynthesis, SpeechSynthesisUtterance */

//var inherits = require('inherits')
//var Writable = WritebleStream

inherits(TextToSpeechStream, WritableStream)

function TextToSpeechStream (opts) {
  var self = this
  if (!(self instanceof TextToSpeechStream)) return new TextToSpeechStream(opts)

  Writable.call(self, {
    objectMode: true
  })

  self.voice = opts.voice || null
  self.pitch = opts.pitch || null
  self.rate = opts.rate || null
  self.lang = opts.lang || null
}

TextToSpeechStream.getVoices = function () {
  return speechSynthesis.getVoices()
}

TextToSpeechStream.getVoiceByName = function (name) {
  return TextToSpeechStream.getVoices().filter(function (voice) {
    return voice.name === name
  })[0]
}

TextToSpeechStream.prototype._write = function (chunk, enc, next) {
  var self = this

  var utterance = new SpeechSynthesisUtterance()
  utterance.text = chunk
  utterance.lang = self.lang
  utterance.voice = self.voice
  utterance.pitch = self.pitch
  utterance.rate = self.rate
  speechSynthesis.speak(utterance)
  next()
}

TextToSpeechStream.prototype.resume = function () {
  speechSynthesis.resume()
}

TextToSpeechStream.prototype.pause = function () {
  speechSynthesis.pause()
}

TextToSpeechStream.prototype.cancel = function () {
  speechSynthesis.cancel()
}
