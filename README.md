# PHP CSV handling tools

## Class `ArtfulRobot\CSV`

This contains various static methods for:

- creating a CSV-safe values: single values, rows, or arrays of rows.
- outputting CSV in a string as a file download HTTP response.
- converting a PDO result to CSV data, and to a direct download.

## Class `ArtfulRobot\CSVParser`

Loads CSV data from a file (uses `fgetcsv()`) and provides an iterator
giving access to the rows referenced by column name, column/row indexes.

See the Synopsis section in the docblock of `ArtfulRobot/CSVParser.php`


## Change log

- v1.0 This began life bundled with the artfulrobot/artfulrobot PHP
  libraries, but was separated out as a lot of the other code in that
  library is deprecated. So it's been in production use for years. If you
  previously depended on artfulrobot/artfulrobot for the CSV libraries you
  should update to artfulrobot/csv which is where future maintenance will
  happen. In this case you should note that the class names have been
  standardised, so it's now `CSVParser` not `CsvParser`.
