document.addEventListener('DOMContentLoaded', async () => {
    // Ottieni l'elemento del player video tramite l'ID.
    const player = document.getElementById('player');

    // Imposta la sorgente del player con la posizione del video specificata dalla variabile 'videoLocation'.
    player.src = videoLocation;

    // Carica il player
    player.load();

    // Imposta la sorgente del player con l'URL del video specificato, lo ricarica e lo riproduce
    const playVideo = (videoUrl) => {
        player.src = videoUrl;
        player.load();
        player.play();
    };

    // Crea la playlist di video della sessione
    const createPlaylist = (videoData) => {
        // Crea un nuovo elemento di lista non ordinata per contenere la playlist video.
        const videoList = document.createElement('ul');

        // Itera su ciascun oggetto video presente nell'array 'videoData'.
        videoData.forEach((videoObj) => {
            const videoUrl = videoObj.video;

            // Estrapola il titolo del video dall'URL del video
            const videoTitle = videoUrl.split('/').pop();

            // Crea un nuovo elemento di lista per rappresentare ciascun video nella playlist.
            const listItem = document.createElement('li');

            // Imposta il contenuto testuale del nuovo elemento di lista con il titolo del video.
            listItem.textContent = videoTitle;

            // Aggiungi un listener di evento per il clic su ciascun elemento di lista, il quale riprodurrà il video corrispondente.
            listItem.addEventListener('click', () => playVideo(videoUrl));

            // Aggiungi l'elemento di lista alla playlist video.
            videoList.appendChild(listItem);
        });

        // Ottieni l'elemento della playlist tramite l'ID 'playlist'.
        const playlistElement = document.getElementById('playlist');

        // Aggiungi la playlist video (elemento ul) all'elemento della playlist.
        playlistElement.appendChild(videoList);
    };

    try {
        // Utilizza l'API per recuperare tutti i video di una sessione avendone uno
        const response = await fetch('http://localhost/cameras/videoList-helper.php?action=get-playlist', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ video: player.src.replace(/^http:\/\/localhost/, "..") }),
        });

        if (!response.ok) {
            throw new Error('La risposta della rete non è valida');
        }

        // Risposta come JSON
        const videos = await response.json();
        createPlaylist(videos);
        
    } catch (error) {
        // Se si verifica un errore durante la richiesta AJAX o la creazione della playlist, registra l'errore nella console.
        console.error('La richiesta AJAX non è riuscita:', error);
    }
});
