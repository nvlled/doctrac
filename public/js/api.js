function getInternalError(err) {
    var msg = err.responseJSON.message;
    return {
        errors: {"server" : [msg] },
    };
}

function defaultHandler(resp, errors) {
    console.log("response: ", resp);
    console.warn("errors: ", errors);
}

function makeHandler(url) {
    return function(data, fn) {
        var url_ = util.interpolate(url, data);
        fn = fn || defaultHandler;
        api.req.post(url_, data, fn);
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

    doc: {
        send: makeHandler("/api/docs/send"),
    },

    user: {
        get: function(id, fn) {
            fn = fn || defaultHandler;
            var url = "/api/users/get/"+id;
            return api.req.post(url, {}, fn);
        },
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

            api.req.post(url, {
                name: office.name,
            }, fn);
        },

        fetch: function(fn) {
            var url = "/api/positions/list";
            api.req.get(url, {}, fn || defaultHandler)
        },

        delete: function(id, fn) {
            fn = fn || defaultHandler;
            var url = "/api/positions/del/"+id;
            api.req.post(url, {}, fn)
        },
    },

    office: {
        add: function(office, fn) {
            fn = fn || defaultHandler;
            var url = "/api/offices/add";
            if (!office.name)
                return fn({errors: ["name is required"]});
            if (!office.campus)
                return fn({errors: ["campus is required"]});

            return api.req.post(url, {
                name: office.name,
                campus: office.campus,
            }, fn);
        },

        fetch: function(fn) {
            var url = "/api/offices/list";
            return api.req.get(url, {}, fn || defaultHandler)
        },

        delete: function(id, fn) {
            fn = fn || defaultHandler;
            var url = "/api/offices/del/"+id;
            return api.req.post(url, {}, fn)
        },
    }
};

