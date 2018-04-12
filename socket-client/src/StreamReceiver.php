<?php

namespace MyApp;

class StreamReceiver
{
    protected $transferSessionId;

    public function __construct($transferSessionId) {
        $this->transferSessionId = $transferSessionId;

        $this->prepareUpload();
    }

    private function prepareUpload() {
        if (empty($this->transferSessionId)) {
            http_response_code(403);
            return;
        }

        if (!is_dir('/uploads/' . $this->transferSessionId)) {
            mkdir('/uploads/' . $this->transferSessionId);
        }

        $uploads = '/uploads';
        if (!is_dir($uploads)) {
            throw new Exception($uploads . ' does not exist!');
        }

        //Use ini_get to get the value of
        //the file_uploads directive
        if (ini_get('file_uploads')) {
//            echo 'file_uploads is set to "1". File uploads are allowed.';
        } else{
            throw new Exception('File_uploads is set to "0". File uploads are NOT allowed.');
        }

        $tempFolder = ini_get('upload_tmp_dir');
        if (empty($tempFolder)) {
            $tempFolder = sys_get_temp_dir();
        }

//        echo 'Your upload_tmp_dir directive has been set to: "' . $tempFolder . '"<br>';

        //Firstly, lets make sure that the upload_tmp_dir
        //actually exists.
        if (!is_dir($tempFolder)) {
            throw new Exception($tempFolder . ' does not exist!');
        } else {
//            echo 'The directory "' . $tempFolder . '" does exist.<br>';
        }

        if (!is_writable($tempFolder)){
            throw new Exception($tempFolder . ' is not writable!');
        } else {
//            echo 'The directory "' . $tempFolder . '" is writable. All is good.<br>';
        }

        //var_dump($_FILES);
        //var_dump($_PUT);
    }

    public function receiveFiles($files) {
        $result = [];

        if (count($files) > 0) {
            foreach($files as $key => $value) {
                $tmpName = $value['tmp_name'];
                $newName = sha1_file($tmpName);
                $uploadName = sprintf(
                    '/uploads/%s/%s',
                    $this->transferSessionId,
                    $newName);

                $value['uploadName'] = $newName;
                $result[] = $value;

                if (!is_uploaded_file($tmpName)) {
                    rename($tmpName, $uploadName);
                } else {
                    move_uploaded_file($tmpName, $uploadName);
                }
            }
        }

        return $result;
    }

}