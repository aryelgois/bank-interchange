<!doctype html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>CNAB240 example</title>
</head>
<body>
    <h2>Intro</h2>
    <p>This example represents a fictional e-Comerce</p>
    <p>For simplicity, the database is stored in JSON files <a href="../lib/example/data">here</a></p>
    
    <h2>Navigation</h2>
    <p>Use the links bellow to view the website from one's point of view.</p>
    <dl>
        <dt><a href="admin.php">Admin</a></dt>
        <dd>- Controls client and product registries; veriies bank billets' state</dd>
        <dt><a href="client.php">Client</a></dt>
        <dd>- Can buy products; verifies if product was sent</dd>
        <dt><a href="bank.php">Bank</a></dt>
        <dd>- Sends Return Files in response to Shipping Files from the website</dd>
    </dl>
    
    <h2>Workflow</h2>
    <ol>
        <li>The administrator registres products and clients</li>
        <li>The client buyies one or more products</li>
        <li>(auto) The website send a Shipping File to the Bank</li>
        <li>The Bank gives a response</li>
        <li>The administrator checks for Return Files and send the products</li>
        <li>The client checks if the product was sent</li>
        <li>One day.. the product arrive :)</li>
    </ol>
</body>
</html>