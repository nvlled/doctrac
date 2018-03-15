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
    this.colMap = opts.colMap || {};

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
            return "<th span=20>"+name+"</th>";
        });
        var $tr = util.jq(["<tr>"].concat(tds).concat(["</tr>"]));
        $thead.append($tr);
    },

    eachRow: function(fn) {
        this.$table.find("tbody > tr").each(function(i) {
            fn($(this), i);
        });
    },

    addRow: function(data) {
        var cols = this.cols;
        var tds = cols.map(function(col) {
            return "<td class='"+col+"'></td>";
        });
        var $tr = util.jq(["<tr>"].concat(tds).concat(["</tr>"]));
        var self = this;
        cols.forEach(function(k) {
            if (k) {
                var fn = self.colMap[k];
                if (typeof fn == "function") {
                    var $td = $tr.find("."+k);
                    fn(data, $td);
                } else {
                    $tr.find("."+k).text(data[k]);
                }
            }
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
            this.$table.find("tbody").append($tr);
        } else {
            data.forEach(function(row) {
                self.addRow(row);
            });
        }
    },

});

var UI = {
    selectOther: function($select, val) {
        var select = $select[0];
        if (select) {
            var count = 10;
            var matches = $select.val() == val;
            while (--count > 0 && matches) {
                var i = select.selectedIndex++;
                if (i == select.children.length-1) {
                    select.selectedIndex++;
                }
                matches = $select.val() == val;
            }
        }
    },

    setSelectedValue: function($select, val) {
        var i = null;
        $select.find("option").each(function(i_) {
            if (this.value == val)
                i = i_;
        });
        if (i != null) {
            $select[0].selectedIndex = i;
        }
    },

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
            api.user.get({id: $input.val()}, function(user) {
                api.user.emit(user);
                if (!user || user.errors) {
                    $output.text("(no match)");
                    return;
                }
                api.user.setSelf({userId: user.id});
                var name = user.firstname + " " + user.lastname;
                $output.text(name + " | " + user.office_name);
            });
        });
    },

    showMessages: function($div, msgs) {
        var $msgs = $div.find("ul.msgs");
        if ($msgs.length == 0) {
            $msgs = $("<ul class='msgs'>");
            $div.append($msgs);
        }
        if (!msgs)
            return;

        if (typeof msgs == "string")
            msgs = [msgs];

        msgs.forEach(function(msg) {
            var $li = $("<li>");
            $li.text(msg);
            $msgs.append($li);
        });
    },

    clearMessages: function($div) {
        $div.find("ul.msgs").html("");
    },

    showErrors: function($div, errors) {
        var $errors = $div.find("ul.errors");
        if ($errors.length == 0) {
            $errors = $("<ul class='errors'>");
            $div.append($errors);
        }
        if (!errors)
            return;

        if (errors.forEach) {
            errors.forEach(function(err) {
                var $li = $("<li>");
                $li.text(err);
                $errors.append($li);
            });
            return;
        }

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

    setText: function($node, text) {
        $node.text(text);
        if (text) {
            $node.show();
        } else {
            $node.hide();
        }
    },

    breakLines: function($node) {
        var text = $node.text();
        $node.html(text.replace(/\n/g, "<BR>"));
    },

    truncatedText: function(text, limit) {
        limit = limit || 180;
        var $div = util.jq([
            "<div>",
            "<span>",
            "</span>",
            "<a href='#' class='action'></a>",
            "</div>",
        ]);
        var $a = $div.find("a");
        var $span = $div.find("span");
        if (text.length < limit) {
            $span.text(text);
            $a.hide();
            return $div;
        }

        $span.text(util.truncate(text, limit));
        $a.text("show more");
        $a.click(function(e) {
            e.preventDefault();
            if ($a.text() == "show more") {
                $span.text(text);
                UI.breakLines($span);
                $a.text("show less")
            } else {
                $span.text(util.truncate(text, limit));
                $a.text("show more")
            }
        });

        return $div;
    },

    addNotification: function(notif) {
        var $a = $("nav.main a.notifications")
            .addClass("has");
        var count = parseInt($a.find(".count").text()) || 0;
        $a.find(".count")
            .text(count+1)
            .removeClass("hidden")
            .show();
    },


    enableButton: function($btn) {
        var text = $btn.data("prev-text");
        $btn.text(text);
        $btn.attr("disabled", false);
    },

    disableButton: function($btn) {
        var text = $btn.text();
        $btn.attr("prev-text", text);
        $btn.text("☯ " + text);
        $btn.attr("disabled", true);
    },

    listenEvents: function(channel, fn) {
        if (!window.io)
            return;
        try {
            var echo = new Echo({
                broadcaster: 'socket.io',
                host: window.location.hostname + ':6001'
            });
            echo.private(channel)
                .notification(fn);
        } catch (e) {
            console.log(e);
        }
    },
}
