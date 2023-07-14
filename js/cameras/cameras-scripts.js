// Variabili per l'autenticazione e le informazioni sulla telecamera
var username = 'admin';
var password = '123-iSTAR';
var cameraip = '10.120.0.1';
var profile = "profile2";

document.addEventListener('DOMContentLoaded', function () {
  // Video.js setup
  var player = videojs('camera1');

  // Chiamata a ffmpegLive()
  fetch('../../modals/ffmpeg-sender.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ cameraRTSP: 'rtsp://' + username + ':'.password + '@' + cameraip + '/profile2/media.smp' })
  })
    .catch(function (error) {
      console.error('Errore durante la chiamata a ffmpegLive:', error);
    });

  // URL del DASH generato da ffmpeg
  var manifestURL = 'output.mpd';

  // Inizializza il DASH player
  player.src({
    src: manifestURL,
    type: 'application/dash+xml'
  });

  // Parte il video quando è pronto
  player.on('loadedmetadata', function () {
    player.play();
  });
});
