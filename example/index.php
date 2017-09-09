<!doctype html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>Example - BankInterchange</title>
    <link rel="stylesheet" type="text/css" href="../res/example.css" />
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
    <p class="todo">
        <a href="client_buy.php">Click here</a> to make the client buy a product.<br />
        It will produce an entry in the database and return the bank billet to the browser
    </p>
    <p>
        <a href="shipping_file.php">Click here</a> to make the server generate shipping files.<br />
        They are stored <a href="data/shipping_files">here</a>
    </p>
    <p class="todo">
        <a href="return_file_file.php">Click here</a> to make the bank send a return file.<br />
        They are stored <a href="data/return_files">here</a>
    </p>
</body>
</html>
