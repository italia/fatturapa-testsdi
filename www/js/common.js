// configuration for jshint
/* jshint browser: true, devel: true */
/* global Vue */

"use strict";

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
              backgroundColor: "linear-gradient(to right, #ff5f6d, #ffc371)",                  
            }).showToast();            
        } else {
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