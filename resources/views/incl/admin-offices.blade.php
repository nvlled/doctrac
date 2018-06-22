

<div>
<form name="offices" class="form-inline">
    <input type="hidden" name="campusCode">
    <input class="form-control mr-2" name="campus-name" placeholder="campus name">
    <input class="form-control mr-2" name="office-name" placeholder="office name">
    <div class="form-check form-check-inline">
        <input class="form-check-input" type="checkbox" name="gateway" value="✓" id="gateway">
        <label class="form-check-label" for="gateway">
            records office?
        </label>
    </div>
    <button class="add btn btn-primary">add</button>
    <div class="col-12"></div>
    <div class="ml-2 error text-danger"></div>
</form>

<table id="offices" class="table">
    <thead>
        <tr>
            <th>Campus Code</th>
            <th>Campus Name</th>
            <th>Office Name</th>
    
            <th>
                <div class="row">
                    <label class="col-lg-3 col-md-12 col-form-label">
                        action 
                    </label>
                    <div class="col-lg-8 col-md-12">
                    <input class="form-control search" size="4" placeholder="search">
                    </div>
                </div>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr class="templ">
            <td class="campus_code"></td>
            <td class="campus_name"></td>
            <td class="name"></td>
            <td class="action"></td>
        </tr>
    </tbody>
</table>
<script src="{{asset('js/awesomplete.min.js')}}"></script>
<script>
    $(function() {
        Promise.all([
            api.campus.fetch(),
            api.office.fetch(),
        ]).then(function(resp) {
            load(resp[0], resp[1]);
        });
    });
function load(campuses, offices) {
    var $form = $("form[name=offices]");
    var $table = $("table#offices");
    var $tbody = $table.find("tbody");
    var $trTempl = $table.find("tr.templ").detach().removeClass("templ");

    var $searchInput = $("input.search");

    var campusNameInput = $("input[name=campus-name]")[0];
    var campusCodeInput = $("input[name=campusCode]")[0];

    new Awesomplete(campusNameInput, {
        filter: function(obj, input) {
            var off = obj.value;
            var name = off.name || "";
            var matches = !! name.match(new RegExp(input, "i"));
            return matches;
        },
        replace: function(obj, input) {
            this.input.value = obj.value.name;
        },
        item: function(obj, input) {
            var off = obj.value;
            var $li = $("<li>");
            $li.text(off.name);
            return Awesomplete.ITEM(off.name, input.match(/[^,]*$/)[0]);
        },

        list: campuses.map(function(off) {
                return off;
            }),
    });
    
    campusNameInput.addEventListener("awesomplete-select", function(data) {
        console.log(data, data.text.value.id, campusCodeInput);
        campusCodeInput.value = data.text.value.code;
    });

    renderRows();

    $form.find("input[name=campus-code]").change(function(e) {
        var input = this;
        campuses.forEach(function(c) {
            if (c.code == input.value) 
                $form.find("input[name=campus-name]").val(c.name);
        });
    });
    $form.submit(function(e) {
        e.preventDefault();
        var $err = $form.find(".error");
        var code = $form.find("input[name=campusCode]").val();
        var name = $form.find("input[name=office-name]").val();
        var isGateway = $form.find("input[name=gateway]")[0].checked;

        UI.formWait($form);
        $.post("/api/offices/add", {
            campusCode: code,
            name: name,
            isGateway: isGateway ? true : null,
        }).then(function(resp) {
            UI.formIdle($form);
            if (resp.errors) {
                var errMsg = UI.formatErrors(resp.errors);
                $err.text(errMsg);
                return;
            }
        });
    });
    $searchInput.change(function() {
        renderRows();
    });

    function renderRows() {
        $tbody.html("");
        var search = $searchInput.val();
        var rx = new RegExp(search, "i");
        (offices || []).forEach(function(c) {
            if (c.campus_code.match(rx) || 
                c.campus_name.match(rx) || 
                c.name.match(rx)) {

                var $tr = UI.mapTextByClass($trTempl.clone(), c);
                if (c.gateway) {
                    $tr[0].classList.add("records");
                }
                $tbody.append($tr);
            }

        });
    }
}
</script>
<style>
tr.records {
    color: #a00;
}
</style>
</div>
