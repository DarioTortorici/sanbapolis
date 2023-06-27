// Variabili per l'autenticazione e le informazioni sulla telecamera
var username = 'admin';
var password = '123-iSTAR';
var cameraId = 'E4-30-22-3F-CF-65';
var serverAddress = "https://127.0.0.1:7001";
const systemId = 'bcf49919-0ace-4c32-a16c-27eac572fb3f';

// Funzione per ottenere gli stream video in diretta
async function getLiveCams() {
  const url = `https://${systemId}.relay.vmsproxy.com/api/createEvent`;
  const headers = {
    Accept: 'application/json',
    'Content-Type': 'application/json',
    Authorization: 'Bearer vms-61c9e97918c389409a20aa731230d43e-U1xOeOz4RR'
  };
  const data = {
    timestamp: '',
    source: 'live',
    caption: ''
  };

  try {
    const response = await fetch(url, {
      method: 'POST',
      headers,
      body: JSON.stringify(data)
    });

    if (response.ok) {
      const responseData = await response.json();
      console.log('Evento creato con successo:', responseData);
    } else {
      console.error('Errore nella creazione dell\'evento:', response.status);
    }
  } catch (error) {
    console.error('Si è verificato un errore durante la creazione dell\'evento:', error);
  }
}

// Funzione per ottenere gli eventi di registrazione
async function getRecordingEvents() {
  const url = `https://${systemId}.relay.vmsproxy.com/api/createEvent`;
  const headers = {
    Accept: 'application/json',
    'Content-Type': 'application/json',
    Authorization: 'Bearer vms-61c9e97918c389409a20aa731230d43e-U1xOeOz4RR'
  };
  const data = {
    timestamp: '',
    source: 'recording',
    caption: ''
  };

  try {
    const response = await fetch(url, {
      method: 'POST',
      headers,
      body: JSON.stringify(data)
    });

    if (response.ok) {
      const responseData = await response.json();
      console.log('Evento creato con successo:', responseData);
    } else {
      console.error('Errore nella creazione dell\'evento:', response.status);
    }
  } catch (error) {
    console.error('Si è verificato un errore durante la creazione dell\'evento:', error);
  }
}

// Funzione per espandere il video selezionato e modificare il layout
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

// Funzione per combinare data e ora in un oggetto DateTime
function combineDateAndTime(date, time) {
  var dateParts = date.split('-');
  var year = parseInt(dateParts[0]);
  var month = parseInt(dateParts[1]) - 1; // I mesi sono indicizzati da 0 in JavaScript
  var day = parseInt(dateParts[2]);

  var timeParts = time.split(':');
  var hours = parseInt(timeParts[0]);
  var minutes = parseInt(timeParts[1]);

  var combinedDate = new Date(year, month, day, hours, minutes);

  return combinedDate;
}

// Funzione per introdurre un ritardo
function delay(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

// Funzione per pianificare una registrazione
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

// Funzione per ottenere data e ora da un ID
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
