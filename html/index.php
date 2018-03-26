<!DOCTYPE html>
<html>
<head>
    <title>Upload Files using XMLHttpRequest - Minimal</title>

    <script type="text/javascript">
        function fileSelected() {
            var file = document.getElementById('fileToUpload').files[0];
            if (file) {
                var fileSize = 0;
                if (file.size > 1024 * 1024)
                    fileSize = (Math.round(file.size * 100 / (1024 * 1024)) / 100).toString() + 'MB';
                else
                    fileSize = (Math.round(file.size * 100 / 1024) / 100).toString() + 'KB';

                document.getElementById('fileName').innerHTML = 'Name: ' + file.name;
                document.getElementById('fileSize').innerHTML = 'Size: ' + fileSize;
                document.getElementById('fileType').innerHTML = 'Type: ' + file.type;
            }
        }

        function uploadFile() {
            console.log(document.getElementById('fileToUpload').files[0]);

            getSession(function(id) {
                var fd = new FormData();
                fd.append("fileToUpload", document.getElementById('fileToUpload').files[0]);
                fd.append("transferSessionId", id);

                var xhr = new XMLHttpRequest();
                xhr.upload.addEventListener("progress", uploadProgress, false);
                xhr.addEventListener("load", uploadComplete, false);
                xhr.addEventListener("error", uploadFailed, false);
                xhr.addEventListener("abort", uploadCanceled, false);
                xhr.open("POST", "upload.php");
                xhr.send(fd);
            });

        }

        function uploadProgress(evt) {
            if (evt.lengthComputable) {
                var percentComplete = Math.round(evt.loaded * 100 / evt.total);
                document.getElementById('progressNumber').innerHTML = percentComplete.toString() + '%';
            }
            else {
                document.getElementById('progressNumber').innerHTML = 'unable to compute';
            }
        }

        function uploadComplete(evt) {
            /* This event is raised when the server send back a response */
            console.log(evt.target.responseText);
            window.location.reload();
        }

        function uploadFailed(evt) {
            alert("There was an error attempting to upload the file.");
        }

        function uploadCanceled(evt) {
            alert("The upload has been canceled by the user or the browser dropped the connection.");
        }

        function resetFiles() {
            var http = new XMLHttpRequest();
            http.open("POST", 'api.php', true);
            http.setRequestHeader("Content-type", "application/json");

            http.onreadystatechange = function() {
                if (http.readyState == 4 && http.status == 200) {
                    // alert(http.responseText);
                }
            };

            http.send(JSON.stringify({method:"reset"}));
            window.location.reload();
        }

        function getFileList() {
            var http = new XMLHttpRequest();
            http.open("POST", "api.php", true);
            http.setRequestHeader("Content-type", "application/json");

            http.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("files").innerHTML = this.responseText;

                    var json = JSON.parse(this.response);
                    console.log(json);

                    var result = "<ul>";
                    json.files.forEach(function(element) {
                       console.log(element);
                       result +=
                           "<li>" +
                           element.name +
                           "<a href='/api/download/" +
                           element.filename +
                           "'> (download)</a>"
                           + "</li>";
                    });
                    result += "</ul>";
                    document.getElementById("files").innerHTML = result;

                    var testElements = document.getElementsByClassName('hasfiles');
                    Array.prototype.filter.call(testElements, function(testElement) {
                        if (json.files.length > 0) {
                            testElement.classList.remove('hidden');
                        }
                    });
                }
            };

            http.send(JSON.stringify({method:"getFileList"}));
        }

        function getSession(callback) {
            var http = new XMLHttpRequest();
            http.open("POST", "api.php", true);
            http.setRequestHeader("Content-type", "application/json");

            http.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    var json = JSON.parse(this.response);
                    console.log(json);

                    if (callback) {
                        callback(json.transferSessionId);
                    }
                }
            };

            http.send(JSON.stringify({method:"getSession"}));
        }

        function startSession(name, callback) {
            var http = new XMLHttpRequest();
            http.open("POST", "api.php", true);
            http.setRequestHeader("Content-type", "application/json");

            http.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    var json = JSON.parse(this.response);
                    console.log(json);

                    callback(json.transferSessionId);
                }
            };

            http.send(JSON.stringify({method:"startSession",name:name}));
        }

        function joinSession(id) {
            var http = new XMLHttpRequest();
            http.open("POST", "api.php", true);
            http.setRequestHeader("Content-type", "application/json");

            http.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    var json = JSON.parse(this.response);
                    console.log(json);
                }
            };

            http.send(JSON.stringify({method:"joinSession",transferSessionId:id}));
        }

        function downloadFile(evt, filename) {
            evt.preventDefault();

            console.log(filename);
        }
    </script>
    <style>
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
<form id="form1" enctype="multipart/form-data" method="post" action="upload_stream.php">
    <div class="row">
        <label for="fileToUpload">Select a File to Upload</label><br />
        <input type="file" name="fileToUpload" id="fileToUpload" onchange="fileSelected();"/>
    </div>
    <div id="fileName"></div>
    <div id="fileSize"></div>
    <div id="fileType"></div>
    <div class="row">
        <input type="button" onclick="uploadFile()" value="Upload" />
    </div>
    <div id="progressNumber"></div>
</form>

<h1>Files</h1>
<div id="files"></div>
<div class="hasfiles hidden">
    <input type="button" name="reset" value="Reset" onclick="resetFiles()" />
</div>

<script>
    getFileList();
    startSession('test', function(id) {
        console.log(id);
        joinSession(id);
        getSession();
    });
</script>

</body>
</html>