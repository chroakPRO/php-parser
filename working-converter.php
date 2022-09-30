<?php
require __DIR__ . '/../vendor/autoload.php';

$arr = [];
$csv = [];
$file_load = fopen("ls.csv", "w");
$title = array("date", "gmt", "cat", "genre", "Title", "desc", "Flags", "Rating");


if ( $xls = SimpleXLS::parseFile('sport.xls') ) {
    $index = 0;
    foreach($xls->rows() as $value){
        $min = $value[0];
        $max = $value[count($value)];

        if(empty($value) === false) {
            $jndex = 0;
            if ($index !== 0) {
                foreach ($value as $row) {
                    if (empty($row) === false) {

                        //print_r($row . "\n");
                        $arr[$jndex] = (string)$row;
                    }
                    $jndex++;
                }

                print_r($arr);
                fputcsv($file_load, $arr, ";");

            } else {fputcsv($file_load, $title, ";");}
        }
        $index++;
    }
    // echo $xls->toHTML();
} else {
    echo SimpleXLS::parseError();
}
