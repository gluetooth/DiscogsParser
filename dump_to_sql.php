#!/usr/bin/php

<?php

$outfile = 'dump.sql';
$table = 'discogs';

$handle = fopen('orders.csv','r');

if (!$handle) {
    die('Unable to open input file');
}

$columns = fgetcsv($handle);

$prepareTable = "DROP TABLE IF EXISTS `{$table}`; CREATE TABLE `{$table}`\n";
$prepareInsert = "INSERT INTO `{$table}` VALUES\n";

file_put_contents(
    $outfile,
    implode([
        $prepareTable,
        mysql_cols($columns, '`'),
        ';',
        "\n\n"
    ])
);

while (false !== ($row = fgetcsv($handle))) {
    file_put_contents(
        $outfile,
        implode([
            $prepareInsert,
            mysql_cols($row, '"', true), // TODO peek if next line is EOF
            ';',
            "\n\n",
        ]),
        FILE_APPEND
    );
}
fclose($handle);

function mysql_cols(Array $data, $wrapper='"', $escape=false)
{
    return implode([
        '(',
        implode(
            ", ",
            array_map(
                function ($val) use ($wrapper, $escape) {
                    return implode([
                        $wrapper,
                        ($escape ? escapeshellcmd($val) : $val),
                        $wrapper,
                    ]);
                },
                $data
            )
        ),
        ')',
    ]);
}

?>
