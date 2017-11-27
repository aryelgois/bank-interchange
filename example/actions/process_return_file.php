<?php

require_once __DIR__ . '/../autoload.php';

use aryelgois\BankInterchange;

$return_file = BankInterchange\Controllers\ReturnFile::process($_POST['return_file']);

$messages = $return_file->getMessages();

$tab = '                ';

$template = $tab . "    <tr>\n"
          . str_repeat($tab . "        <td>%s</td>\n", 4)
          . $tab . "    </tr>\n";

/*
 * Result info
 */
$info = '';
foreach ($messages['info'] as $m) {
    $info .= sprintf(
        $template,
        $m['our_number'],
        $m['movement'] ?? '',
        $m['occurrence'],
        $m['occurrence_date'] ?? ''
    );
}

/*
 * Errors
 */
$count = count($messages['error']);
$error_title = $count . ' errors' . ($count > 0 ? ':' : '');
$error = ($count)
       ? $tab . "<ul>\n"
       . $tab . '    <li>'
       . implode("</li>\n" . $tab . '    <li>', $messages['error'])
       . "</li>\n"
       . $tab . "</ul>"
       : '';

/*
 * Warnings
 */
$count = count($messages['warning']);
$warning_title = $count . ' warnings' . ($count > 0 ? ':' : '');
$warning = ($count)
         ? $tab . "<ul>\n"
         . $tab . '    <li>'
         . implode("</li>\n" . $tab . '    <li>', $messages['warning'])
         . "</li>\n"
         . $tab . "</ul>"
         : '';

/*
 * Applying
 */
if (isset($_POST['apply'])) {
    $result = $return_file->apply();
    if ($result === false) {
        $apply = 'Nothing to apply';
    } elseif ($result === true) {
        $apply = 'Applied successfully';
    } else {
        $apply = 'Titles which failed: ' . implode(', ', $result);
    }
}

?>
<!doctype html>
<html>
    <head>
        <meta charset="UTF-8" />
        <link rel="stylesheet" href="../main.css" />
        <style>

main {
  padding: 0;
}

table.table-list td + td {
    border-left: 1px solid #ddd;
}

        </style>
    </head>
    <body>
        <main>
            <section>
                <h2>Result:</h2>
                <table class="table-list">
                    <tr>
                        <th>Our Number</th>
                        <th>Movement</th>
                        <th>Ocurrence</th>
                        <th>Ocurrence date</th>
                    </tr>
<?php echo $info; ?>
                </table>

                <h3><?php echo $error_title ?></h3>
<?php echo $error; ?>

                <h3><?php echo $warning_title ?></h3>
<?php echo $warning; ?>

<?php if (isset($_POST['apply'])): ?>

                <h2>Applying...</h2>
                <p>
                    <?php echo $apply; ?>

                </p>
<?php endif; ?>
            </section>
        </main>
    </body>
</html>
