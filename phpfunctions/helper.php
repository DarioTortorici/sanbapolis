<?php

function validate_input_text($textValue){
    if (!empty($textValue)){
        $trim_text = trim($textValue);
        // remove illegal character
        $sanitize_str = filter_var($trim_text, FILTER_SANITIZE_STRING);
        return $sanitize_str;
    }
    return '';
}

function validate_input_email($emailValue){
    if (!empty($emailValue)){
        $trim_text = trim($emailValue);
        // remove illegal character
        $sanitize_str = filter_var($trim_text, FILTER_SANITIZE_EMAIL);
        return $sanitize_str;
    }
    return '';
}

function validate_password($password) {
    // Verifica la lunghezza minima
    if (strlen($password) < 8) {
        return false;
    }

    // Verifica se contiene almeno una lettera maiuscola
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }

    // Verifica se contiene almeno un carattere speciale
    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        return false;
    }

    // La password soddisfa tutti i requisiti
    return true;
}


// profile image
function upload_profile($path, $file){
    $targetDir = $path;
    $default = "beard.png";

    // get the filename
    $filename = basename($file['name']);
    $targetFilePath = $targetDir . $filename;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

    If(!empty($filename)){
        // allow certain file format
        $allowType = array('jpg', 'png', 'jpeg', 'gif', 'pdf');
        if(in_array($fileType, $allowType)){
            // upload file to the server
            if(move_uploaded_file($file['tmp_name'], $targetFilePath)){
                return $targetFilePath;
            }
        }
    }
    // return default image
    return $path . $default;
}

// get user info
function get_user_info($con, $userID){
    $query = "SELECT firstName, lastName, email, sport, userType, society, profileImage FROM user WHERE userID=:userID";
    $stmt = $con->prepare($query);
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return empty($row) ? false : $row;
}

// salting della password
define( 'PWD_SALT', 'yRUbX$2JOt7#9A?p7dDa' );
function add_salt( $password, $userID ) {
	$salted_pwd = md5( PWD_SALT ) . md5( $password ) . md5( $userID );
	return $salted_pwd;
}
