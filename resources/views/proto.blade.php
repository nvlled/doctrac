@extends("layout")

@section("contents")

<section id="context">
    <h2>locations</h2>
    <form>
    <input name="userId" class="" placeholder="campus">
    <input name="locId" class="" placeholder="office">
    <button>add</button>
    </form>

    <table>
        <thead>
        <tr>
            <th>id</th>
            <th>campus</th>
            <th>office</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>1</td>
            <td>Urdaneta</td>
            <td>Registrar</td>
        </tr>
        <tr>
            <td>2</td>
            <td>Urdaneta</td>
            <td>MIS</td>
        </tr>
        </tbody>
    </table>
</section>

<section id="context">
    <h2>context</h2>
    <form>
    <input name="userId" class="full" placeholder="current userID">
    <input name="locId" class="full" placeholder="current locId">
    <p>
        username: <span id="username">
    </p>
    <p>
        location: <span id="location">
    </p>
    </form>
</section>

<section id="new-dispatch">
    <form>
    <input name="trackingID" class="full" placeholder="tracking ID">
    <textarea name="details" rows="7" class="full"
        placeholder="document details"></textarea>
    </form>
    <p>Attachment: <input name="attachment" type="file"></p>
    <p>Document Pathway</p>
    <table>
        <thead>
        <tr>
            <th>id</th>
            <th>campus</th>
            <th>office</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>1</td>
            <td>Urdaneta</td>
            <td>Registrar</td>
            <td><button>x</button></td>
        </tr>
        <tr>
            <td>2</td>
            <td>Urdaneta</td>
            <td>MIS</td>
            <td><button>x</button></td>
        </tr>
        </tbody>
    </table>
    <input name="destId" class="" placeholder="location ID">
    <button>add</button>
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
    <button>SEND</button>
</section>

@endsection




