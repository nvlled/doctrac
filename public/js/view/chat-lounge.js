

function ChatLounge(args) {
    args = args || {};

    var ids = {};
    var messages = args.messages || [];
    messages.forEach(function(msg) {
        ids[msg.id] = true;
    });
    // TODO: truncate messages when it gets too large

    var self = {
        username: args.username || "anonymous",
        messages: args.messages || [ /*{username: "asdf", contents: "ajidsfjaisd", loading: false,}*/ ],
        onlineUsers: [],

        _insertMsgIds: ids,

        insertMessage: function(msg) {
            console.log("inserting message", msg);
            if (!msg)
                return;
            if (msg.id && self._insertMsgIds[msg.id])
                return;
            self._insertMsgIds[msg.id] = true;
            self.messages.push(msg)
            console.log("message inserted", msg.id);
        },

        updateDeletedMessage: function(msg) {
            var messages = self.messages;
            var delmsg = null;
            for (var i = 0; i < messages.length; i++) {
                var msg_ = messages[i];
                if (msg.id == msg_.id) {
                    delmsg = msg_;
                    break;
                }
            }
            if (delmsg) {
                delmsg.deleted_at = msg.deleted_at;
                self.vm.redraw();
            }
        },

        deleteMessage: function(msg, e) {
            var args = arguments;
            if (e)
                e.preventDefault();
            if (!msg)
                return;
            if (!confirm("delete message?"))
                return;

            msg.deleted_at = (+new Date());
            msg.loading = true;
            api.lounge.deleteMessage(msg).then(function(resp) {
                msg.loading = false;
                self.vm.redraw();
            });
            self.vm.redraw();
        },

        sendMessage: function() {
            var inputMsg = self.vm.refs["input-msg"].el;
            var msg = inputMsg.value.trim();
            if (!msg)
                return;
            inputMsg.value = "";
            var msg = {
                username: self.username,
                contents: msg,
                loading: true,
            }

            self.insertMessage(msg);
            self.vm.redraw();
            //$.post(sendURL, msg).then(function(resp) {
            api.lounge.sendMessage(msg).then(function(resp) {
                if (!resp)
                    return;
                console.log("send response", resp, resp.id);
                msg.loading = false;
                msg.id = resp.id;
                msg.created_at = resp.created_at;
                self._insertMsgIds[msg.id] = true;
                self.vm.redraw();
            }).fail(function(err) {
                msg.loading = false;
                msg.failed = true;
                console.log("failed to send message", err);
            });
        },

        listen: function() {
            var channel = UI.createChannel("lounge");
            if (!channel)
                return;
            channel.listen("ChatEvent", function(e) {
                var msg = e.msgData;
                if (msg.deleted_at)
                    self.updateDeletedMessage(msg);
                else
                    self.insertMessage(msg);
                self.vm.redraw();
                console.log("chat event", e);
            });
            var chan = UI.joinChannel("lounge").here(function(users) {
                users = users || [];
                self.onlineUsers = users.map(function(user) {
                    return user.username;
                });
                console.log("users", self.onlineUsers);
                self.vm.redraw();
            }).joining(function(user) {
                if (!user)
                    return;
                console.log("user join", user.username);
                self.onlineUsers.push(user.username);
                self.vm.redraw();
            }).leaving(function(user) {
                if (!user)
                    return;
                console.log("user left", user.username);
                util.arrayRemove(self.onlineUsers, user.username);
                self.vm.redraw();
            });
            console.log("join channel", chan);
        },

        isOwn: function(msg) {
            return self.username == msg.username;
        },
    }
    self.vm = domvm.createView(ChatLounge.View, self);
    self.listen();

    return self;
}

ChatLounge.View = function(vm, api) {
    var el = domvm.defineElement;
    var tx = domvm.defineText;
    var cm = domvm.defineComment;
    var ie = domvm.injectElement;

    var sheet = j2c.sheet({
        ".chat-lounge": {
            width: "800px",
        },
        ".messages": {
            "border": "1px solid #552",
            "border-radius": "5px",
        },
        ".online-users": {
        },
        ".online-username": {
            padding: "2px 5px",
        },
        ".deleted": {
            "color": "gray",
            "font-style": "italic",
        },
        ".msg": {
            "padding": "10px",
        },
        ".msg-footer": {
            color: "gray",
            "font-size": "9px",
            "border-bottom": "1px solid #ddd",
        },
        ".msg-date": {
            "padding": "5px",
            "::before": { content: "'('" },
            "::after": { content: "')'" },
        },
        ".full-width": {
            display: "block",
            "max-width": "98%",
            "width": "500px",
            "color": "blue",
        },
        ".red": { "color": "red" },
        ".blue": { "color": "blue" },
    });

    var cl = function() {
        var classNames = [];
        for (var i = 0; i < arguments.length; i++) {
            var name = sheet[arguments[i]];
            if (!name)
                console.warn("select is not defined", arguments[i]);
            else
                classNames.push(name);
        }
        return classNames.join(" ");
    }

    return function() {
        var zerop = function(n) {
            var s = n.toString();
            var t = 2-s.length;
            if (t < 0)
                return s;
            return "0".repeat(t)+s;
        }
        var formatDate = function(dateStr) {
            if (!dateStr)
                return;
            var d = new Date(dateStr);
            return d.getMonth() + "/" + d.getDate() + " " +
                zerop(d.getHours()) + ":" + zerop(d.getMinutes());
        }
        var messages = api.messages.map(function(msg) {
            var domContents = $("<pre>"+msg.contents.split("\n").join("<br>")+"</pre>")[0];

            return el("div", {class: cl("msg")}, [
                msg.loading && el("span", "@"),
                msg.failed && el("span", "[failed to send]"),
                el("span", msg.username),
                el("span", " : "),

                msg.deleted_at
                    ? el("span", {class: cl("deleted")}, "(deleted)")
                    : el("span", {_ref: Math.random()+""}, [el("pre", msg.contents)]),
                    //: el("span", {_ref: Math.random()+""}, [ie(domContents)]),


                el("div", {class: cl("msg-footer")}, [
                    msg.created_at && el("span", {class: cl("msg-date")}, formatDate(msg.created_at)),
                    api.isOwn(msg) && el("a", {
                        href: "#",
                        onclick: [api.deleteMessage, msg],
                    }, "✗"),
                ]),
            ]);
        });
        var onlineUsers = api.onlineUsers.map(function(username) {
            return el("span", {class: cl("online-username")}, [
                username
            ]);
        });
        return el("div", { class: cl("chat-lounge") }, [
            el("div", { class: cl("messages") }, messages),
            el("div", {class: cl("online-users")}, [
                el("span", "online users: "),
                el("em", onlineUsers),
            ]),
            el("textarea.message", {
                _ref: "input-msg",
                name: "message",
                "class": cl("full-width"),
            }),
            el("button.send.full-width", {
                name: "send",
                onclick: [api.sendMessage],
            }, "send"),
            el("style", sheet.toString()),
        ]);
    }
}
