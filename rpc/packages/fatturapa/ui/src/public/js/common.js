// configuration for jshint
/* jshint browser: true, devel: true */
/* global Vue */

var EventBus = new Vue();

// GET url and on success if element id is supplied, show response text inside element
function get(url, element) {
    var request = new XMLHttpRequest();
    request.open("GET", url);
    request.onload = function() {
        if (request.status == 200) {
            if (element) {
                document.getElementById(element).innerHTML = request.responseText;
            }
        } else {
            console.error(request.responseText);
        }
    };
    console.log('GETting ' + url);
    request.send();
}

// POST parameter=element.value to url
function post(url, parameter, element) {
    var request = new XMLHttpRequest();
    request.open("POST", url);
    request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    request.onload = function() {
        if ((request.status != 200) && (request.status != 201)) {
            console.error(request.responseText);
            Toastify({
                text: "C'è stato un errore " + request.status + ", riprova.",
                duration: 5000,
                close: true,
                gravity: "top",
                backgroundColor: "#f73e5a",
            }).showToast();
        } else {
            Toastify({
                text: "Chiamata a " + url + ' completata',
                duration: 5000,
                close: true,
                gravity: "top",
                backgroundColor: "#00cc85",
            }).showToast();
            EventBus.$emit('refreshTables');
        }
    };
    if (parameter && element) {
        var value = document.getElementById(element).value;
        var data = parameter + '=' + value; // TODO urlescape !
        console.log('POSTing ' + data + ' to ' + url);
        request.send(data);
    } else {
        request.send();
    }
}

function sendReceiveData(dataTo, verb, url, callback) {
    // sends the dataTo to the url with the supplied verb
    // on success executes the callback passing the received data (if any)
    // on error executes the error callback
    // input:
    //     - dataTo, JSON data to send (only POST & PUT)
    //     - verb, string: one of GET, POST, DELETE or PUT
    //     - url, string: the API endpoint
    //     - callback, function: the function to execute on success; the JSON data returned (if any) is forwarded
    // output: on success, whatever the callback returns; on error, nothing
    console.log('sendReceiveData ' + verb + ' ' + url);
    var request = new XMLHttpRequest();
    console.log(verb + ' ' + url);
    request.onload = function() {
        if (request.status >= 200 && request.status < 400) {
            if (request.responseText) {
                console.log('sendReceiveData request success: ' + request.responseText.substring(0, 100) + '...');
                var dataFrom = JSON.parse(request.responseText);
                Toastify({
                    text: "Chiamata a " + url + ' completata',
                    duration: 5000,
                    close: true,
                    gravity: "top",
                    backgroundColor: "#00cc85",
                }).showToast();
                return callback(dataFrom);
            } else {
                return callback();
            }
        } else if (request.status === 401) {
            // if authorization fails on the API backend, reload the entire page
            window.location.reload(true);
        } else {
            Toastify({
                text: "C'è stato un errore " + request.status + ", riprova.",
                duration: 5000,
                close: true,
                gravity: "top",
                backgroundColor: "#f73e5a",
            }).showToast();
        }
    }; // onload
    request.onerror = function(e) {
        Toastify({
            text: 'Errore di connessione, riprova.',
            duration: 5000,
            close: true,
            gravity: "top",
            backgroundColor: "#f73e5a",
        }).showToast();
    };
    request.open(verb, url);
    request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    request.setRequestHeader("accept", "application/json");
    if (verb == 'POST' || verb == 'PUT') {
        var formData = JSON.stringify(dataTo);
        request.setRequestHeader("Content-Type", "application/json; charset=UTF-8");
        request.send(formData);
    } else {
        request.send();
    }
} // sendReceiveData

// show datetime in UI
var clock;

function refreshClock() {
    var url = window.location.protocol + "//" + window.location.host + "/";
    url = url + "sdi/rpc/datetime";
    $.ajax({
        url: url,
        dataType: 'json',
        success: function(data) {
            if (clock) {
                window.clearInterval(clock);
            }
            var wallclock = new Date().getTime();
            var timestamp = data.timestamp;
            var speed = data.speed;
            var date = new Date(timestamp * 1000);
            $("#dateTime").html(date.toLocaleString());
            if (speed > 0) {
                clock = window.setInterval(function() {
                    var new_wallclock = new Date().getTime();
                    var new_timestamp = timestamp + (new_wallclock - wallclock) * speed / 1000.0;
                    date = new Date(new_timestamp * 1000);
                    $("#dateTime").html(date.toLocaleString());
                    wallclock = new_wallclock;
                    timestamp = new_timestamp;
                }, 1000); // refresh rate in milliseconds
            }
        },
        error: function(data) {
            Toastify({
                text: "Errore caricamento in: " + url + ", riprova.",
                duration: 5000,
                close: true,
                gravity: "top",
                backgroundColor: "#f73e5a",
            }).showToast();
        }
    });
}

$(document).ready(refreshClock);
window.onunload = function() {
    if (clock) {
        window.clearInterval(clock);
    }
};
