<!doctype html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>Example - BankInterchange</title>
    <link rel="stylesheet" type="text/css" href="../res/css/example.css" />
</head>
<body>
    <h2>Intro</h2>
    <p>
        This example represents a fictional e-Comerce
    </p>
    <p>
        First of all, you need to create <a href="../data/database.sql">this database</a>
        in your server, then populate it with <a href="database_populate.sql">this</a>.<br />
        Lastly, configure the database access in <a href="database_config.json">database_config.json</a>
    </p>
    <p>
        What you will see here is the result of interaction, not the website itself.
    </p>
    
    <h2>Scripts</h2>
    <h3>Bank Billet</h3>
    <p>
        <a href="bank_billet.php">Click here</a> to make the client buy a product.<br />
        It will produce an entry in the database and return the bank billet to the browser.<br />
        Remember that, in production, you have to generate and send the Shipping File before outputing the billet.
    </p>
    <br />
    <h3>CNAB240</h3>
    <p>
        <a href="cnab240_shipping_file.php">Click here</a> to make the server generate CNAB240 Shipping Files.<br />
        They are stored <a href="data/cnab240/shipping_files">here</a>
    </p>
    <p class="todo">
        <a href="cnab240_return_file_file.php">Click here</a> to make the bank send a CNAB240 Return File.<br />
        They are stored <a href="data/cnab240/return_files">here</a>
    </p>
    <br />
    <h3>CNAB400</h3>
    <p class="todo">
        <a href="cnab400_shipping_file.php">Click here</a> to make the server generate CNAB400 Shipping Files.<br />
        They are stored <a href="data/cnab400/shipping_files">here</a>
    </p>
    <p class="todo">
        <a href="cnab400_return_file_file.php">Click here</a> to make the bank send a CNAB400 Return File.<br />
        They are stored <a href="data/cnab400/return_files">here</a>
    </p>
</body>
</html>
