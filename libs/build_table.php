<?php

function build_table($results, $header = true)
{
    var_dump($results);

    $table = '';

    if ($header) {
        $table .= '|framework          |requests per second|relative|peak memory|relative|          type|' . "\n";
        $table .= '|-------------------|------------------:|-------:|----------:|-------:|-------------:|' . "\n";
    }
    
    foreach ($results as $fw => $result) {
        $table .= sprintf(
            "|%-19s|%19s|%8s|%11s|%8s|%14s|\n",
            $fw,
            number_format($result['rps'], 2),
            number_format($result['rps_relative'], 1),
            number_format($result['memory'], 2),
            number_format($result['memory_relative'], 1),
            $result['type']
        );
    }

    return $table;
}
