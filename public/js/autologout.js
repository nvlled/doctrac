
var autologout = {
    MINUTES_TIMEOUT: 20,
    WARN_MESSAGE: "You are about to be logged out in",

    lastActive: +new Date(),
    intervalId: null,

    perform: function() {
        this.lastActive = +new Date();
        this.$dialog.hide();
    },

    start: function() {
        var self = this;
        var perform = this.perform.bind(this);
        window.onmousemove = perform;
        window.onmouseup = perform;
        window.onkeyup = perform;

        this.intervalId = setInterval(function() {
            self.update();
        }, 500);
        this.createWarning();
    },

    createWarning: function() {
        var $dialog = this.$dialog = util.jq([
            "<div class='autologout'>",
            this.WARN_MESSAGE,
            "<span class='seconds'></span>",
            "</div>",
        ]);
        $dialog.hide();
        $("body").append($dialog);
    },

    update: function() {
        var now = +new Date();
        var msElapsed = now - this.lastActive;
        var minutesElapsed = (msElapsed/1000)/60;
        var secondsLeft = Math.floor((this.MINUTES_TIMEOUT - minutesElapsed) * 60);
        if (secondsLeft < 20) {
            this.$dialog.show();
            this.$dialog.find(".seconds").text(secondsLeft);
        }

        if (minutesElapsed > this.MINUTES_TIMEOUT) {
            this.lastActive = +new Date();
            clearInterval(this.intervalId);
            api.user.logout().then(function() {
                util.redirect("/");
            });
        }
    },
}

$(function() {
    api.user.self().then(function(user) {
        if (user)
            autologout.start();
    });
});
