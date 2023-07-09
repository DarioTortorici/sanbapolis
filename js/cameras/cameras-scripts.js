// Variabili per l'autenticazione e le informazioni sulla telecamera
var username = 'admin';
var password = '123-iSTAR';
var cameraMAC = 'E4-30-22-3F-CF-65';
var cameraId = '25c54d43-ff6f-02f7-b313-eaf464e41506'
var serverAddress = "https://127.0.0.1:7001";
const systemId = 'bcf49919-0ace-4c32-a16c-27eac572fb3f';


//Avvio pagina

document.addEventListener('DOMContentLoaded', async () => {
  // Passo 1: Autenticazione

  authKey = authenticate()
  videoUrl = generateVideoUrl(authKey);

  const videoElement = document.getElementById('camera1');
  videoElement.src = videoUrl;
  console.log(videoUrl);
  videoElement.play();
});

function authenticate() {
  $.ajax({
    url: `https://${systemId}.relay.vmsproxy.com/api/getNonce`,
    type: "GET",
    success: function (response) {
      var realm = response.realm;
      var nonce = response.nonce;
      var digest = md5(username + ":" + realm + ":" + password);
      var partial_ha2 = md5("GET" + ":");
      var simplified_ha2 = md5(digest + ":" + nonce + ":" + partial_ha2);
      var authKey = btoa(username + ":" + nonce + ":" + simplified_ha2);
      return authKey
    }
  })
}


function generateVideoUrl(authKey) {
  return `https://${systemId}.relay.vmsproxy.com/media/${cameraId}.mp4&auth=${authKey}`;
}


// Funzione per ottenere gli stream video in diretta
async function getLiveStream() {
  const url = `https://${systemId}.relay.vmsproxy.com/media/${cameraId}.mp4`;
  const headers = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'Authorization': 'Bearer vms-61c9e97918c389409a20aa731230d43e-U1xOeOz4RR'
  };

  const data = {
    "user": username,
    "password": password
  };

  try {
    const response = await fetch(url, {
      method: 'GET',
      headers: headers
    });

    if (response.ok) {
      const videoBlob = await response.blob();
      const videoUrl = URL.createObjectURL(videoBlob);
      return videoUrl;
    } else {
      console.error('Errore nella richiesta dello stream:', response.status);
      return null;
    }
  } catch (error) {
    console.error('Si è verificato un errore durante il recupero dello stream:', error);
    return null;
  }
}

async function createSession() {
  const url = `https://${systemId}.relay.vmsproxy.com/rest/v1/login/sessions`;
  const headers = {
    'Content-Type': 'application/json',
  };
  const body = {
    username: username,
    password: password,
    setCookie: true,
  };

  try {
    const response = await fetch(url, {
      method: 'POST',
      headers: headers,
      body: JSON.stringify(body),
    });

    if (response.ok) {
      const sessionData = await response.json();
      const token = sessionData.token;
      const expiresInS = sessionData.expiresInS;
      console.log('Session created successfully');
      console.log('Token:', token);
      console.log('Expires in seconds:', expiresInS);
      return token;
    } else {
      console.error('Failed to create session:', response.status);
    }
  } catch (error) {
    console.error('An error occurred while creating the session:', error);
  }
}


// Funzione per ottenere gli stream video in diretta
async function getLiveCams(token) {
  const url = `https://${systemId}.relay.vmsproxy.com/media/${cameraId}.mp4`;
  const headers = {
    Authorization: 'Bearer vms-61c9e97918c389409a20aa731230d43e-KPMxP7sJBT'
  };

  try {
    const response = await fetch(url, {
      method: 'GET',
      headers: headers
    });

    if (response.ok) {
      const videoElement = document.getElementById('camera1');
      videoElement.src = url;
      videoElement.play();
    } else {
      console.error('Errore nella richiesta dello stream:', response.status);
      return null;
    }
  } catch (error) {
    console.error('Si è verificato un errore durante il recupero dello stream:', error);
    return null;
  }
}

async function prova(token) {
  const url = `https://${systemId}.relay.vmsproxy.com/api/createEvent`;
  const headers = {
    Accept: 'application/json',
    'Content-Type': 'application/json',
    Authorization: "Bearer vms-61c9e97918c389409a20aa731230d43e-F4KgZzgFSm"
  };
  const data = {
    timestamp: '',
    source: 'prova',
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
    data: { id: id },
    success: function (response) {
      return combineDateAndTime(response.start, response.StartTime);
    },
    error: function (xhr, status, error) {
      console.log(xhr.responseText);
    }
  });
}
