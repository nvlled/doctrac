
<section id="dispatch">
    <form>
    <p class='user-name hidden'></p>
    <h3 class='user-office hidden'></h3>
    <input name="title" class="full title" placeholder="document name or title">
    <textarea name="details" rows="7" class="full details"
        placeholder="document details"></textarea>
    <br><hr>
    <textarea name="annotations" rows="4" class="full annotations"
        placeholder="notes/annotations"></textarea>
    </form>

    <p>
        Classification level:
        <select class='classification'>
            @foreach (\App\Enums::$classification as $level)
            <option value='{{$level}}'>{{$level}}</option>
            @endforeach
        </select>
    </p>

    <p class="">
        Attachment:
        <input name="attachment" type="file">
    </p>

    <h3>Office destinations</h3>
    @include("sub/office-selection")

    <br>
        <ul class="errors"></ul>
    <div class="center">
        <label><input name="dispatch-type" value="serial" type="radio" checked>serial </label>
        <label><input disabled name="dispatch-type" value="parallel" type="radio">parallel </label>
        <br>
        <button class="half send action pure-button pure-button-primary">Send</button>
        <p class='message' style='color: #050'><p>
    </div>
    <p style="font-size: 15px; color: gray">
    *note: <br>
    serial: documents are passed from one office to another<br>
    parallel: documents are passed to all the offices at the same time
    </p>
    <script src='{{asset("js/sub/office-selection.js")}}'></script>
    <script src="{{asset('js/sub/dispatch.js')}}"></script>
</section>
