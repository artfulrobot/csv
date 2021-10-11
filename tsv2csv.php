#!/usr/bin/env php
<?php
require_once __DIR__ . '/ArtfulRobot/CSVParser.php';
require_once __DIR__ . '/ArtfulRobot/CSV.php';

function helpAndExit() {
  global $argv;
  fwrite(STDERR, <<<TXT
  Converts tab-separated-file (.tsv) data to comma-separated (CSV).
  Usage $argv[0] [input.tsv [output.csv]]

  If output is missing, writes to STDOUT.
  If input and output are missing, reads STDIN, writes to STDOUT.
  You can specify '-' as a file to use STDIN/STDOUT, too.

  TXT
  );

  exit();
}

if ($argc === 1) {
  // Read STDIN, write STDOUT
  $in = "php://stdin";
  $out = "php://stdout";
}
elseif ($argc === 2) {
  // Read given file
  $in = $argv[1];
  if ($in === '-') {
    $in = 'php://stdin';
  }
  $out = "php://stdout";
}
elseif ($argc === 3) {
  // Read given file
  $in = $argv[1];
  if ($in === '-') {
    $in = 'php://stdin';
  }
  $out = $argv[2];
  if ($out === '-') {
    $out = 'php://stout';
  }
}
else {
  helpAndExit();
}
if (preg_match('/^(--?)?(h(elp)?)$/', $in)) {
  helpAndExit();
}
if ($in !== 'php://stdin' && (!file_exists($in) || !is_readable($in))) {
  helpAndExit("'$in' does not exist, or can't be read");
}
$csv = \ArtfulRobot\CSVParser::createFromTSVFile($in)->toCSVString();
$b = file_put_contents($out, $csv);
fwrite(STDERR, round($b/1024, 1) . "kB written to $b\n");

