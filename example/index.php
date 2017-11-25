<?php

require_once 'autoload.php';

use aryelgois\BankInterchange;
use aryelgois\Medools;

/*
 * helper functions
 */

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

function format_model_pretty($model, $html = true)
{
    $person = $model->person;
    $info = ($model instanceof BankInterchange\Models\Assignor)
          ? 'Account: ' . $model->formatAgencyAccount(4, 11)
          : $person->documentFormat(true);

    $result = $person->name
            . ($html ? '<br/><small>' : ' (')
            . $info
            . ($html ? '</small>' : ')');

    return $result;
}

function select_option_foreign_person(Medools\ModelIterator $iterator)
{
    foreach ($iterator as $model) {
        printf(
            "                        <option value=\"%s\">%s</option>\n",
            $model->id,
            format_model_pretty($model, false)
        );
    }
}

/*
 * example functions
 */

function list_payers()
{
    select_option_foreign_person(
        new Medools\ModelIterator('aryelgois\BankInterchange\Models\Payer', [])
    );
}

function list_assignors()
{
    select_option_foreign_person(
        new Medools\ModelIterator('aryelgois\BankInterchange\Models\Assignor', [])
    );
}

function list_titles()
{
    $template = "                <tr>
                    <td><input name=\"titles[]\" value=\"%s\" type=\"checkbox\" /></td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td><a href=\"generate_billet.php?id=%s\">pdf</a></td>
                </tr>\n";

    $iterator = new Medools\ModelIterator('aryelgois\BankInterchange\Models\Title', []);
    foreach ($iterator as $model) {
        $id = $model->id;
        $payer = $model->payer;
        $assignor = $model->assignor;
        $value = $model->specie->format($model->value);

        $data = [
            $id,
            $id,
            format_model_pretty($payer),
            format_model_pretty($assignor),
            $value,
            $model->stamp,
            $id,
        ];

        printf($template, ...$data);
    }
}

function list_shipping_files()
{
    $template = "            <tr>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
                <td>
                    <a href=\"generate_cnab.php?cnab=240&id=%s\">CNAB240</a>
                    <a href=\"generate_cnab.php?cnab=400&id=%s\">CNAB400</a>
                </td>
            </tr>\n";

    $shipping_files = new Medools\ModelIterator('aryelgois\BankInterchange\Models\ShippingFile', []);
    foreach ($shipping_files as $shipping_file) {
        $id = $shipping_file->id;
        $titles = [];
        $total = 0.0;

        $shipping_file_titles = new Medools\ModelIterator(
            'aryelgois\BankInterchange\Models\ShippingFileTitle',
            ['shipping_file' => $id]
        );
        foreach ($shipping_file_titles as $sft) {
            $title = $sft->title;
            $titles[] = $title->id;
            $total += (float) $title->value;
        }

        $data = [
            $id,
            implode(', ', $titles),
            $title->specie->format($total),
            $shipping_file->stamp,
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
    <title>Example - BankInterchange</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta charset="UTF-8" />
    <!--[if lt IE 9]><script src="https://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv.js"></script><![endif]-->
    <script>
function select_all(source, name) {
    checkboxes = document.getElementsByName(name);
    for (let i = 0, n = checkboxes.length; i < n; i++) {
        checkboxes[i].checked = source.checked;
    }
}
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/7.0.0/normalize.min.css" />
    <link rel="stylesheet" href="main.css" />
</head>
<body>
    <aside>
        <header>
            <h2>BankInterchange</h2>
            <em>example</em>
        </header>

        <nav>
            [links]
        </nav>
    </aside>

    <main>
    <section id="intro">
        <h2>Intro</h2>
        <p>
            This example represents a fictional e-Comerce
        </p>
    </section>

    <section id="setup">
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
    </section>

    <section id="generate_title">
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
    </section>

    <section id="generate_shipping_file">
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
    </section>

    <section id="generate_cnab">
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
    </section>

    <section id="process_return_file">
        <h2>Return File</h2>
        <p>
            Enter a Return File sent by a Bank to process it
        </p>
        <form action="process_return_file.php" method="POST">
            <textarea name="return_file" required></textarea>
            <p>
                <label><input name="apply" type="checkbox" />Apply in the Database</label>
            </p>
            <button>Send</button>
        </form>
    </section>
    </main>
</body>
</html>
