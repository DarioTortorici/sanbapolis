var username = 'admin';
var password = '123-iSTAR';
var cameraId = 'E4-30-22-3F-CF-65';
var serverAddress = "https://127.0.0.1:7001";

// Prendi il flusso di cams live all'apertura della pagina
document.addEventListener("DOMContentLoaded", function () {
  getLiveCams();
});


// Funzione per ottenere i flussi delle telecamere in tempo reale
function getLiveCams() {
  // Chiama l'API Wisenet Wave per ottenere i flussi delle telecamere in tempo reale
  var url = serverAddress + '/api/createEvents?source=live&login=' + username + '&password=' + password;

  fetch(url)
    .then(function (response) {
      if (response.ok) {
        return response.text();
      } else {
        throw new Error('Errore nella richiesta. Codice di stato: ' + response.status);
      }
    })
    .then(function (data) {
      // Gestisci la risposta qui
      var camera1_flow = data;
      console.log('Risposta API:', camera1_flow);
      // Esegui altre azioni con i flussi delle telecamere in tempo reale
    })
    .catch(function (error) {
      console.log('Errore nella richiesta:', error.message);
    });
}

// Funzione per ottenere gli eventi di registrazione
function getRecordingEvents() {
  var url = serverAddress + '/api/createEvents?source=recording&login=' + username + '&password=' + password;

  fetch(url)
    .then(function (response) {
      if (response.ok) {
        return response.json();
      } else {
        throw new Error('Errore nella richiesta. Codice di stato: ' + response.status);
      }
    })
    .then(function (responseData) {
      // Imposto il nuovo URL per il video della Camera 1
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

function scheduleRecording(dateTime) {
  var targetDateTime = new Date(dateTime);

  var currentDateTime = new Date();
  var timeDifference = targetDateTime.getTime() - currentDateTime.getTime();

  if (timeDifference > 0) {
    setTimeout(function() {
      console.log('Avvio registrazione automatica:', targetDateTime);
    }, timeDifference);
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