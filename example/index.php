<?php

require_once 'autoload.php';

use aryelgois\BankInterchange;
use aryelgois\Medools;

function protected_example(callable $callback)
{
    try {
        $callback();
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Unknown database') === false) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
        /*
         * Silently skip error:
         * User might not have configured config/medools.php yet
         */
    }
}

function select_option_foreign_person(Medools\ModelIterator $iterator)
{
    foreach ($iterator as $model) {
        printf(
            "                        <option value=\"%s\">%s</option>\n",
            $model->get('id'),
            format_model_pretty($model, false)
        );
    }
}

function list_payers()
{
    select_option_foreign_person(
        new Medools\ModelIterator(new BankInterchange\Models\Payer, [])
    );
}

function list_assignors()
{
    select_option_foreign_person(
        new Medools\ModelIterator(new BankInterchange\Models\Assignor, [])
    );
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
        $payer = $model->getForeign('payer');
        $assignor = $model->getForeign('assignor');
        $value = $model->getForeign('specie')->format($model->get('value'));

        $data = [
            $id,
            $id,
            format_model_pretty($payer),
            format_model_pretty($assignor),
            $value,
            $model->get('stamp'),
            $id,
        ];

        printf($template, ...$data);
    }
}

function format_model_pretty($model, $html = true)
{
    $person = $model->getForeign('person');
    $info = ($model instanceof BankInterchange\Models\Assignor)
          ? 'Account: ' . $model->formatAgencyAccount(4, 11)
          : $person->documentFormat(true);

    $result = $person->get('name')
            . ($html ? '<br/><small>' : ' (')
            . $info
            . ($html ? '</small>' : ')');

    return $result;
}

function list_shipping_files()
{
    $template = "        <tr>
            <td>%s</td>
            <td>%s</td>
            <td>%s</td>
            <td>%s</td>
            <td>
                <a href=\"generate_cnab.php?cnab=240&id=%s\">CNAB240</a>
                <a href=\"generate_cnab.php?cnab=400&id=%s\">CNAB400</a>
            </td>
        </tr>\n";

    $shipping_files = new Medools\ModelIterator(new BankInterchange\Models\ShippingFile, []);
    foreach ($shipping_files as $shipping_file) {
        $id = $shipping_file->get('id');
        $titles = [];
        $total = 0.0;

        $shipping_file_titles = new Medools\ModelIterator(
            new BankInterchange\Models\ShippingFileTitle,
            ['shipping_file' => $id]
        );
        foreach ($shipping_file_titles as $sft) {
            $title = $sft->getForeign('title');
            $titles[] = $title->get('id');
            $total += (float) $title->get('value');
        }

        $data = [
            $id,
            implode(', ', $titles),
            $title->getForeign('specie')->format($total),
            $shipping_file->get('stamp'),
            $id,
            $id,
        ];

        printf($template, ...$data);
    }
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
    <ol>
        <li>
            Create <a href="../data/database.sql">this database</a> in your
            server, then populate it with <a href="../data/database_populate.sql">this</a>
            and <a href="database_populate_example.sql">this</a>.
        </li>
        <li>
            Configure the database options in <code>../config/medools.php</code>
        </li>
    </ol>

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

protected_example('list_payers');

?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>is buying from</td>
                <td>
                    <select name="assignor" required>
<?php

protected_example('list_assignors');

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

    <h2>Generate Shipping File</h2>
    <p>
        Below is a list of all titles in the database. Those not yet in a
        shipping file have a checkbox.
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
        <button formaction="generate_shipping_file.php">Ok</button>
        <p>
            Remember that, in production, you have to generate and send the
            Shipping File before outputing the Billet.
        </p>
    </form>

    <h2>Generate CNAB</h2>
    <p>
        Below is a list of all Shipping Files in the database. Choose how you
        want to render them.
    </p>
    <table class="table-list">
        <tr>
            <th>id</th>
            <th>Titles</th>
            <th>Total</th>
            <th>Date</th>
            <th>CNAB</th>
        </tr>
<?php

protected_example('list_shipping_files');

?>
    </table>

    <h2>TODO</h2>
    <ul>
        <li>Return Files</li>
    </ul>
</body>
</html>
