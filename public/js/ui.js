
function Table($table, opts) {
    this.$table = $table;
    this.url = opts.url || "";
    this.actions = opts.actions || {};
    this.colNames = opts.colNames || {};

    var cols = opts.cols || [];
    //if (cols.indexOf("actions") < 0)
    //    cols = cols.concat(["actions"]);
    this.setColumns(cols);
    this.selectableRows = opts.selectableRows || false;
    if (this.selectableRows) {
        $table.addClass("sel");
    }

    if (!$table.find("tbody"))
        $table.append("<tbody>");
}

Table.prototype = Object.assign(Table.prototype, {
    setColumns: function(cols) {
        var $table = this.$table;
        this.cols = cols;
        var $thead = $table.find("thead");
        if (!$thead) {
            $thead = $("<thead>");
            $table.append($thead);
        }
        $thead.html("");
        var self = this;
        var tds = cols.map(function(col) {
            var name = self.colNames[col];
            if (name == null)
                name = col;
            return "<th>"+name+"</th>";
        });
        var $tr = util.jq(["<tr>"].concat(tds).concat(["</tr>"]));
        $thead.append($tr);
    },

    eachRow: function(fn) {
        this.$table.find("tbody > tr").each(function() {
            fn($(this));
        });
    },

    addRow: function(data) {
        var cols = this.cols;
        var tds = cols.map(function(col) {
            return "<td class='"+col+"'>";
        });
        var $tr = util.jq(["<tr>"].concat(tds).concat(["</tr>"]));
        cols.forEach(function(k) {
            if (k)
                $tr.find("."+k).text(data[k]);
        });
        $tr.data("value", data);
        if (this.selectableRows) {
            $tr.click(function(e) {
                $tr.toggleClass("sel");
            });
        }
        this.$table.find("tbody").append($tr);
    },

    fetchData: function(params) {
        var self = this;
        api.req.get(this.url, params || {}, function(resp) {
            if (resp && resp.errors) {
                UI.showErrors(self.$table, resp.errors);
            } else {
                self.loadData(resp);
            }
        });
    },

    clearData: function() {
        this.$table.find("tbody").html("");
    },

    loadData: function(data) {
        var self = this;
        this.clearData();
        if (!data || data.length == 0) {
            var n = this.cols.length;
            var $tr = util.jq([
                "<tr>",
                "<td colspan='"+n+"' class='center'><i>(none)</i></td>",
                "</tr>",
            ]);
            console.log("X");
            this.$table.find("tbody").append($tr);
        } else {
            data.forEach(function(row) {
                self.addRow(row);
            });
        }
    },

});

var UI = {

    createTable: function($table, opts) {
        return new Table($table, opts);
    },

    queryUser: function(inputSel, outputSel) {
        var $input = $(inputSel);
        var $output = $(outputSel);
        api.user.self()
           .then(function(user) {
               if (!user)
                   return;
               var name = user.firstname + " " + user.lastname;
               $input.val(user.id);
               $output.text(name + " | " + user.office_name);
           });
        $input.change(function() {
            $output.text("");
            api.user.get($input.val(), function(user) {
                api.user.emit(user);
                if (!user || user.errors) {
                    api.user.clearSelf();
                    $output.text("(no match)");
                    return;
                }
                api.user.setSelf({userId: user.id});
                var name = user.firstname + " " + user.lastname;
                $output.text(name + " | " + user.office_name);
            });
        });
    },
    
    showErrors: function($div, errors) {
        var $errors = $div.find("ul.errors");
        if ($errors.length == 0) {
            $errors = $("<ul class='errors'>");
            $div.append($errors);
        }
        if (!errors)
            return;
        Object.keys(errors).forEach(function(errName) {
            var subErrors = errors[errName];
            if (subErrors.forEach) {
                subErrors.forEach(function(err) {
                    var $li = $("<li>");
                    $li.text(err);
                    $errors.append($li);
                });
            } else if (typeof subErrors == "string") {
                var $li = $("<li>");
                $li.text(subErrors);
                $errors.append($li);
            }
        });
    },

    clearErrors: function($div) {
        var $errors = $div.find("ul.errors");
        if ($errors.length > 0) {
            $errors.html("");
        }
    },
}
