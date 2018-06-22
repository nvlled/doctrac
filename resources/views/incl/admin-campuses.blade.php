

<form name="campuses" class="form-inline">
    <input class="form-control mr-2" name="campus-code" placeholder="code">
    <input class="form-control mr-2" name="campus-name" placeholder="name">
    <button class="add btn btn-primary">add</button>
    <div class="col-12"></div>
    <div class="ml-2 error text-danger"></div>
</form>
<table id="campuses" class="table">
    <thead>
    <tr>
        <th>code</th>
        <th>name</th>
        <th>action</th>
    </tr>
    </thead>
    <tbody>
        <tr class="templ">
            <td class="code"></td>
            <td class="name"></td>
            <td class="action"></td>
        </tr>
    </tbody>
</table>
<script>
$(function() {
    var $form = $("form[name=campuses]");
    var $table = $("table#campuses");
    var $tbody = $table.find("tbody");
    var $trTempl = $table.find("tr.templ").detach().removeClass("templ");
    $.getJSON("/api/campuses/list").then(function(campuses) {
        $tbody.html("");
        (campuses || []).forEach(function(c) {
            var $tr = UI.mapTextByClass($trTempl.clone(), c);
            $tbody.append($tr);
        });
    });
    $form.find("button.add").click(function(e) {
        e.preventDefault();
        var $err = $form.find(".error");
        var code = $form.find("input[name=campus-code]").val();
        var name = $form.find("input[name=campus-name]").val();
        $err.text("");
        UI.formWait($form);
        $.post("/api/campuses/add", {
            code: code,
            name: name,
        }).then(function(resp) {
            UI.formIdle($form);
            if (resp.errors) {
                var errMsg = UI.formatErrors(resp.errors);
                $err.text(errMsg);
                return;
            }
            location.reload();
        });
    });
});
</script>

