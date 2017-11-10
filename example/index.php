<?php

require_once 'autoload.php';

use aryelgois\BankInterchange;
use aryelgois\Medools;

function protected_example(callable $callback, ...$params) {
    try {
        $callback(...$params);
    } catch (RuntimeException $e) {
        if ($e->getMessage() !== 'Unknown database') {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
        /*
         * Silently skip error:
         * User might not have configured config/medools.php yet
         */
    }
}

function select_option_foreign_person(Medools\ModelIterator $iterator) {
    foreach ($iterator as $model) {
        $person = $model->getForeign('person');
        printf(
            "                        <option value=\"%s\">%s (%s)</option>\n",
            $model->get('id'),
            $person->get('name'),
            $person->documentFormat()
        );
    }
}

function list_titles()
{
    $template = "            <tr>
                <td><input name=\"titles[]\" value=\"%s\" type=\"checkbox\" /></td>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
                <td><a href=\"generate_billet.php?id=%s\">pdf</a></td>
            </tr>\n";

    $iterator = new Medools\ModelIterator(new BankInterchange\Models\Title, []);
    foreach ($iterator as $model) {
        $id = $model->get('id');
        $payer_person = $model->getForeign('payer')->getForeign('person');
        $assignor_person = $model->getForeign('assignor')->getForeign('person');
        $value = $model->getForeign('specie')->getFormated($model->get('value'));

        $data = [
            $id,
            $id,
            format_person_pretty($payer_person),
            format_person_pretty($assignor_person),
            $value,
            $model->get('stamp'),
            $id,
        ];

        printf($template, ...$data);
    }
}

function format_person_pretty(Medools\Models\Person $person) {
    $result = $person->get('name')
            . '<br/><small>'
            . $person->documentFormat(true)
            . '</small>';

    return $result;
}

?>
<!doctype html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>Example - BankInterchange</title>
    <link rel="stylesheet" type="text/css" href="style.css" />
    <script>
function select_all(source, name) {
    checkboxes = document.getElementsByName(name);
    for (let i = 0, n = checkboxes.length; i < n; i++) {
        checkboxes[i].checked = source.checked;
    }
}
    </script>
</head>
<body>
    <h2>Intro</h2>
    <p>
        This example represents a fictional e-Comerce
    </p>

    <h2>Setup</h2>
    <p>
        First of all, you need to create <a href="../data/database.sql">this database</a>
        in your server, then populate it with <a href="../data/database_populate.sql">this</a>
        and <a href="database_populate_example.sql">this</a>.<br />
        Then, configure the database options in <code>../config/medools.php</code>
    </p>

    <h2>Generate Title</h2>
    <p>
        It represents the client interaction in the Website.
    </p>
    <p>
        The client would log in, choose some products (the value below is the
        sum) and the server would known from who.
    </p>
    <form action="generate_title.php" method="POST">
        <table>
            <tr>
                <td>The client</td>
                <td>
                    <select name="payer" required>
<?php

protected_example(
    'select_option_foreign_person',
    new Medools\ModelIterator(new BankInterchange\Models\Payer, [])
);

?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>is buying from</td>
                <td>
                    <select name="assignor" required>
<?php

protected_example(
    'select_option_foreign_person',
    new Medools\ModelIterator(new BankInterchange\Models\Assignor, [])
);

?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>something of <span>R$</span></td>
                <td><input name="value" type="number" min="0.5" step="0.01" value="5" required /></td>
            </tr>
            <tr>
                <td></td>
                <td><button name="action" value="generate_title">Ok</button></td>
            </tr>
        </table>
    </form>

    <h2>Titles generated</h2>
    <p>
        List of all titles in the database. The ones not yet in a shipping file
        have a checkbox.
    </p>
    <form method="POST">
        <table class="table-list">
            <tr>
                <th><input type="checkbox" onchange="select_all(this, 'titles[]')" /></th>
                <th>id</th>
                <th>Client</th>
                <th>Assignor</th>
                <th>Value</th>
                <th>Date</th>
                <th>Billet</th>
            </tr>
<?php

protected_example('list_titles');

?>
        </table>
        <button action="generate_cnab240.php">Generate CNAB240</button>
        <button action="generate_cnab400.php">Generate CNAB400</button>
        <p>
            Remember that, in production, you have to generate and send the
            Shipping File before outputing the Billet.
        </p>
    </form>

    <h2>Shipping Files generated</h2>
    <p>
        List of all Shipping Files in the database.
    </p>
    <table class="table-list">
        <tr>
            <th>id</th>
            <th>Filename</th>
            <th>Date</th>
            <th>File</th>
        </tr>
        <tr>
            <td>0</td>
            <td>Name</td>
            <td>Y-m-d H:i:s</td>
            <td><a href="storage/shipping_files/file">CNAB240/CNAB400</a></td>
        </tr>
    </table>

    <h2>TODO</h2>
    <ul>
        <li>Return Files</li>
    </ul>
</body>
</html>
