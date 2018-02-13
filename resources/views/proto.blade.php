@extends("layout")

@section("contents")

<section id="session">
    <h3>Login as</h3>
    <input id="session-userid" name="userId"
        size=7
        class="userId autocomplete local-save "
        placeholder="search for user or office name"
        data-format="{lastname}, {firstname} | ({officeId}) {office_name}"
        data-url="/api/users/search">
    <br>
    <script>
    $("#session input#session-userid").change(function() {
        api.user.setSelf({userId: this.value});
    });
    </script>
</section>

<section id="doc-history">
    <input name="trackingId"
        id="doc-history-userid"
        class="half trackingId autocomplete local-save"
        placeholder="tracking ID"
        data-hidetext=true
        data-format="{title}"
        data-key="trackingId"
        data-url="/api/docs/search">
    <h3 class="title">
        <span class='contents'></span>
        (<small class='type'>*</small>)
    </h3>
    <pre class='details'></pre>
    <p>Routes</p>
    <h4 class='origin hidden'>Document origin: <span class='contents'></span></h4>
    <table class='full'>
        <thead>
        <tr>
            <th>id</th>
            <th>campus</th>
            <th>office</th>
            <th>status</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    <br>
    <div class="center">
        <div class="send-data">
            <textarea name="annotation" rows="5" class="full annots"
            placeholder="comments, notes or annotation" ></textarea>
            <br>
            Destination: <select class="offices"></select>
        </div>
        <button class='action half'>SEND / RECEIVE / ABORT SEND / </button>
    </div>
    <script src="{{asset('js/sub/routes.js')}}"></script>
</section>

<section id="agent">
    <h3 class='center'>(<span class='office-id'>0000</span>)
        <span class='office-name'>office name</span></h3>
    <p class='center'><span class='user-name'>user name</span></p>
    <div>
        <h4>Incoming</h4>
        <ul id="incoming">
            <em>(none)</em>
        </ul>
    </div>
    <div>
        <h4>Processing</h4>
        <ul id="held">
            <em>(none)</em>
        </ul>
    </div>
    <div>
        <h4>Delivering</h4>
        <ul id="dispatched">
            <em>(none)</em>
        </ul>
    </div>

    <div>
        <h4>Received/Final</h4>
        <ul id="final">
            <em>(none)</em>
        </ul>
    </div>

    <hr>
    <div id="view-document">
        <h2><span class='title'>doc title</span> (<small class='trackingId'>tracking ID</small>)</h2>
        <p class=''>status: <span class='status'>document details</span></p>
        <pre>details: <span class='details'></span></pre>
        <pre>annotations: <span class='annotations'></span></pre>

        <div class=''>
            <button class='action hidden'>send/receive</button>
        </div>
        <pre>seen by: <span class='seen-by'></span></pre>
        <hr>
        <p>activity log: </p>
        <ul class='activities'>
        </ul>
    </div>

    <script src="{{asset('js/sub/agent.js')}}"></script>
</section>

<section id="dispatch">
    <form>
    <p class='user-name'></p>
    <p class='user-office'></p>
    <hr>
    <button class="rand">random ID</button>
    <input name="trackingId" class="half trackingId" placeholder="tracking ID">
    <input name="title" class="full title" placeholder="document name or title">
    <textarea name="details" rows="7" class="full details"
        placeholder="document details"></textarea>
    </form>
    <p class="hidden">Attachment: <input name="attachment" type="file"></p>

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
            data-format="{campus} {name}"
            data-url="/api/offices/search">
        <button class='add hidden'>add</button>
        <span class='error add-error'><span>
    </div>

    <br>
        <ul class="errors"></ul>
    <div class="center">
        <label><input name="dispatch-type" value="serial" type="radio" checked>serial </label>
        <label><input name="dispatch-type" value="parallel" type="radio">parallel </label>
        <br>
        <button class="half send action">Send</button>
        <p class='message' style='color: #050'><p>
    </div>
    <p style="font-size: 15px; color: gray">
    *note: <br>
    serial: documents are passed from one office to another<br>
    parallel: documents are passed to all the offices at the same time
    </p>
    <script src="{{asset('js/sub/dispatch.js')}}"></script>
</section>



<section id="context">
    <h2>offices</h2>
    <div class="add-office">
        <input class="campus-name" placeholder="campus">
        <input class="office-name" placeholder="office">
        <button class="add">add</button>
        <table>
            <thead>
            <tr>
                <th>id</th>
                <th>campus</th>
                <th>office</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    <script src="{{asset('js/sub/add-office.js')}}"></script>
</section>

<section id="context">
    <h2>staff positions</h2>
    <div class="positions">
        <input class="pos-name" placeholder="position title or name">
        <button class="add">add</button>
        <table>
            <thead>
            <tr>
                <th>id</th>
                <th>name</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    <script src="{{asset('js/sub/positions.js')}}"></script>
</section>

<section id="context">
    <h2>user privileges</h2>
    <div class="privileges">
        <input class="priv-name" placeholder="position title or name">
        <button class="add">add</button>
        <table>
            <thead>
            <tr>
                <th>id</th>
                <th>name</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    <script src="{{asset('js/sub/privileges.js')}}"></script>
</section>

<section id="user-accounts">
    <h2>user accounts</h2>
    <div class="users">
        <input class="email full" type="email" placeholder="email">
        <input class="firstname full" placeholder="firstname">
        <input class="middlename full" placeholder="middlename">
        <input class="lastname full" placeholder="lastname">
        <input type="hidden" class="password full" placeholder="lastname" value="password">
        Position: <select class="positions"></select><br>
        Privilege: <select class="privileges"></select><br>
        Office: <input id="useraccount-officeId" size=7
                    name="officeId"
                    class="officeId autocomplete"
                    placeholder="office name/ID"
                    data-format="{campus} {name}"
                    data-url="/api/offices/search">

        <ul class="errors">
        </ul>
        <button class="add half">add user</button>
        <hr>

        <table>
            <thead>
            <tr>
                <th>id</th>
                <th>fullname</th>
                <th>position</th>
                <th>privilege</th>
                <th>office</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    <script src="{{asset('js/sub/users.js')}}"></script>
</section>

<section id="doc-fr" class='hidden'>
    <form>
    <input class="userID" placeholder="user id">
    <span class="fullname">aaaa bbbb</span>  
    (<small class="officename">office name</small>)
    <hr>
    <input name="trackingId" readonly class="full" value="UR-1112233">
    <textarea name="details" rows="7" class="full"
        placeholder="document details" readonly>Rush, deadline was years ago
    </textarea>
    </form>
    <p class="hidden">Attachment: <input name="attachment" type="file"></p>
    <p>Document Routes</p>
    <table>
        <thead>
        <tr>
            <th>id</th>
            <th>campus</th>
            <th>office</th>
            <th>status</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>1</td>
            <td>Urdaneta</td>
            <td>Registrar</td>
            <td>*</td>
        </tr>
        <tr>
            <td>2</td>
            <td>Urdaneta</td>
            <td>MIS</td>
            <td>*</td>
        </tr>
        </tbody>
    </table>
    <textarea name="annotation" rows="5" class="full"
        placeholder="comments, notes or annotation" ></textarea>
    <button>SEND/RECEIVE/IN-Transit</button>
</section>
@endsection
