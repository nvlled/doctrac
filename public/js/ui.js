
var UI = {

    queryUser: function(inputSel, outputSel) {
        var $input = $(inputSel);
        var $output = $(outputSel);
        console.log($input, $output);
        $input.change(function() {
            $output.text("");
            api.user.get($input.val(), function(user) {
                if (!user) {
                    $output.text("(no match)");
                    return;
                }
                if (user.errors)
                    return;
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
