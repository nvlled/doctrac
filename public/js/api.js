
var api = {
    defaultHandler: function(resp, errors) {
        console.log("response: ", resp);
        console.log("errors: ", errors);
    },

    user: {
        add: function(user, fn) {
            fn = fn || this.defaultHandler;
            var url = "/api/users/add";
            if (!user.name)
                return fn({errors: ["name is required"]});

            $.post(url, user, fn);
        },

        fetch: function(fn) {
            var url = "/api/users/list";
            $.get(url, {}, fn || this.defaultHandler)
        },

        delete: function(id, fn) {
            fn = fn || this.defaultHandler;
            var url = "/api/users/del/"+id;
            $.post(url, fn)
        },
    },

    privilege: {
        add: function(office, fn) {
            fn = fn || this.defaultHandler;
            var url = "/api/privileges/add";
            if (!office.name)
                return fn({errors: ["name is required"]});

            $.post(url, {
                name: office.name,
            }, fn);
        },

        fetch: function(fn) {
            var url = "/api/privileges/list";
            $.get(url, {}, fn || this.defaultHandler)
        },

        delete: function(id, fn) {
            fn = fn || this.defaultHandler;
            var url = "/api/privileges/del/"+id;
            $.post(url, fn)
        },
    },

    position: {
        add: function(office, fn) {
            fn = fn || this.defaultHandler;
            var url = "/api/positions/add";
            if (!office.name)
                return fn({errors: ["name is required"]});

            $.post(url, {
                name: office.name,
            }, fn);
        },

        fetch: function(fn) {
            var url = "/api/positions/list";
            $.get(url, {}, fn || this.defaultHandler)
        },

        delete: function(id, fn) {
            fn = fn || this.defaultHandler;
            var url = "/api/positions/del/"+id;
            $.post(url, fn)
        },
    },

    office: {
        add: function(office, fn) {
            fn = fn || this.defaultHandler;
            var url = "/api/offices/add";
            if (!office.name)
                return fn({errors: ["name is required"]});
            if (!office.campus)
                return fn({errors: ["campus is required"]});

            $.post(url, {
                name: office.name,
                campus: office.campus,
            }, fn);
        },

        fetch: function(fn) {
            var url = "/api/offices/list";
            $.get(url, {}, fn || this.defaultHandler)
        },

        delete: function(id, fn) {
            fn = fn || this.defaultHandler;
            var url = "/api/offices/del/"+id;
            $.post(url, fn)
        },
    }
};
