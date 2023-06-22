const axios = require('axios');

const LOCAL_USER = 'admin'; // local account LOCAL_USER
const LOCAL_PASSWORD = 'pass123'; // local account LOCAL_PASSWORD
const LOCAL_URL = 'https://localhost:7001'; // https://<server_ip>:<sever_port> or https://{system_id}.relay.vmsproxy.com
const CLOUD_USER = 'user@gmail.com'; // cloud account email
const CLOUD_PASSWORD = 'pass123'; // cloud account LOCAL_PASSWORD
const CLOUD_DOMAIN_NAME = 'ync.wavevms.com'; // cloud service domain name
const CLOUD_URL = `https://${CLOUD_DOMAIN_NAME}`;
const deviceId = 'E4-30-22-3F-CF-65';

//RSTP
const options = {
    pos: '',
    resolution: '480p',
    rotation: '',
    codec: 'H264',
    stream: 0,
    speed: '1',
    multiple_payload_types: 'true',
    onvif_replay: true,
    disable_fast_channel_zapping: true
};

//HTTP
format = 'webm';
var httpoptions = {
    resolution: '320x240',
    pos: '123456789',
    endPos: '987654321',
    rotation: '90',
    sfd: true,
    rt: true,
    audio_only: true,
    accurate_seek: true,
    stream: 0,
    duration: 60,
    signature: true,
    utc: true
};


//Download
download_format = 'mkv';
const Download_options = {
    pos: '2015-02-05T19:00',
    duration: 10,
    hi: true
};

// Usage register file
const fileName = 'your_file_name';
const size = 0;
const md5 = '';
const url = 'http://example.com/your_file_url';
const peerPolicy = 'none';

/*
registerFile(fileName, size, md5, url, peerPolicy)
    .then((fileRecord) => {
        // File registered successfully, start using the fileRecord
    })
    .catch((error) => {
        // Error registering file
    });


  // Usage delete file
  deleteDownload(fileName)
    .then(() => {
      // File record deleted successfully
    })
    .catch((error) => {
      // Error deleting file record
    });
  

const archiveFragmentUrl = constructArchiveFragmentUrl(deviceId, download_format, Download_options);
console.log(archiveFragmentUrl);

const httpStreamUrl = constructHTTPStreamUrl(deviceId, format, httpoptions);
console.log(httpStreamUrl);

const rtspUrl = constructRTSPUrl(deviceId, options);
console.log(rtspUrl);
*/

// Helper function to check API response status
function checkStatus(response, verbose) {
    if (response.status === 200) {
        if (verbose) {
            console.log('Request successful');
            console.log(response.data);
        }
        return true;
    }
    console.error(`Request error ${response.status}`);
    console.error(response.data);
    return false;
}

// Helper function to send API requests
async function requestApi(url, uri, method, config) {
    const fullUrl = `${url}${uri}`;
    try {
        const response = await axios.request({
            method,
            url: fullUrl,
            ...config,
        });
        if (!checkStatus(response, false)) {
            process.exit(1);
        }
        return response.data;
    } catch (error) {
        console.error(`Request error ${error.message}`);
        process.exit(1);
    }
}

// Helper function to create the authorization header
function createHeader(bearerToken) {
    return {
        Authorization: `Bearer ${bearerToken}`,
    };
}

// Helper function to print system information
function printSystemInfo(response) {
    const systemInfo = response.reply || response;
    const numberOfServers = systemInfo.length;
    const systemName = systemInfo[0].systemName;
    console.log(`System ${systemName} contains ${numberOfServers} server(s):`);
    console.log(systemInfo);
}

// Local and LDAP Users Authentication
async function authenticateLocalUser() {
    // Step 1: Check user type
    const cloudState = await requestApi(LOCAL_URL, `/rest/v1/login/users/${LOCAL_USER}`, 'GET', {
        verify: false,
    });
    if (!isLocalUser(cloudState)) {
        console.log(`${LOCAL_USER} is not a local user.`);
        process.exit(1);
    }

    // Step 2: Perform login to obtain bearer tokens
    const payload = {
        LOCAL_USER: LOCAL_USER,
        LOCAL_PASSWORD: LOCAL_PASSWORD,
        setCookie: false,
    };

    const primarySession = await requestApi(LOCAL_URL, '/rest/v1/login/sessions', 'POST', {
        verify: false,
        json: payload,
    });
    const primaryToken = primarySession.token;

    const secondarySession = await requestApi(LOCAL_URL, '/rest/v1/login/sessions', 'POST', {
        verify: false,
        json: payload,
    });
    const secondaryToken = secondarySession.token;

    // Step 3: Check primary token expiration
    const primaryTokenInfo = await requestApi(LOCAL_URL, `/rest/v1/login/sessions/${primaryToken}`, 'GET', {
        verify: false,
    });
    if (isExpired(primaryTokenInfo)) {
        console.log('Expired token');
        process.exit(1);
    }

    // Step 4: Perform authenticated API request
    const getMethodHeader = createHeader(primaryToken);
    const systemInfo = await requestApi(LOCAL_URL, '/rest/v1/servers/*/info', 'GET', {
        verify: false,
        headers: getMethodHeader,
    });
    printSystemInfo(systemInfo);

    // Step 5: Terminate secondary session
    const deleteMethodHeader = createHeader(secondaryToken);
    await requestApi(LOCAL_URL, `/rest/v1/login/sessions/${secondaryToken}`, 'DELETE', {
        verify: false,
        headers: deleteMethodHeader,
    });
}

