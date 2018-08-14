// configuration for jshint
/* jshint browser: true, devel: true */
/* global Vue, Handlebars, jsonSchema */

"use strict";

Vue.component('invoice-table', {
  props: ['endpoint', 'title'],
  data: function () {
    return {
      invoices: []
    }
  },
  mounted: function () {
    var self = this;
    var request = new XMLHttpRequest();
    request.open("GET", self.endpoint);
    request.onload = function() {
        if (request.status == 200) {
            if (request.responseText) {
                var invoices = JSON.parse(request.responseText);
                self.invoices = invoices;
            }
        }
    };
    request.send();
  },
  template: '\
    <div>\
      <h3>{{ title }}</h3>\
      <table>\
        <tr><th>Id</th><th>Da</th><th>A</th></tr>\
        <tr v-for="i in invoices">\
            <td>{{ i.id }}</td>\
            <td>{{ i.source }}</td>\
            <td>{{ i.destination }}</td>\
        </tr>\
      </table>\
    </div>'
});

var app = new Vue({
  el: '#tables'
});

document.addEventListener('DOMContentLoaded', function() {
  console.log("DOM fully loaded and parsed");
});
