
@extends("layout")

@section("contents")

<section id="offices">
    <h2>offices</h2>
    <div class="add-office">
        <ul class='errors'></ul>
        <ul class='msgs'></ul>
        <input class="campus-name autocomplete"
               data-url='/api/campuses/search'
               data-key='id'
               data-format='{name}'
               placeholder="campus">
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

<section id="users">
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
@endsection
