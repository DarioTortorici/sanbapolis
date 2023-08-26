// Funzione per ottenere le variabili del server tramite AJAX
function fetchCameraVariables(callback) {
  var xhr = new XMLHttpRequest();
  xhr.open('GET', 'config.php', true);
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4 && xhr.status === 200) {
      var config = JSON.parse(xhr.responseText);
      callback(config);
    }
  };
  xhr.send();
}

// Funzione per inizializzare il video e avviare ffmpeg
function initializeVideoAndFFmpeg(config) {
  var player = videojs('camera1'); // Inizializza il lettore video

  var cameraRTSP = 'rtsp://' + config.username + ':' + config.password + '@' + config.cameraip + '/' + config.profile + '/media.smp';

  // Chiamata a ffmpegLive
  fetch('../../modals/ffmpeg-sender.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ cameraRTSP: cameraRTSP })
  })
  .catch(function (error) {
    console.error('Errore durante la chiamata a ffmpegLive:', error);
  });

  var manifestURL = '../../cameras/live/output.mpd'; // URL del DASH generato da ffmpeg

  // Inizializza il DASH player
  player.src({
    src: manifestURL,
    type: 'application/dash+xml'
  });

  // Avvia il video quando è pronto
  player.on('loadedmetadata', function () {
    player.play();
  });
}

// Esegue la chiamata AJAX per ottenere le variabili e inizializza il video
fetchCameraVariables(initializeVideoAndFFmpeg);

