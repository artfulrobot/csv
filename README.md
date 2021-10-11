# PHP CSV handling tools

## Class `ArtfulRobot\CSV`

This contains various static methods for:

- creating a CSV-safe values: single values, rows, or arrays of rows.
- outputting CSV in a string as a file download HTTP response.
- converting a PDO result to CSV data, and to a direct download.

## Class `ArtfulRobot\CSVParser`

Loads CSV data from a file (uses `fgetcsv()`) or string (uses `str_getcsv()`)
and provides an iterator giving access to the rows referenced by column name,
column/row indexes.

See the Synopsis section in the docblock of `ArtfulRobot/CSVParser.php`

If your CSV has some header rows that need to be ignored, pass a different
header row parameter to the parse method (default is 1).


## Change log

- v.12.1 New feature: Added TSV file parsing capabilities. Also see tsv2csv
  command line utility.

- v1.2 Added ability to parse without any header row, and to define this later
  either by passing in an array of headers, or by extracting headers from the
  parsed data. Also fixed bug in getRowNumber() which was returning 1 too high.
  Tests updated/added. More type hints added.

- v1.1 Added two new features: (1) Ability to specify the header row from
  the source data (rows before this are skipped), and (2) Ability to parse
  from a string source. Tests have been written to cover the new features, and
  have been updated for compatibility with  PHPUnit 9.

- v1.0 This began life bundled with the artfulrobot/artfulrobot PHP
  libraries, but was separated out as a lot of the other code in that
  library is deprecated. So it's been in production use for years. If you
  previously depended on artfulrobot/artfulrobot for the CSV libraries you
  should update to artfulrobot/csv which is where future maintenance will
  happen. In this case you should note that the class names have been
  standardised, so it's now `CSVParser` not `CsvParser`.
