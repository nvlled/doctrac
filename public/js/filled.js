
function fillForms() {
    var nodes = document.querySelectorAll("input, textarea, select, button");
    Array.prototype.forEach.call(nodes, function(node) {
        if (node.tagName == "INPUT")
            fillInput(node);
        else if (node.tagName == "TEXTAREA")
            fillTextarea(node);
    });

    function fillInput(input) {
        switch (input.type) {
            case "text":
                input.value = randomSentence();
                break;
        }
    }

    function fillTextarea(textarea) {
        textarea.textContent = randomParagraph();
    }

    function randomString() {
        var alphas = "abcdefghijklmnopqrstuvwxyz".split("");
        return ((+new Date)*Math.random()*99999).toString(36)
            .replace(/\d/g, function(d) {
                var i = Math.floor(Math.random()*alphas.length);
                return alphas[i];
            });
    }

    function randomStrings(n) {
        n = n || 5;
        var text = "";
        for (var i = 0; i < n; i++)
            text += randomString();
        return text;
    }

    function randomSentence() {
        var str = randomStrings();
        var text = "";
        while (str) {
            var i = Math.random()*3 + 2;
            text += str.slice(0, i) + " ";
            str = str.slice(i);
        }
        text = text.slice(0, 1).toUpperCase() + text.slice(1);
        return text.trim()+".";
    }

    function randomParagraph(n) {
        n = n || (Math.random()*5 + 3);
        var text = "";
        for (var i = 0; i < n; i++)
            text += randomSentence() + "\n";
        return text.trim();;
    }

}

window.addEventListener("load", function() {
    var $btn = util.jq([
        "<button class='autofill'>AUTO-FILL FORM</button>",
    ]);
    $btn.click(fillForms);
    $("body").append($btn);
});
