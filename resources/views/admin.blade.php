
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
        <button class="reset">*reset all*</button>
        <table>
            <thead>
            <tr>
                <th>campus</th>
                <th>office</th>
                <th>username</th>
                <th>action</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    <script src="{{asset('js/sub/add-office.js')}}"></script>
</section>
@endsection
