// configuration for jshint
/* jshint browser: true, devel: true */
/* global Vue, get */

"use strict";

Vue.component('invoice-table', {
    props: ['endpoint', 'title', 'description', 'button', 'action', 'home'],
    data: function() {
        return {
            invoices: []
        };
    },
    mounted: function() {
        var self = this;
        var request = new XMLHttpRequest();
        request.open("GET", self.home + self.endpoint);
        request.onload = function() {
            if (request.status == 200) {
                if (request.responseText) {
                    var data = JSON.parse(request.responseText);
                    self.invoices = data.invoices;
                }
            }
        };
        request.send();
    },
    methods: {
        doit: function() {
            post(this.home + this.action);
        }
    },
    template: '\
<div class="card mb-3">\
    <div class="card-header">\
        <i class="fas fa-table"></i> {{ title }}\
    </div>\
    <div class="card-body">\
        <div class="table-responsive">\
            <div>\
                <table class="table table-bordered" width="100%" cellspacing="0">\
                    <thead>\
                        <tr>\
                            <th>Id</th>\
                            <th>Nome file</th>\
                            <th>Data e ora</th>\
                        </tr>\
                    </thead>\
                    <tfoot>\
                        <tr v-for="i in invoices">\
                            <td>{{ i.uuid }}</td>\
                            <td>{{ i.nomefile }}</td>\
                            <td>{{ i.ctime }}</td>\
                        </tr>\
                    </tfoot>\
                </table>\
            </div>\
        </div>\
    </div>\
    <div class="card-footer small text-muted">\
        <span class="text-muted">{{ description }}</span>\
        <button style="float: right;" v-if="button" type="button" v-on:click="doit();" class="btn btn-info">{{ button }}</button>\
    </div>\
</div>'
});