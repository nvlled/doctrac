

<section id="doc-history">
    <div class="col-12">
        <input id="document" value="{{$doc ?? ""}}" type="hidden">
        <input id="user" value="{{$user->toJson()}}" type="hidden">

        <div class="row ">
            <div class="col-8">
                <h2 class="title ">
                    {{$doc->title}}
                    <small class='trackingId'>({{$doc->trackingId}})</small>
                </h2>
            </div>
        </div>

        <div class="row">
            <strong class="col-2 text-right">state </strong>
            <span class="info doc-state {{$doc->state}}">
                {{$doc->state}}
            </span>
        </div>
        <div class="row">
            <strong class="col-2 text-right">type </strong>
            <span class="doc-state ">
                {{$doc->type}}
            </span>
        </div>
        <div class="row">
            <strong class="col-2 mr-2 text-right">classification </strong>
            <span class="doc-state ">
                {{$doc->classification}}
            </span>
        </div>
        <div class="row">
            <strong class="col-2 text-right">action </strong>
            <span class="doc-state {{$doc->action}}">
                {{$doc->action ?? "--"}}
            </span>
        </div>

        <div class="row">
            <strong class="col-2 text-right">details
            </strong>
            <pre class="details">{{trim($doc->details ?? "--")}}</pre>
            <div class="ml-2">
                <button type="button" class="edit-details btn btn-sm btn-outline-secondary">edit</button>
            </div>
        </div>
        <div class='row info attachment'>
            <strong class="col-2 text-right" for="customFile">attachment</strong>
            @php $url = $doc->attachment_url @endphp
            <a href="{{$url ? $url :  "#" }}"
                target="{{textIf($url, '_blank')}}">{{$doc->attachment_filename ?? "--"}}
            </a>
            <span class="col offset-0 {{hiddenIf(!$user || !$user->ownsDocument($doc))}}">
                <input class="hidden" type="file" name="attachment">
                <button class="btn btn-sm btn-outline-secondary change small">change</button>
                <button class="btn btn-sm btn-success d-none upload small">upload new file</button>
                <script>
                </script>
            </span>
        </div>
        <div class="row text-danger">
            <ul class="errors inline indent15"></ul>
        </div>

        <table class='table full routes'>
            <colgroup>
                <col span="1" style="">
                <col span="1" style="">
                <col span="1" style="">
                <col span="1" style="">
                <col span="1" style="">
            </colgroup>
            <thead>
                <tr>
                    <th>office name</th>
                    <th>actions taken</th>
                    <th>time elapsed</th>
                    <th class="d-none">sender/receiver</th>
                    @if ($doc->type == "serial")
                        <th>status</th>
                        <th>annotations</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @php
                    $root = @$routes[0];
                @endphp
                @foreach ($routes as $route)
                    <tr>
                        @php
                            $office = optional($route->office);
                        @endphp
                        <td class='{{textIf($doc->type == "parallel", "indent-{$route->depth}")}}'>
                            <a href="{{$route->link}}">{{$office->complete_name}}</a>
                        </td>
                        <td>{{$route->actionTaken}}</td>
                        <td>{{$route->time_elapsed}}</td>
                        <td class="d-none">
                            @if ($route->receiver)
                                received by: {{$route->receiver->fullname}}
                            @endif
                            @if ($route->sender)
                                sent by: {{$route->sender->fullname}}
                            @endif
                        </td>
                        @if ($doc->type == "serial")
                            <td>{{$route->approvalState}}</td>
                            <td><pre>{{$route->annotations}}<pre></td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
            <style>
                input[type=file] { display : none; }
            </style>
            <input id="trackingId" type="hidden" value="{{$doc->trackingId}}">
            <script>
                $("button.edit-details").click(function() {
                    UI.editTextDialog("Edit Details", $(".details").text().trim(),
                        function(newDetails) {
                            var data = {
                                trackingId: $("#trackingId").val(),
                                details: newDetails,
                            }
                            return api.doc.updateDetails(data, function(resp) {
                                if (resp && resp.errors)
                                    return {error: Object.values(resp.errors).join(" / ")};
                                $(".details").text(newDetails);
                                return {done: true};
                            });
                        }
                    );
                });
                window.addEventListener("load", function() {
                    var $inputFile = $("input[name=attachment");
                    var $uploadBtn = $("button.upload");
                    var trackingId = $("input#trackingId").val().trim();
                    var $fileLink  = $("div.attachment a");
                    var channel = UI.createChannel("doc."+trackingId);
                    var $inputFile = $(".info.attachment input[name=attachment]");
                    var $changeBtn = $(".info.attachment button.change");

                    $changeBtn.click(function(e) {
                        e.preventDefault();
                        $inputFile.click();
                        console.log("X");
                    });
                    $inputFile.change(function() {
                        if ($inputFile[0].files.length > 0) {
                            $changeBtn.text($inputFile[0].files[0].name);
                            $uploadBtn
                                .removeClass("d-none")
                                .attr("disabled", false)
                                .show();
                        } else {
                            $uploadBtn.addClass("d-none").hide();
                        }
                    });

                    $uploadBtn.click(function(e) {
                        var file = $inputFile[0].files[0];
                        UI.uploadFile(trackingId, $uploadBtn, file)
                            .then(function(href) {
                                UI.flashMessage("file uploaded: " + file.name, "file-upload");
                                $fileLink.text(file.name).attr("href", href);
                                $uploadBtn.addClass("hidden").hide();
                                $changeBtn.text("change");
                            });
                    });

                    if (!channel)
                        return;

                    channel.listen("DocUpdate", function(e) {
                        console.log("document update", e);
                        var msg = $("<a href='#'>document updated, click to reload</a>")[0];
                        msg.onclick = function(e) {
                            e.preventDefault();
                            location.reload();
                        };
                        UI.flashMessage(msg, "doc-update");
                    });
                });
            </script>
        </table>
    </div>
</section>
