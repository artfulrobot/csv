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


