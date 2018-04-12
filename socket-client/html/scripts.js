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

function setChannel() {
    let channel = document.getElementById('channel').value;
    conn.send(JSON.stringify({
        command: "joinChannel",
        channel: channel
    }));
}

function setHandle() {
    let name = document.getElementById('handle').value;
    conn.send(JSON.stringify({
        command: "name",
        name: name
    }));
}

function openWebsocketConnection() {
    console.log('openWebsocketConnection');
    let conn = new WebSocket('ws://localhost:8081/channel');

    conn.onopen = function () {
        console.log("Connection established!");
        console.log(getCookie('PHPSESSID'));
    };

    conn.onmessage = function (e) {
        let data = JSON.parse(e.data);
        console.log("onMessage: ", data);

        let action = data.action;

        if (action === "joinChannel") {
            let channelElement = document.getElementById('currentChannel');
            removeChildren(channelElement);

            let newText = document.createTextNode(data.channel);
            channelElement.appendChild(newText);

            document.getElementById('channel').value = data.channel;

            let element = document.getElementById('members');
            removeChildren(element);

            data.members.forEach(function(member) {
                let span = document.createElement('div')
                let text = document.createTextNode(member.name);
                span.appendChild(text);
                element.appendChild(span);
            });
        }

        if (action === "receiveFiles") {
            let element = document.getElementById('files');

            data.files.forEach(function(file) {
                let div = document.createElement('div');
                div.appendChild(document.createTextNode(file.filename));

                let a = document.createElement('a');
                let linkText = document.createTextNode("accept");
                a.appendChild(linkText);
                a.title = "accept";
                a.href = "/download/" + data.transferSessionId + "/" + file.uploadName;
                // a.onclick = function(e) {
                //     e.preventDefault();
                //     acceptFile(channel, file);
                // };

                div.appendChild(a);

                element.appendChild(div);
            });
        }

        if (data.yourName) {
            document.getElementById('handle').value = data.yourName;
        }
    };
}

function acceptFile(channel, file) {
    console.log('acceptFile', channel, file);

    let http = new Http();
    let url = '/download/' + channel + '/' + file.filename;
    console.log('acceptFile', url);
    http.get(url).then(function (response) {
        console.log(response);
        let json = JSON.parse(response);
        console.log('downloadFile', json);
    }, function (error) {
        console.error("downloadfile Failed!", error);
    });
}

function removeChildren(element) {
    while(element.childNodes.length >= 1) {
        element.removeChild(element.firstChild);
    }
}