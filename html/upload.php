<?php

session_start();

//Enable error reporting.
error_reporting(E_ALL);
ini_set("display_errors", 1);

//Use ini_get to get the value of
//the file_uploads directive
if(ini_get('file_uploads')){
    echo 'file_uploads is set to "1". File uploads are allowed.';
} else{
    echo 'Warning! file_uploads is set to "0". File uploads are NOT allowed.';
}

$tempFolder = ini_get('upload_tmp_dir');

echo 'Your upload_tmp_dir directive has been set to: "' . $tempFolder . '"<br>';

//Firstly, lets make sure that the upload_tmp_dir
//actually exists.
if(!is_dir($tempFolder)){
    throw new Exception($tempFolder . ' does not exist!');
} else{
    echo 'The directory "' . $tempFolder . '" does exist.<br>';
}

if(!is_writable($tempFolder)){
    throw new Exception($tempFolder . ' is not writable!');
} else{
    echo 'The directory "' . $tempFolder . '" is writable. All is good.<br>';
}

$uploads = '/uploads';
if (!is_dir($uploads)) {
    throw new Exception($uploads . ' does not exist!');
}

var_dump($_FILES);
//var_dump($_PUT);

$files = array();

/* Handle moving the file(s) */
if (count($_FILES) > 0) {
    foreach($_FILES as $key => $value) {
        $files[$value['name']] = $value['name'];

        if (!is_uploaded_file($value['tmp_name'])) {
            rename($value['tmp_name'], '/uploads/'.$value['name']);
        } else {
            move_uploaded_file($value['tmp_name'], '/uploads/'.$value['name']);
        }
    }
}

$_SESSION['files'] = $files;

echo "Ok";
