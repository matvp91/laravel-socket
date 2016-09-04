var Socket = function (url) {
    this.url = url;

    this.events = [];

    this.connected = false;

    this.triggerEvent = function (name, data, event) {
        var i = 0, len = this.events.length;
        for (; i < len; i++) {
            if (this.events[i].name === name) {
                this.events[i].callback(data, event);
            }
        }
    };

    this.addEvent = function (name, callback) {
        this.events.push({
            name: name,
            callback: callback
        });
    };

    this.removeEvent = function (callback) {
        var i = 0, len = this.events.length;
        for (; i < len; i++) {
            if (!this.events[i])
                continue;
            if (this.events[i].callback === callback) {
                this.events.splice(i, 1);
            }
        }
    };

    this.bindConnection = function () {
        var _this = this;

        this.connection.onopen = function (event) {
            _this.connected = true;

            _this.triggerEvent('connected', null, event);
        };

        this.connection.onmessage = function (event) {
            _this.triggerEvent('message', null, event);

            var incoming = JSON.parse(event.data);
            _this.triggerEvent(incoming.command, incoming.data, event);
        };

        this.connection.onclose = function (event) {
            _this.triggerEvent('disconnected', null, event);
        };
    };
};

Socket.prototype.connect = function (callback) {
    if (callback) {
        this.bind('connected', callback);
    }

    this.connection = new WebSocket(this.url);
    this.bindConnection();
};

Socket.prototype.isConnected = function () {
    return this.connected;
};

Socket.prototype.disconnect = function () {
    this.connection.close();
};

Socket.prototype.bind = function (name, callback) {
    this.addEvent(name, callback);
    return callback;
};

Socket.prototype.on = Socket.prototype.bind;

Socket.prototype.unbind = function (callback) {
    this.removeEvent(callback);
};

Socket.prototype.send = function (command, data) {
    var json = JSON.stringify({
        command: command,
        data: data
    });
    this.connection.send(json);
};
