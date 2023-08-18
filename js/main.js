$(document).ready(function (e) {

    let $uploadfile = $('#register .upload-profile-image input[type="file"]');

    $uploadfile.change(function () {
        readURL(this);
    });

    $("#reg-form").submit(function (event) {
        let $password = $("#password");
        let $confirm = $("#confirm_pwd");
        let $error = $("#confirm_error");
        if($password.val() === $confirm.val()){
            return true;
        }else{
            $error.text("Password not Match");
            event.preventDefault();
        }
    });

     // Get the current page's filename
     var pageName = window.location.pathname.split('/').pop();

     // Remove file extension if present
     pageName = pageName.replace(/\.[^/.]+$/, "");

     // Remove dashes and underscores
     pageName = pageName.replace(/[-_]/g, " ");

     // Capitalize the first letter of each word
     pageName = pageName.replace(/\b\w/g, function(txt) {
         return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
     });

     // Set the title dynamically
     document.getElementById("page-title").textContent = "Sanbapolis | " + pageName;


});



function readURL(input) {
    if(input.files && input.files[0]){
        let reader = new FileReader();
        reader.onload = function (e) {
            $("#register .upload-profile-image .img").attr('src', e.target.result);
            $("#register .upload-profile-image .camera-icon").css({display: "none"});
        }

        reader.readAsDataURL(input.files[0]);

    }
}