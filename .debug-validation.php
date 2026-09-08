<?php

require __DIR__.'/demo-app/vendor/autoload.php';

use FlussiH\Validator\FlussiHValidator;

$h1Path = '/Users/carmenromano/Desktop/LUGLIO_2026_FILE_H_H1.h1';
$h2Path = '/Users/carmenromano/Desktop/LUGLIO_2026_FILE_H_H2.h2';
$validator = new FlussiHValidator();
$result = $validator->validate($h1Path, $h2Path);
$summary = [];
foreach ($result->issues() as $issue) {
    $key = $issue->severity.'|'.$issue->code.'|'.$issue->field.'|'.$issue->message;
    $summary[$key] = isset($summary[$key]) ? $summary[$key] + 1 : 1;
}
arsort($summary);
echo 'valid=' . ($result->isValid() ? 'yes' : 'no') . ' errors='.$result->errorCount().' warnings='.$result->warningCount().PHP_EOL;
foreach (array_slice($summary, 0, 30, true) as $key => $count) {
    echo $count."\t".$key.PHP_EOL;
}

$handle = fopen($h1Path, 'rb');
$line = rtrim(fgets($handle), "\r\n");
fclose($handle);
echo 'H1 first record bytes='.strlen($line).PHP_EOL;
$nonAscii = [];
for ($i = 0; $i < strlen($line); $i++) {
    if (ord($line[$i]) > 127) {
        $nonAscii[] = ($i + 1).':'.strtoupper(bin2hex($line[$i]));
    }
}
echo 'H1 non-ASCII bytes: '.implode(',', $nonAscii).PHP_EOL;
preg_match_all('/[A-Z]{6}[0-9A-Z]{2}[A-Z][0-9A-Z]{2}[A-Z][0-9A-Z]{3}[A-Z]/', $line, $matches, PREG_OFFSET_CAPTURE);
echo 'CF-like offsets (1-based): '.implode(',', array_map(function ($match) { return $match[1] + 1; }, $matches[0])).PHP_EOL;
foreach ([51, 52, 53, 54, 92, 93, 94, 132, 133, 134, 148, 149, 150, 158, 159, 160, 165, 166, 168, 169, 171, 172, 173, 243, 244] as $position) {
    $character = substr($line, $position - 1, 1);
    echo $position.'='.($character === ' ' ? 'SP' : (ctype_digit($character) ? 'N' : 'A')).' ';
}
echo PHP_EOL;
foreach ([[1, 28], [149, 172], [235, 266]] as $range) {
    list($from, $to) = $range;
    echo "H1 {$from}-{$to}: ";
    for ($position = $from; $position <= $to; $position++) {
        $character = substr($line, $position - 1, 1);
        echo ($character === ' ' ? '_' : $character);
    }
    echo PHP_EOL;
}
for ($position = 240; $position <= strlen($line); $position++) {
    $character = substr($line, $position - 1, 1);
    echo $position.'='.($character === ' ' ? '[SP]' : $character).' ';
}
echo PHP_EOL;

$handle = fopen($h2Path, 'rb');
$line = rtrim(fgets($handle), "\r\n");
fclose($handle);
echo 'H2 first record bytes='.strlen($line).PHP_EOL;
for ($position = 95; $position <= strlen($line); $position++) {
    if (in_array($position, [95, 101, 103, 111, 119, 122, 123, 130, 131, 137, 138, 139, 148, 149, 158, 169, 173, 181, 189, 195], true)) {
        echo PHP_EOL;
    }
    $character = substr($line, $position - 1, 1);
    echo ($character === ' ' ? '[SP]' : $character);
}
echo PHP_EOL;
