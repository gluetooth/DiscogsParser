#!/usr/bin/php

<?php

$arguments = getopt("f:t:");

if (false === $arguments || 0 === count($arguments)) {
    echo "\nUsage:" .
        "\nphp " . $argv[0] . " -f filename";
}

$filename = $arguments['f'];

printRecords(readDiscogsCsv($filename));


/**
 * Read $filename as Discogs CSV dump, return records by format
 *
 * @param string $filename
 * @return array
 */
function readDiscogsCsv($filename)
{
    $handle = fopen($filename, 'r');

    if (false === $handle) {
        die("Unable to open file " . $filename);
    }

    $columns = array_map('strtolower', fgetcsv($handle));

    $records = array();

    while (false !== ($record = fgetcsv($handle))) {

        $records[
            mapFormat(
                $record[
                    array_search('format', $columns)
                ]
            )
        ][] = array_combine($columns, $record);
    }

    fclose($handle);

    return $records;
}


/**
 * Echo records in desired format to stdout
 *
 * @param array $records
 * @return null
 */
function printRecords($records)
{
    foreach ($records as $format => $recordsByFormat) {
        echo "\n" . $format . "\n" . '***' . "\n";

        usort($recordsByFormat, function ($a, $b) {
            return strcasecmp($a['artist'], $b['artist']);
        });

        foreach ($recordsByFormat as $record) {
            echo
                 (array_key_exists('price', $record) ? (int)$record['price'] . ' e ': '' ) .
                 getArtist($record['artist']) .
                 ' - ' .
                 $record['title'] .
                 getFormatModifiers($record['format']) .
                 "\n";
        }
    }

    return null;
}


/**
 * Pretty-print artist name. Strip disambiguator suffix if present.
 *
 * @param string $artist
 * @return string
 */
function getArtist($artist)
{
    return preg_replace(
        '#\ \([0-9]+\)#',
        '',
        $artist);
}


/**
 * Pretty-print record format. Map input format to available values or return it unmodified.
 *
 * @param string $inputFormat
 * @return string
 */
function mapFormat($inputFormat)
{
    foreach (['7"', '10"', '12"', 'LP', 'CD', 'Cass'] as $presetFormat) {
        if (false !== stripos($inputFormat, $presetFormat)) {
            return $presetFormat;
        }
    }
    return $inputFormat;
}

/**
 * Prefix format with modifiers.
 *
 * Note that 2x pic LP is not supported with this version.
 *
 * @param string $format
 * @return string
 */
function getFormatModifiers($format)
{
    foreach (['Pic', '2x'] as $modifier) {
        if (false !== stripos($format, $modifier)) {
            return ' ' .$modifier . mapFormat($format);
        }
    }
    return null;
}

?>
