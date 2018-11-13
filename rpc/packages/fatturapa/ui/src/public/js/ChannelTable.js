// configuration for jshint
/* jshint browser: true, devel: true, multistr:true */
/* global Vue, get, post, $, EventBus, sendReceiveData */

"use strict";

Vue.component('channel-table', {
    props: ['endpoint', 'title', 'description', 'button', 'action', 'home'],
    data: function() {
        return {
            channels: [],
            channel: {
                id: '9999993',
                code: 'IT-123456789'
            },
            cedente: {
                id: '9999993',
                cedente: 'AT-00000000000'
            },
        };
    },
    mounted: function() {
        this.loadData();
    },
    created: function() {
        var self = this;
        EventBus.$on('refreshTables', function() {
            self.loadData();
        });
    },
    methods: {
    	addNewActor: function () {
        var self = this;
        sendReceiveData(this.channel, 'POST', '/sdi/rpc/actors', function() {
          self.loadData();
        });
	    },
	    removeActor: function (issuer) {	    
        var self = this;
        sendReceiveData(this.channel, 'DELETE', '/sdi/rpc/actors/' + issuer, function() {
          self.loadData();
        });
	    },
      	addNewChannel: function () {
        var self = this;
        sendReceiveData(this.cedente, 'POST', '/sdi/rpc/channels', function() {
          self.loadData();
        });
	    },
	    removeChannel: function (issuer) {
        var self = this;
        sendReceiveData(this.channel, 'DELETE', '/sdi/rpc/channels/' + issuer, function() {
          self.loadData();
        });
	    },
	    addNewCedente: function (issuer) {
        var self = this;
        sendReceiveData(this.cedente, 'POST', '/sdi/rpc/channels/?id=' + issuer, function() {
          self.loadData();
        });
	    },
	    removeCedente: function (issuer,cedente) {
        var self = this;
        sendReceiveData(this.channel, 'DELETE', '/sdi/rpc/channels/' + cedente , function() {
          self.loadData();
        });
	    },
        loadData: function() {
            var self = this;
            var request = new XMLHttpRequest();
            request.open("GET", self.home + self.endpoint);
            request.onload = function() {
                if (request.status == 200) {
                    if (request.responseText) {
                        var data = JSON.parse(request.responseText);
                        self.channels = data.channels;
                    }
                }
            };
            request.send();
        }
    },
    template: '\
<div class="card mb-3">\
    <div class="card-header">\
        <i class="fas fa-table"></i> {{ title }} \
         <button type="button" v-on:click="addNewActor" class="btn btn-block btn-success">Add +</button> \
    </div>\
    <div class="card-body">\
        <div class="table-responsive">\
            <div>\
                <table class="table table-bordered" width="100%" cellspacing="0">\
                    <thead>\
                        <tr>\
                            <th>Id</th>\
                            <th>Associated Cedente</th>\
                        </tr>\
                    </thead>\
                    <tbody>\
                        <tr v-for="(i, index) in channels">\
                            <td><a v-on:click="removeActor(i.id)" ><i class="it-cancel"></i></a> {{ i.id }}</td>\
                            <td><div v-for="(p, index2) in i.cedenti"><a v-on:click="removeCedente(i.id,p)"><i class="it-cancel"></i></a> {{ p }}</div><div><a class="link" v-on:click="addNewCedente(i.id)"><i class="it-more-actions"></i></a></div></td>\
                        </tr>\
                    </tbody>\
                </table>\
            </div>\
        </div>\
    </div>\
</div>'
});

