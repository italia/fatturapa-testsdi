// configuration for jshint
/* jshint browser: true, devel: true */
/* global Vue, get */

"use strict";

Vue.component('invoice-table2', {
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
    created: function() {
        EventBus.$on('refreshTables', function() {
            console.log("ciao");
        });
    },
    methods: {
        doit: function() {
            post(this.home + this.action);
        },
        accept: function(id) {
            post(this.home + "/rpc/accept/" + id);
        },
        refuse: function(id) {
            post(this.home + "/rpc/refuse/" + id);
        }
    },
    template: '\
<div class="card mb-3">\
    <div class="card-header">\
        <i class="fas fa-table"></i> {{ title }}\
    </div>\
    <div class="card-body">\
        <div class="table-responsive">\
            <table class="table table-bordered" width="100%" cellspacing="0">\
                <thead>\
                    <tr>\
                        <th>Id</th>\
                        <th>Nome file</th>\
                        <th>Data e ora</th>\
                        <th>Azioni</th>\
                    </tr>\
                </thead>\
                <tbody>\
                    <tr v-for="i in invoices">\
                        <td>{{ i.id }}</td>\
                        <td>{{ i.nomefile }}</td>\
                        <td>{{ i.ctime }}</td>\
                        <td>\
                            <button type="button" class="btn btn-success" v-on:click="accept(i.id);">Accetta</button>\
                            <button type="button" class="btn btn-danger" v-on:click="refuse(i.id);">Rifiuta</button>\
                        </td>\
                    </tr>\
                </tbody>\
            </table>\
        </div>\
    </div>\
    <div class="card-footer small text-muted">\
        <span class="text-muted">{{ description }}</span>\
    </div>\
</div>'
});