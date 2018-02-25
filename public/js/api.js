function getInternalError(err) {
    var msg = err.responseJSON.message;
    return {
        errors: {"server" : [msg] },
    };
}

function defaultHandler(resp) {
    if (resp && resp.errors) {
        console.warn("errors: ", JSON.stringify(resp.errors));
    } else {
        console.log("api response", resp);
    }
}

function makeHandler(url) {
    return function(data, fn) {
        var url_ = util.interpolate(url, data);
        fn = fn || defaultHandler;
        return api.req.post(url_, data, fn);
    }
}

// TODO: refactor repeated code
var api = {
    req: {
        post: function(url, data, handler) {
            return $.post(url, data, handler)
                .fail(function(e) {
                    handler(getInternalError(e));
                });
        },
        get: function(url, data, handler) {
            return $.get(url, data, handler)
                .fail(function(e) {
                    handler(getInternalError(e));
                });
        },
    },

    dev: {
        cleanDB: makeHandler("/api/dev/clean-db"),
    },

    file: {
        upload: function(data) {
            var fd = new FormData();
            fd.append('filename', data.title);
            fd.append('filedata', data.filedata );

            return $.ajax({
                url: '/api/files/upload',
                data: fd,
                processData: false,
                contentType: false,
                type: 'POST',
            }).then(defaultHandler);
        },
    },

    doc: {
        send: makeHandler("/api/docs/send"),
        get: makeHandler("/api/docs/get/{trackingId}"),
        randomId: makeHandler("/api/docs/rand-id"),
        currentRoutes: makeHandler("/api/docs/current-routes/{trackingId}"),
        nextRoutes:    makeHandler("/api/docs/next-routes/{trackingId}"),
        forward:       makeHandler("/api/docs/forward/{trackingId}"),
        receive:       makeHandler("/api/docs/receive/{trackingId}"),
        abortSend:     makeHandler("/api/docs/abort-send/{trackingId}"),

        emit: function(data) {
            events.trigger("doc-change", data);
        },
        change: function(handler) {
            events.on("doc-change", function(_, arg) {
                handler(arg);
            });
        },

        setAttachment: function(data) {
            var fd = new FormData();
            var trackingId = data.trackingId;
            fd.append('trackingId', data.trackingId);
            fd.append('filename',   data.filename);
            fd.append('filedata',   data.filedata );

            var url = util.interpolate(
                '/api/docs/{trackingId}/set-attachment',
                {trackingId: trackingId},
            );
            return $.ajax({
                url: url,
                data: fd,
                processData: false,
                contentType: false,
                type: 'POST',
            }).then(defaultHandler);
        },
    },

    route: {
        serial: makeHandler("/api/routes/serial/{trackingId}"),
        parallel: makeHandler("/api/routes/parallel/{trackingId}"),
        origins: makeHandler("/api/routes/origins/{trackingId}"),
    },

    campus: {
        add: makeHandler("/api/campuses/add"),
        get: makeHandler("/api/campuses/{code}/get"),
        fetch: makeHandler("/api/campuses/list"),
        search: makeHandler("/api/campuses/search"),
    },

    user: {
        login: makeHandler("/api/users/login"),
        logout: makeHandler("/api/users/logout"),
        search: makeHandler("/api/users/search"),
        emit: function(data) {
            events.trigger("user-change", data);
        },
        change: function(handler) {
            events.on("user-change", function(_, arg) {
                handler(arg);
            });
        },

        seenRoutes: makeHandler("/api/users/{userId}/seen-routes"),
        seeRoute: makeHandler("/api/users/{userId}/see-route/{routeId}"),

        self: makeHandler("/api/users/self"),
        clearSelf: makeHandler("/api/users/self/clear"),

        setSelf: makeHandler("/api/users/self/{userId}"),

        get: makeHandler("/api/users/get/{id}"),

        add: function(user, fn) {
            fn = fn || defaultHandler;
            var url = "/api/users/add";
            return api.req.post(url, user, fn);
        },

        fetch: function(fn) {
            var url = "/api/users/list";
            return api.req.get(url, {}, fn || defaultHandler)
        },

        delete: function(id, fn) {
            fn = fn || defaultHandler;
            var url = "/api/users/del/"+id;
            return api.req.post(url, {}, fn)
        },
    },

    privilege: {
        add: function(office, fn) {
            fn = fn || defaultHandler;
            var url = "/api/privileges/add";
            if (!office.name)
                return fn({errors: ["name is required"]});

            return api.req.post(url, {
                name: office.name,
            }, fn);
        },

        fetch: function(fn) {
            var url = "/api/privileges/list";
            return api.req.get(url, {}, fn || defaultHandler)
        },

        delete: function(id, fn) {
            fn = fn || defaultHandler;
            var url = "/api/privileges/del/"+id;
            return api.req.post(url, {}, fn)
        },
    },

    position: {
        add: function(office, fn) {
            fn = fn || defaultHandler;
            var url = "/api/positions/add";
            if (!office.name)
                return fn({errors: ["name is required"]});

            return api.req.post(url, {
                name: office.name,
            }, fn);
        },

        fetch: function(fn) {
            var url = "/api/positions/list";
            return api.req.get(url, {}, fn || defaultHandler)
        },

        delete: function(id, fn) {
            fn = fn || defaultHandler;
            var url = "/api/positions/del/"+id;
            return api.req.post(url, {}, fn)
        },
    },

    office: {
        search: makeHandler("/api/offices/search"),
        actionFor: makeHandler("/api/offices/{officeId}/action-for/{trackingId}"),

        canSend: function(officeId, trackingId, fn) {
            fn = fn || defaultHandler;
            var url = util.interpolate(
                '/api/offices/{officeId}/can-send/{trackingId}',
                {
                    officeId: officeId,
                    trackingId: trackingId,
                }
            );
            return api.req.post(url, {}, fn)
        },

        incoming: makeHandler("/api/offices/{officeId}/incoming"),
        held: makeHandler("/api/offices/{officeId}/held"),
        dispatched: makeHandler("/api/offices/{officeId}/dispatched"),
        final: makeHandler("/api/offices/{officeId}/final"),

        add: makeHandler("/api/offices/add"),
        get: makeHandler("/api/offices/get"),

        fetch: function(fn) {
            var url = "/api/offices/list";
            return api.req.get(url, {}, fn || defaultHandler)
        },

        nextOffices: makeHandler("/api/offices/{officeId}/next-offices"),

        delete: function(id, fn) {
            fn = fn || defaultHandler;
            var url = "/api/offices/del/"+id;
            return api.req.post(url, {}, fn)
        },
    },

    util: {
        urlFor: makeHandler("/api/util/url-for/{routeName}"),
    },
};
