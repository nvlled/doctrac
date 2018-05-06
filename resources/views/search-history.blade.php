
@extends("layout")

@section("contents")
<section id="search-doc">
<form method="POST" class="search pure-form pure-form-aligned">
    {{ csrf_field() }}
    <fieldset>
        <div class="pure-control-group">
            <label for="name">time received</label>
            <input id="recv-start" name="recv-start" placeholder="from">
            <br>
            <label for="name"></label>
            <input id="recv-end" name="recv-end" placeholder="to">
            <hr>
        </div>
        <div class="pure-control-group">
            <label for="name">campus</label>
            <input id="campusId" name="campusId" type="hidden">
            <input id="campus" name="campus">
        </div>
        <div class="pure-control-group">
            <label for="name">title</label>
            <input id="subject" name="title">
        </div>
        <div class="pure-control-group">
            <label for="name">keyword</label>
            <input id="keyword" name="keyword">
        </div>
        <div class="pure-control-group">
            <label for="name"></label>
            <button type="submit" class="search pure-button pure-button-primary">Search</button>
        </div>
        <div class="pure-controls">
            <ul class="errors"></ul>
            <span class="pure-form-message-inline error">{{$message ?? ""}}</span>
        </div>
    </fieldset>
</form>
<table id="results">
    <thead>
        <tr>
        <th>source office</th>
        <th>destination office</th>
        <th>tracking ID</th>
        <th>time received</th>
        <th>time sent</th>
        </tr>
    </thead>
    <tbody>
        <tr class="templ">
            <td class="src-office"></td>
            <td class="dst-office"></td>
            <td class="trackingId">
                <a href="#"></a>
            </td>
            <td class="arrivalTime"></td>
            <td class="forwardTime"></td>
        </tr>
    </tbody>
</table>

<link rel="stylesheet" href="{{asset('css/rome.min.css')}}">
<script src="{{asset('js/lib/rome.min.js')}}"></script>
<script>
    rome($("input#recv-start")[0]);
    rome($("input#recv-end")[0]);

    var $table = $("table#results tbody");
    var $form  = $("form.search");
    var $rowTempl     = $table.find("tr.templ");
    var $searchButton = $form.find("button.search");
    $rowTempl.detach().removeClass("templ");

    $searchButton.click(function(e) {
        e.preventDefault();
        $searchButton.attr("disabled", true);
        api.route.searchHistory({
            title:        $("input[name=title]").val(),
            keyword:      $("input[name=keyword]").val(),
            campusId:     $("input[name=campusId]").val(),
            timeRecvFrom: $("input[name=recv-start]").val(),
            timeRecvTo:   $("input[name=recv-end]").val(),
            timeSentFrom: $("input[name=recv-start]").val(),
            timeSentTo:   $("input[name=recv-end]").val(),
        }).then(function(resp) {
            $searchButton.attr("disabled", false);
            $table.html("");
            if (!resp) {
                return;
            }
            if (resp.errors) {
                return UI.showErrors($form, resp.errors);
            }
            resp.forEach(function(route) {
                var $row = $rowTempl.clone()
                $row.find(".src-office").text(route.office_name);
                $row.find(".dst-office").text(route.next_office_name);
                $row.find(".title a")
                    .attr("href", route.document_link)
                    .text(route.document_title);
                $row.find(".trackingId a")
                    .attr("href", route.document_link)
                    .text(route.trackingId);
                if (route.prevId)
                    $row.find(".arrivalTime").text(route.arrivalTime);
                $row.find(".forwardTime")
                    .text(route.forwardTime);
                $table.append($row);
            });
        });
    });
</script>
</section>
@endsection