// Helper function to make API requests
async function requestApi(endpoint, method, data = null, params = null) {
    const fullUrl = LOCAL_URL + endpoint;
    const response = await axios({
        method: method,
        url: fullUrl,
        auth: {
            username: LOCAL_USER,
            password: LOCAL_PASSWORD
        },
        data: data,
        params: params,
        validateStatus: false
    });

    return response.data;
}

async function addCamera(cameraIp, cameraUser, cameraPassword) {
    const searchResponse = await requestApi('/api/manualCamera/search', 'GET', {
        start_ip: cameraIp,
        user: cameraUser,
        password: cameraPassword
    });
    const processUuid = searchResponse.reply.processUuid;

    let searchStatusResponse;
    const searchTimeout = 10;
    const startTime = Date.now();
    do {
        searchStatusResponse = await requestApi('/api/manualCamera/status', 'GET', {
            uuid: processUuid
        });

        if (searchStatusResponse.reply.cameras.length > 0) {
            break;
        }

        await new Promise(resolve => setTimeout(resolve, 1000));
    } while ((Date.now() - startTime) < searchTimeout * 1000);

    if (searchStatusResponse.reply.cameras.length === 0) {
        console.log('Timeout exceeded. No camera found.');
        return;
    }

    const camera = searchStatusResponse.reply.cameras[0];

    const addResponse = await requestApi('/api/manualCamera/add', 'POST', {
        user: cameraUser,
        password: cameraPassword,
        cameras: [{
            uniqueId: camera.uniqueId,
            url: camera.url,
            manufacturer: camera.manufacturer
        }]
    });

    console.log('Camera added successfully:', addResponse);

    const stopResponse = await requestApi('/api/manualCamera/stop', 'GET', {
        uuid: processUuid
    });

    console.log('Search process stopped:', stopResponse);
}

async function addRTSPStream(streamUrl, streamUser, streamPassword) {
    const searchResponse = await requestApi('/api/manualCamera/search', 'GET', {
        url: streamUrl,
        user: streamUser,
        password: streamPassword
    });
    const processUuid = searchResponse.reply.processUuid;

    const searchStatusResponse = await requestApi('/api/manualCamera/status', 'GET', {
        uuid: processUuid
    });

    const stream = searchStatusResponse.reply.cameras[0];

    const addResponse = await requestApi('/api/manualCamera/add', 'GET', {
        user: streamUser,
        password: streamPassword,
        uniqueId0: stream.uniqueId,
        url0: stream.url,
        manufacturer0: stream.manufacturer
    });

    console.log('RTSP stream added successfully:', addResponse);

    const stopResponse = await requestApi('/api/manualCamera/stop', 'GET', {
        uuid: processUuid
    });

    console.log('Search process stopped:', stopResponse);
}

// Usage
addCamera('camera_ip', 'camera_user', 'camera_password')
    .then(() => {
        console.log('Camera added successfully');
    })
    .catch(error => {
        console.log('Error adding camera:', error);
    });

addRTSPStream('rtsp_stream_url', 'stream_user', 'stream_password')
    .then(() => {
        console.log('RTSP stream added successfully');
    })
    .catch(error => {
        console.log('Error adding RTSP stream:', error);
    });


function constructRTSPUrl(deviceId, options = {}) {
    const baseUrl = `rtsp://<server_ip>:<port>/${deviceId}`;

    const params = new URLSearchParams();

    if (options.pos) {
        params.append('pos', options.pos);
    }

    if (options.resolution) {
        params.append('resolution', options.resolution);
    }

    if (options.rotation) {
        params.append('rotation', options.rotation);
    }

    if (options.codec) {
        params.append('codec', options.codec);
    }

    if (options.stream !== undefined) {
        params.append('stream', options.stream);
    }

    if (options.speed) {
        params.append('speed', options.speed);
    }

    if (options.multiple_payload_types) {
        params.append('multiple_payload_types', options.multiple_payload_types);
    }

    if (options.onvif_replay) {
        params.append('onvif_replay', options.onvif_replay);
    }

    if (options.disable_fast_channel_zapping) {
        params.append('disable_fast_channel_zapping', options.disable_fast_channel_zapping);
    }

    const queryString = params.toString();
    return queryString ? `${baseUrl}?${queryString}` : baseUrl;
}

