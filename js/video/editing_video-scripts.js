$(document).ready(function () {
    // Video time update event handling
    var video = $('.embed-responsive-item');
    video.on("timeupdate", function () {
        var stime = video[0].currentTime;
        stime = stime.toString();
        stime = stime.split(".").pop();
        stime = stime.substr(0, 3);

        $('#timing_video').val(fromSeconds(video[0].currentTime) + ':' + stime);
    });

    // Handling window onload event
    window.onload = function () {
        let timing = findGetParameter("timing_screen");
        if (timing != null) {
            timing = parseFloat(timing);
            document.getElementById("<?php echo $filename ?>").currentTime = timing;
        }

        let message = findGetParameter("message");
        if (message == "mark_exists") {
            showSnackbar();
        }
    };

    // Attach click event listeners to clickable-row <td> elements
    const clickableRows = document.querySelectorAll('.clickable-row td[data-href]');
    clickableRows.forEach(td => {
        td.addEventListener('click', () => {
            const videoPath = td.getAttribute('data-href');
            changeVideoSource(videoPath);
        });
    });

    /* 
       ---------------
        Modal Section
       ---------------
    */
    // Apre i modal corrispondenti ai bottoni cliccati
    document.getElementById("openVideoModal").addEventListener("click", function () {
        $('#videoModal').modal('show');
    });
    document.getElementById("openClipModal").addEventListener("click", function () {
        $('#clipModal').modal('show');
    });
    document.getElementById("openMarksModal").addEventListener("click", function () {
        $('#marksModal').modal('show');
    });
    document.getElementById("openScreensModal").addEventListener("click", function () {
        $('#screensModal').modal('show');
    });
});

/**
 * Invia una richiesta al server per ottenere il timing del segnaposto corrispondente.
 * Aggiorna i dettagli del segnaposto e mette in pausa il video.
 */
function segnaposto() {
    const xhttp = new XMLHttpRequest(); // Crea una nuova richiesta XMLHttpRequest
    var url = "../marks/mark_manager.php?timing=" + $('#timing_video').val(); // Costruisce l'URL per la richiesta
    xhttp.open("GET", url, true); // Imposta il metodo e l'URL della richiesta
    xhttp.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            let timing = xhttp.responseText; // Ottiene il timing dalla risposta
            if (timing !== "") {
                $('#timing_mark')[0].value = timing; // Imposta il timing del segnaposto
                $('#addMarkModal').modal('show'); // Mostra il form per aggiungere  segnaposto
                $('.embed-responsive-item')[0].pause(); // Mette in pausa il video corrente
            }
        }
    };
    xhttp.send(); // Invia la richiesta al server
}

/**
* Imposta il tempo corrente del video e lo mette in pausa.
*
* @param {HTMLVideoElement} video - L'elemento video su cui agire.
* @param {number} timing - Il tempo da impostare nel video (in secondi).
*/
function goToTiming(video, timing) {
    video.currentTime = timing;
    video.pause();
}

/**
 * Cambia la sorgente del video e avvia la riproduzione.
 *
 * @param {string} videoPath - Il percorso del nuovo video da caricare.
 */
function changeVideoSource(videoPath) {
    const videoElement = document.querySelector(".embed-responsive-item");
    
    videoElement.pause(); // Mette in pausa il video corrente
    videoElement.src = videoPath; // Imposta la nuova sorgente video
    videoElement.load(); // Carica la nuova sorgente video
    videoElement.play(); // Avvia la riproduzione del nuovo video
}

function toggleIframe(iframeId) {
    var iframe = document.getElementById(iframeId);
    if (iframe.style.display === "none") {
        iframe.style.display = "block";
    } else {
        iframe.style.display = "none";
    }
}
