@extends("layout")

@section("contents")

<section id="single-dispatch">
    <h1>Dispatch Document</h1>
    <form>
    <input name="trackingID" class="full" placeholder="tracking ID">
    <textarea name="details" rows="7" class="full"
        placeholder="document details"></textarea>
    </form>
    <p>Attachment: <input name="attachment" type="file"></p>

    <hr>

    <h3>Office destinations</h3>
    <table>
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
        <select class="offices"></select><button class='add'>add</button>
    </div>
    <script src="{{asset('js/sub/single-dispatch.js')}}"></script>

    <br>
    <div class="center">
        <label><input type="checkbox"> Multiple Recipients</label>
        <br>
        <button class="half">Send</button>
    </div>
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
                <th>office</th>
                <th>campus</th>
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
        <input class="firstname full" placeholder="firstname">
        <input class="middlename full" placeholder="middlename">
        <input class="lastname full" placeholder="lastname">
        <input type="hidden" class="password full" placeholder="lastname" value="password">
        Position: <select class="positions"></select><br>
        Privilege: <select class="privileges"></select><br>
        Office: <select class="offices"></select><br>
        <br>
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

<section id="new-dispatch">
    <form>
    <input name="trackingID" readonly class="full" value="UR-1112233">
    <textarea name="details" rows="7" class="full"
        placeholder="document details" readonly>Rush, deadline was years ago
    </textarea>
    </form>
    <p>Attachment: <input name="attachment" type="file"></p>
    <p>Document Pathway</p>
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
