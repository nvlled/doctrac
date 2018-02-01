function getInternalError(err) {
    var msg = err.responseJSON.message;
    return {
        errors: {"server" : [msg] },
    };
}
function defaultHandler(resp, errors) {
    console.log("response: ", resp);
    console.log("errors: ", errors);
}

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

    user: {
        add: function(user, fn) {
            fn = fn || defaultHandler;
            var url = "/api/users/add";
            api.req.post(url, user, fn);
        },

        fetch: function(fn) {
            var url = "/api/users/list";
            api.req.get(url, {}, fn || defaultHandler)
        },

        delete: function(id, fn) {
            fn = fn || defaultHandler;
            var url = "/api/users/del/"+id;
            api.req.post(url, {}, fn)
        },
    },

    privilege: {
        add: function(office, fn) {
            fn = fn || defaultHandler;
            var url = "/api/privileges/add";
            if (!office.name)
                return fn({errors: ["name is required"]});

            api.req.post(url, {
                name: office.name,
            }, fn);
        },

        fetch: function(fn) {
            var url = "/api/privileges/list";
            api.req.get(url, {}, fn || defaultHandler)
        },

        delete: function(id, fn) {
            fn = fn || defaultHandler;
            var url = "/api/privileges/del/"+id;
            api.req.post(url, {}, fn)
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

            api.req.post(url, {
                name: office.name,
                campus: office.campus,
            }, fn);
        },

        fetch: function(fn) {
            var url = "/api/offices/list";
            api.req.get(url, {}, fn || defaultHandler)
        },

        delete: function(id, fn) {
            fn = fn || defaultHandler;
            var url = "/api/offices/del/"+id;
            api.req.post(url, {}, fn)
        },
    }
};
