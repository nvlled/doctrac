
var api = {
    defaultHandler: function(resp, errors) {
        console.log("response: ", resp);
        console.log("errors: ", errors);
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
