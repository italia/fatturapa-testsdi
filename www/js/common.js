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