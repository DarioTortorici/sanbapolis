var username = 'admin';
var password = '123-iSTAR';
var cameraId = 'E4-30-22-3F-CF-65';
var serverAddress = "https://127.0.0.1:7001";


// Prendi il flusso di cams live all'apertura della pagina
document.addEventListener("DOMContentLoaded", function () {
  integrateVideo()
});


// Function to get live camera streams
function getLiveCams() {
  // Construct the API URL
  var url = `${serverAddress}/api/createEvent?source=live&login=${username}&password=${password}`;

  axios.get(url)
    .then(function (response) {
      if (response.status === 200) {
        return response.data;
      } else {
        throw new Error('Request error. Status code: ' + response.status);
      }
    })
    .then(function (data) {
      // Handle the response here
      var camera1_flow = data;
      console.log('API response:', camera1_flow);
      // Perform additional actions with the live camera streams
    })
    .catch(function (error) {
      console.log('Request error:', error.message);
    });
}

// Function to get recording events
function getRecordingEvents() {
  var url = `${serverAddress}/api/createEvent?source=recording&login=${username}&password=${password}`;

  axios.get(url)
    .then(function (response) {
      if (response.status === 200) {
        return response.data;
      } else {
        throw new Error('Errore nella richiesta. Codice di stato: ' + response.status);
      }
    })
    .then(function (responseData) {
      // Set the new URL for Camera 1 video
      console.log(responseData);
      var camera1Video = document.getElementById('camera1');
      camera1Video.src = responseData;
    })
    .catch(function (error) {
      console.log('Errore nella richiesta:', error.message);
    });
}

// Funzione per espandere il video selezionato e modifcare il layout
function expandVideo(element) {
  var table = element.closest("table");
  var mainRow = table.querySelector(".main-row");
  var subRow = table.querySelector(".sub-row");

  var videosToMove = subRow.querySelectorAll(".video-container");
  for (var i = 0; i < videosToMove.length; i++) {
    if (videosToMove[i].parentNode !== element.parentNode) {
      mainRow.appendChild(videosToMove[i].parentNode);
    }
  }

  mainRow.appendChild(element.parentNode);
  mainRow.classList.add("expanded-row");
}

function combineDateAndTime(date, time) {
  var dateParts = date.split('-');
  var year = parseInt(dateParts[0]);
  var month = parseInt(dateParts[1]) - 1; // Months are zero-based in JavaScript
  var day = parseInt(dateParts[2]);

  var timeParts = time.split(':');
  var hours = parseInt(timeParts[0]);
  var minutes = parseInt(timeParts[1]);

  var combinedDate = new Date(year, month, day, hours, minutes);

  return combinedDate;
}

function delay(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

async function scheduleRecording(dateTime) {
  var targetDateTime = new Date(dateTime);
  var currentDateTime = new Date();

  var timeDifference = targetDateTime.getTime() - currentDateTime.getTime();

  if (timeDifference > 0) {
    await delay(timeDifference);
    console.log('Avvio registrazione automatica:', targetDateTime);
    getRecordingEvents();
  } else {
    console.log('Il momento specificato è già passato:', targetDateTime);
  }
}


function getDateTime(id) {
  jQuery.ajax({
    url: 'http://localhost/calendar/calendar-helper.php?action=get-time',
    type: 'GET',
    dataType: 'json',
    data: {id: id},
    success: function (response) {
      return combineDateAndTime(response.start, response.StartTime);
    },
    error: function (xhr, status, error) {
      console.log(xhr.responseText);
    }
  });
}

function integrateVideo(){
  $.ajax({
    url: serverAddress +"/api/getNonce",
    type: "GET",
    success: function (response) {

        var realm = response.reply.realm;
        var nonce = response.reply.nonce;
        var digest = md5(username + ":" + realm + ":" + password);
        var partial_ha2 = md5("GET" + ":");
        var simplified_ha2 = md5(digest + ":" + nonce + ":" + partial_ha2);
        var authKey = btoa(username + ":" + nonce + ":" + simplified_ha2);

        callback(authKey); // This key can be used in URL now
    }
});

var cameraURL = serverAddress + '/hls/' + cameraId + '.m3u8?lo&auth=' + authKey;
var video = document.getElementById('camera1');
 
if (Hls.isSupported()) {
 
    var hls = new Hls();
    hls.loadSource(cameraURL);
    hls.attachMedia(video);
    hls.on(Hls.Events.MANIFEST_PARSED, function() {
 
        video.play();
    });
}
}