function constructHTTPStreamUrl(deviceId, format, options = {}) {
    const baseUrl = `http://<server_ip>:<port>/media/${deviceId}.${format}`;

    const params = new URLSearchParams();

    if (options.resolution) {
        params.append('resolution', options.resolution);
    }

    if (options.pos) {
        params.append('pos', options.pos);
    }

    if (options.endPos) {
        params.append('endPos', options.endPos);
    }

    if (options.rotation) {
        params.append('rotation', options.rotation);
    }

    if (options.sfd) {
        params.append('sfd', options.sfd);
    }

    if (options.rt) {
        params.append('rt', options.rt);
    }

    if (options.audio_only) {
        params.append('audio_only', options.audio_only);
    }

    if (options.accurate_seek) {
        params.append('accurate_seek', options.accurate_seek);
    }

    if (options.stream !== undefined) {
        params.append('stream', options.stream);
    }

    if (options.duration !== undefined) {
        params.append('duration', options.duration);
    }

    if (options.signature) {
        params.append('signature', options.signature);
    }

    if (options.utc) {
        params.append('utc', options.utc);
    }

    const queryString = params.toString();
    return queryString ? `${baseUrl}?${queryString}` : baseUrl;
}

function constructArchiveFragmentUrl(deviceId, format, options = {}) {
    const baseUrl = `http://<server_ip>:<port>/hls/${deviceId}.${format}`;

    const params = new URLSearchParams();

    if (options.pos) {
        params.append('pos', options.pos);
    }

    if (options.duration !== undefined) {
        params.append('duration', options.duration);
    }

    if (options.hi) {
        params.append('hi', options.hi);
    }

    if (options.low) {
        params.append('low', options.low);
    }

    const queryString = params.toString();
    return queryString ? `${baseUrl}?${queryString}` : baseUrl;
}

async function registerFile(fileName, size, md5, url, peerPolicy) {
    const requestBody = {
        size: size,
        md5: md5,
        url: url,
        peerPolicy: peerPolicy
    };

    try {
        const response = await fetch('/api/registerFile', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(requestBody)
        });

        if (response.ok) {
            const fileRecord = await response.json();
            console.log('File registered successfully:', fileRecord);
            return fileRecord;
        } else {
            throw new Error('Failed to register file');
        }
    } catch (error) {
        console.log('Error registering file:', error);
        throw error;
    }
}

async function deleteDownload(fileName, deleteData = true) {
    const queryParams = new URLSearchParams({ deleteData: deleteData });
  
    try {
      const response = await fetch(`/api/downloads/${fileName}?${queryParams}`, {
        method: 'DELETE'
      });
  
      if (response.ok) {
        console.log('File record deleted successfully');
      } else {
        throw new Error('Failed to delete file record');
      }
    } catch (error) {
      console.log('Error deleting file record:', error);
      throw error;
    }
  }

  async function createDeviceRecord(deviceData) {
    try {
      const response = await fetch('/api/devices', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(deviceData),
      });
  
      if (response.ok) {
        const deviceRecord = await response.json();
        console.log('Device record created successfully:', deviceRecord);
        return deviceRecord;
      } else {
        throw new Error('Failed to create device record');
      }
    } catch (error) {
      console.log('Error creating device record:', error);
      throw error;
    }
  }

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
        if (checkStatus(response, true)) {
          var camera1_flow = response.data;
          console.log('API response:', camera1_flow);
          // Perform additional actions with the live camera streams
        }
      })
      .catch(function (error) {
        console.log('Request error:', error.message);
      });
  }
  
  // Function to get recording events
  function getRecordingEvents() {
    // Construct the API URL
    var url = `${serverAddress}/api/createEvent?source=recording&login=${username}&password=${password}`;
  
    axios.get(url)
      .then(function (response) {
        if (checkStatus(response, true)) {
          var responseData = response.data;
          console.log(responseData);
          var camera1Video = document.getElementById('camera1');
          camera1Video.src = responseData;
        }
      })
      .catch(function (error) {
        console.log('Request error:', error.message);
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

var cameraURL = serverAddress + '/hls/' + deviceId + '.m3u8?lo&auth=' + authKey;
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