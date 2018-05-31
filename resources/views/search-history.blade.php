
@extends("layout")

@section("contents")
<section id="search-doc">
<form method="POST" class="search pure-form pure-form-aligned">
    {{ csrf_field() }}
    <fieldset>
        <div class="grid-lr">
            <div class="grid-left">
                <div class="">
                    <label for="name">title</label>
                    <input id="subject" name="title">
                </div>
                <div class="">
                    <label style="position: relative; top: 10px" for="name">campus</label>
                    <input id="campusId" name="campusId" type="hidden">
                    <input id="campus" name="campus" class="typeahead">
                </div>
                <div class="">
                    <label for="name">keyword</label>
                    <input id="keyword" name="keyword">
                </div>
            </div>
            <div class="grid-right">
                <label for="name">time</label>
                <input id="time-start" name="time-start" placeholder="from">
                <br>
                <label for="name"></label>
                <input id="time-end" name="time-end" placeholder="to">
                <br>
                <label></label>
                <span class="nobreak">
                    <label class="radio"><input checked type="radio" name="time-type" value="recv"> received</label>
                    <label class="radio"><input type="radio" name="time-type" value="sent"> sent</label>
                    <label class="radio"><input type="radio" name="time-type" value="both"> both</label>
                </span>
                <br>
                <div class="center">
                <button type="submit" class="search pure-button pure-button-primary">Search</button>
                </div>
            </div>
        </div>
        <div class="pure-controls">
            <ul class="errors"></ul>
            <span class="pure-form-message-inline error">{{$message ?? ""}}</span>
        </div>
        <style>
        label {
            text-align: center !important;
            display: inline-block;
            width: 90px !important;
        }
        label.radio {
            width: inherit !important;
            padding: 0px !important;
            margin: 0px !important;
        }
        #site-wrapper > .site-contents {
            background-color: #444;
            background-image: url(/images/bg.jpg);
            background-size: 110%;
            background-repeat: repeat-y;
        }
        </style>
        <link rel="stylesheet" href="/css/typeahead.css">
        <script src="{{asset('/js/lib/typeahead.bundle.min.js')}}"></script>
        <script>
        api.campus.fetch().then(function(campuses) {
            campuses = campuses || [];

            var $input = $("input#campus");
            $input.typeahead({
                hint: true,
                highlight: true,
                minLength: 1
            }, {
                name: "campuses",
                source: matcher,
                display: function(obj) {
                    return obj.name;
                },
            });
            $input.bind('typeahead:select', function(ev, campus) {
                console.log("campus id", campus.id);
                $("input#campusId").val(campus.id);
            });

            function matcher(query, cb) {
                var result = [];
                campuses.forEach(function(c) {
                    if (c.name.toLowerCase().search(query.toLowerCase()) >= 0)
                        result.push(c);
                });
                cb(result);
            }
        });
        </script>
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
    rome($("input#time-start")[0]);
    rome($("input#time-end")[0]);

    var $table = $("table#results tbody");
    var $form  = $("form.search");
    var $rowTempl     = $table.find("tr.templ");
    var $searchButton = $form.find("button.search");
    $rowTempl.detach().removeClass("templ");

    $searchButton.click(function(e) {
        e.preventDefault();
        $searchButton.attr("disabled", true);
        var time = getTime();
        api.route.searchHistory({
            title:        $("input[name=title]").val(),
            keyword:      $("input[name=keyword]").val(),
            campusId:     $("input[name=campusId]").val(),
            timeRecvFrom: time.recvFrom,
            timeRecvTo:   time.recvTo,
            timeSentFrom: time.sentFrom,
            timeSentTo:   time.sentFrom,
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

        function getTime() {
            var from = $("input#time-start");
            var to = $("input#time-end");
            var type = $("input[name=time-type]:checked").val();
            if (type == "recv") {
                return {
                    recvFrom: from.val(),
                    recvTo:   to.val(),
                }
            }
            if (type == "sent") {
                return {
                    sentFrom: from.val(),
                    sentTo:   to.val(),
                }
            }
            if (type == "both") {
                return {
                    recvFrom: from.val(),
                    recvTo:   to.val(),
                    sentFrom: from.val(),
                    sentTo:   to.val(),
                }
            }
            return {};
        }
    });
</script>
</section>
@endsection
