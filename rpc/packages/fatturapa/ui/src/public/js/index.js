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
  <div class="card mb-3">\
  <div class="card-header">\
  <i class="fas fa-table"></i> {{ title }}</div>\
  <div class="card-body">\
  <div class="table-responsive">\
  <div>\
  <table class="table table-bordered" width="100%" cellspacing="0">\
  <thead><tr><th>Id</th><th>Da</th><th>A</th></tr></thead>\
  <tfoot><tr v-for="i in invoices">\
  <td>{{ i.id }}</td>\
  <td>{{ i.source }}</td>\
  <td>{{ i.destination }}</td>\
  </tr></tfoot>\
  </table>\
  </div>\
  </div>\
  </div>\
  <div class="card-footer small text-muted">Updated yesterday at 11:59 PM</div>\
  </div>'
});
var app = new Vue({
  el: '#tables'
});

document.addEventListener('DOMContentLoaded', function() {
  console.log("DOM fully loaded and parsed");
});