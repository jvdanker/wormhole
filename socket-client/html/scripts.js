function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

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

    var channelId = document.getElementById('channel').value;
    console.log('uploadFile:', channelId);
    var fd = new FormData();
    fd.append("fileToUpload", document.getElementById('fileToUpload').files[0]);
    fd.append("channelId", channelId);

    var xhr = new XMLHttpRequest();
    xhr.upload.addEventListener("progress", uploadProgress, false);
    xhr.addEventListener("load", uploadComplete, false);
    xhr.addEventListener("error", uploadFailed, false);
    xhr.addEventListener("abort", uploadCanceled, false);
    xhr.open("POST", "upload.php");
    xhr.send(fd);
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
    console.log(evt.target.response);
    // window.location.reload();
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

// function getFileList(channelId, timestamp) {
//     var http = new XMLHttpRequest();
//     http.open("POST", "api.php", true);
//     http.setRequestHeader("Content-type", "application/json");
//
//     if (timestamp === undefined) {
//         timestamp = Math.floor((new Date).getTime()/1000);
//     }
//
//     return new Promise(function (resolve, reject) {
//         http.onreadystatechange = function() {
//             if (this.readyState == 4 && this.status == 200) {
//                 var json = JSON.parse(this.response);
//                 resolve(json);
//             }
//         };
//
//         http.send(JSON.stringify({
//             method:"getFileList",
//             channelId: channelId,
//             time: timestamp
//         }));
//     });
// }

function startSession(name) {
    return new Promise(function(resolve, reject) {
        var http = new Http();
        http.post('api.php', {
            method: "startSession",
            name: name
        }).then(function (response) {
            var json = JSON.parse(response);
            console.log('startSession', json);
            resolve(json);
        }, function (error) {
            console.error("startSession Failed!", error);
            reject(error);
        });
    });
}

// function startSession(name, callback) {
//     var http = new XMLHttpRequest();
//     http.open("POST", "api.php", true);
//     http.setRequestHeader("Content-type", "application/json");
//
//     http.onreadystatechange = function() {
//         if (this.readyState == 4 && this.status == 200) {
//             console.log('startSession: ', this.response);
//
//             var json = JSON.parse(this.response);
//             console.log('startSession', json);
//
//             if (callback) {
//                 callback(json);
//             }
//         }
//     };
//
//     http.send(JSON.stringify({method:"startSession",name:name}));
// }

// function joinChannel(id) {
//     var http = new XMLHttpRequest();
//     http.open("POST", "api.php", true);
//     http.setRequestHeader("Content-type", "application/json");
//
//     http.onreadystatechange = function() {
//         if (this.readyState == 4 && this.status == 200) {
//             var json = JSON.parse(this.response);
//             console.log('joinChannel', json);
//         }
//     };
//
//     http.send(JSON.stringify({method:"joinChannel",channelId:id}));
// }

function downloadFile(evt, filename) {
    evt.preventDefault();

    console.log(filename);
}