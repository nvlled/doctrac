<div class='office-selection'>
    <table class="route">
        <thead>
        <tr>
            <th class='hidden'>id</th>
            <th>name</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>

    <div class="add-dest">
        <input size=30
            name="officeId"
            class="officeId autocomplete"
            placeholder="search for office name"
            data-format="{campus_name} {name}"
            data-url="/api/offices/{officeId}/next-offices">
        <button class='add hidden pure-button pure-button-primary'>add</button>
        <span class='error add-error'><span>
    </div>
    <script src='{{asset("js/sub/office-selection.js")}}'></script>
    <script>
    UI.OfficeSelection
    </script>
</div>
