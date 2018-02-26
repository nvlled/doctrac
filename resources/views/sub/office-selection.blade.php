<div class='office-selection'>
    <table class="route">
        <thead>
        <tr>
            <th class='hidden'>id</th>
            <th>campus</th>
            <th>office</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    <br>

    <div class="add-dest">
        <select name="campuses"
                class="campuses "
                data-format="{name}"
                data-url="/api/campuses/list"></select>
        <select name="offices"
                class="offices "
                data-format="{name}"
                data-url="/api/campuses/{campusId}/offices"></select>
        <button class='add pure-button pure-button-default'>add</button>
        <br>
        <input size=30
            name="officeId"
            class="hidden officeId autocomplete"
            placeholder="search for office name"
            data-format="{campus_name} {name}"
            data-url="/api/offices/{officeId}/next-offices">
        <span class='error add-error'><span>
    </div>
    <script src='{{asset("js/sub/office-selection.js")}}'></script>
    <script>
    UI.OfficeSelection
    </script>
</div>
