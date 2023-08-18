$(document).ready(function () {
    // Gestisce foto profilo upload
    let $uploadfile = $('#register .upload-profile-image input[type="file"]');
    $uploadfile.change(function () {
        readURL(this);
    });

    // Gestisce sottimisione form registrazione
    $("#reg-form").submit(function (event) {
        let $password = $("#password");
        let $confirm = $("#confirm_pwd");
        let $error = $("#confirm_error");
        
        if ($password.val() === $confirm.val()) {
            return true;
        } else {
            $error.text("Password does not match");
            event.preventDefault();
        }
    });

    // Imposta dinamicamente il titolo della pagina
    var pageName = window.location.pathname.split('/').pop();
    pageName = pageName.replace(/\.[^/.]+$/, "");
    pageName = pageName.replace(/[-_]/g, " ");
    pageName = pageName.replace(/\b\w/g, function(txt) {
        return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
    });
    document.getElementById("page-title").textContent = "Sanbapolis | " + pageName;
});

/**
 * Legge il URL del file selezionato e visualizza l'immagine nel profilo utente.
 *
 * @param {HTMLInputElement} input - L'elemento di input del file.
 */
function readURL(input) {
    if (input.files && input.files[0]) {
        // Crea un oggetto FileReader per leggere il contenuto del file.
        let reader = new FileReader();
        
        // Gestisce l'evento "onload" che si verifica quando il contenuto del file è stato letto.
        reader.onload = function (e) {
            // Imposta l'attributo 'src' dell'immagine nel profilo utente con il risultato letto.
            $("#register .upload-profile-image .img").attr('src', e.target.result);
            
            // Nasconde l'icona della fotocamera nell'elemento di caricamento immagine.
            $("#register .upload-profile-image .camera-icon").css({ display: "none" });
        };
        
        // Legge il contenuto del file come URL dati.
        reader.readAsDataURL(input.files[0]);
    }
}