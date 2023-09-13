<?php
use InfluxDB2\Point;
include './vendor/autoload.php';

include ('../modals/header.php');
?>

<div>
    <h1>Servizio Rest per l'accesso ai dati generati dai sensori</h1>
    <h4>Come usare il tutto:</h4>
    <p>Il servizio mette a disposizione dell'utente delle API implementate tramite un servizio web di tipo REST</p>
    <p>Le interfaccie disponibili, che seguono le operazioni <a href="https://it.wikipedia.org/wiki/CRUD">CRUD</a>, sono:</p>
    <ul>
        <li>/api/create.php</li>
        <li>/api/read.php</li>
        <li>/api/query.php</li>
        <li>/api/delete.php</li>
    </ul>

    <div>
        <h5>Create</h5>
        <p>Permette di inserire nel databse nuovi dati.</p>
        <p>Deve essere inoltrata una richiesta HTTP con metodo POST all'indirizzo /api/create.php</p>
        <p>La richiesta HTTP deve seguire il seguente formato:</p>
        <pre><code>
curl --request POST \
"INFLUX_HOST/api/v2/write?org=INFLUX_ORG&bucket=BUCKET&precision=PRECISION" \
--header "Authorization: Token INFLUX_TOKEN" \
--header "Content-Type: text/plain; charset=utf-8" \
--header "Accept: application/json" \
--data-binary "
DATI DI ESEMPIO SCRITTI IN LINE PROTOCOL
home,room=Living\ Room temp=21.1,hum=35.9,co=0i 1641024000
home,room=Kitchen temp=21.0,hum=35.9,co=0i 1641024000
home,room=Living\ Room temp=21.4,hum=35.9,co=0i 1641027600
"</code></pre>
    </div>
    <div>
        <h5>Read</h5>
    </div>
    <div>
        <h5>Query</h5>
    </div>
    <div>
        <h5>Delete</h5>
    </div>

</div>

<?php
include ('../modals/footer.php');
?>