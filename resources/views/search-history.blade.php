

@php
    $layoutName = "anon";
@endphp
@if (Auth::user())
    @php $layoutName = "layout" @endphp
@endif
@extends($layoutName)


@section("contents")
<section id="search-doc">
<form method="POST" class="search ">
    {{ csrf_field() }}
    <div class="row">
        <div class="col-5">
            <input id="query" name="query" class="form-control" placeholder="search">
        </div>
        <div>
        <button type="submit" class="search btn btn-primary form-control">Search</button>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <a class="btn btn-outline-info" data-toggle="collapse" href="#collapseExample"
                role="button" aria-expanded="false" aria-controls="collapseExample">
                set time
            </a>
            @foreach (["title" => "title", "keyword" => "keyword"] as $val => $name)
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="query-type" id="{{$name}}" value="{{$val}}" >
                <label class="form-check-label" for="{{$name}}">
                    {{$name}}
                </label>
            </div>
            @endforeach
        </div>
    </div>

    <div class="row">
        <div class="collapse col-6" id="collapseExample">
            <div class="card card-body">
                <input class="form-control" id="time-start" name="time-start" placeholder="start time">
                <input class="form-control" id="time-end" name="time-end" placeholder="end time">
                <label></label>
                <span class="nobreak">
                    <label class="radio"><input checked type="radio" name="time-type" value="recv"> received</label>
                    <label class="radio"><input type="radio" name="time-type" value="sent"> sent</label>
                    <label class="radio"><input type="radio" name="time-type" value="both"> both</label>
                </span>
            </div>
        </div>
    </div>

    <div class="pure-controls">
        <ul class="errors"></ul>
        <span class="pure-form-message-inline error">{{$message ?? ""}}</span>
    </div>
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
</form>
<table id="results" class="table">
    <thead>
        <tr>
        <th>source office</th>
        <th>destination office</th>
        <th>title</th>
        <th>tracking ID</th>
        <th>time</th>
        </tr>
    </thead>
    <tbody>
        <tr class="templ">
            <td class="src-office"></td>
            <td class="dst-office"></td>
            <td class="title"></td>
            <td class="trackingId">
                <a href="#"></a>
            </td>
            <td class="time">
                <div class="arrivalTime"></div>
                <div class="forwardTime"></div>
            </td>
        </tr>
    </tbody>
</table>
<ul class="page-nav pagination">
    <li class="page-item">
        <a class="page-link" href="#">1</a>
    </li>
</ul>

<link rel="stylesheet" href="{{asset('css/rome.min.css')}}">
<script src="{{asset('js/lib/rome.min.js')}}"></script>
<script>
    rome($("input#time-start")[0]);
    rome($("input#time-end")[0]);

    var $table = $("table#results tbody");
    var $form = $("form.search");
    var $rowTempl = $table.find("tr.templ");
    var $searchButton = $form.find("button.search");
    var pageNav = loadPageNav();

    $rowTempl.detach().removeClass("templ");

    $searchButton.click(function(e) {
        e.preventDefault();
        fetchData(0);
    });

    function fetchData(page) {
        var time = getTime();

        var vals = {};
        vals[getQueryType()] = $("input[name=query]").val();
        UI.buttonWait($searchButton);

        api.route.searchHistory({
            title:        vals["title"],
            keyword:      vals["keyword"],
            campusId:     vals["campusId"],
            timeRecvFrom: time.recvFrom,
            timeRecvTo:   time.recvTo,
            timeSentFrom: time.sentFrom,
            timeSentTo:   time.sentFrom,
            page: page,
        }).then(function(resp) {
            UI.buttonIdle($searchButton);
            $table.html("");
            if (!resp) {
                return;
            }
            if (resp.errors) {
                return UI.showErrors($form, resp.errors);
            }
            var rows = resp.rows;
            pageNav.update(page, resp.numPages);
            rows.forEach(function(route) {
                var $row = $rowTempl.clone()
                $row.find(".src-office").text(route.office_name);
                $row.find(".dst-office").text(route.next_office_name);
                $row.find(".title").text(route.document_title);
                $row.find(".title a")
                    .attr("href", route.document_link)
                    .text(route.document_title);
                $row.find(".trackingId a")
                    .attr("href", route.document_link)
                    .text(route.trackingId);
                if (route.prevId && route.arrivalTime)
                    $row.find(".arrivalTime").text("received: " + route.arrivalTime);
                if (route.forwardTime)
                    $row.find(".forwardTime").text("sent: " + route.forwardTime);
                $table.append($row);
            });
        });
    };

    function disableButtons() {
    }
    function enableButtons() {
    }

    function getQueryType() {
        var sel = "input[name=query-type]";
        var t = $(sel+":checked").val();
        if (!t)
            t = $(sel).first().val();
        return t;
    }

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

    function loadPageNav() {
        var $pageNav = $(".page-nav");
        var $listItem = $pageNav.find("li");
        $listItem.detach();
                $pageNav.html("");
        return {
            update(currentPage, numPages) {
                $pageNav.html("");
                for (var i = 1; i <= numPages; i++) {
                    (function(page) {
                        var $item = $listItem.clone();
                        $item.find("a").text(page);
                        $item.click(function(e) {
                            e.preventDefault();
                            fetchData(page-1);
                        });
                        if (currentPage == page-1) {
                            $item.addClass("active");
                        }
                        $pageNav.append($item);
                    })(i);
                }
            }
        }
    }
</script>
</section>
@endsection
