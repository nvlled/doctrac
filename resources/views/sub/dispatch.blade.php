
<section id="dispatch">
    <form>
    <p class='user-name hidden'></p>
    <p class='user-office'></p>
    <input name="title" class="full title" placeholder="document name or title">
    <textarea name="details" rows="7" class="full details"
        placeholder="document details"></textarea>
    </form>
    <p class="">
        Attachment:
        <input name="attachment" type="file">
    </p>

    <h3>Office destinations</h3>
    <table class="route">
        <thead>
        <tr>
            <th>id</th>
            <th>name</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    <div class="add-dest">
        <input id="dispatch-officeId" size=30
            name="officeId"
            class="officeId autocomplete"
            placeholder="search for office name"
            data-format="{campus_name} {name}"
            data-url="/api/offices/{officeId}/next-offices">
        <button class='add hidden pure-button pure-button-primary'>add</button>
        <span class='error add-error'><span>
    </div>

    <br>
        <ul class="errors"></ul>
    <div class="center">
        <label><input name="dispatch-type" value="serial" type="radio" checked>serial </label>
        <label><input name="dispatch-type" value="parallel" type="radio">parallel </label>
        <br>
        <button class="half send action pure-button pure-button-primary">Send</button>
        <p class='message' style='color: #050'><p>
    </div>
    <p style="font-size: 15px; color: gray">
    *note: <br>
    serial: documents are passed from one office to another<br>
    parallel: documents are passed to all the offices at the same time
    </p>
    <script src="{{asset('js/sub/dispatch.js')}}"></script>
</section>


