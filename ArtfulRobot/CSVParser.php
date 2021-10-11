<?php
/*
+--------------------------------------------------------------------+
| Copyright Rich Lott 2021. All rights reserved.                     |
|                                                                    |
| This work is published under the GNU GPLv3.0 license with some     |
| permitted exceptions and without any warranty. For full license    |
| and copyright information, see LICENSE                             |
+--------------------------------------------------------------------+
*/

namespace ArtfulRobot;

/**
 * Parse a CSV file.
 *
 * Synopsis:
 *
 *     $csv = CsvParser::createFromFile('foo.csv');
 *     print "Name: $csv->Name\n";
 *     print "Age: $csv->Age\n";
 *     print "Name: " . $csv->getCell($col=0) . "\n";
 *     foreach ($csv as $row) {
 *        print "Hello, $row->Name\n"; // Actually identical to $csv->Name
 *     }
 *     print "There are " . $csv->count() . " rows\n";
 *
 * Notes
 *
 * - You can access cells of the current row by header name, unless empty.
 * - All headers must be unique (or blank); an exception is thrown if duplicate headers are found.
 * - Within the data range, a zero-length string is returned if there is no data.
 * - $row in the above code is actually identical to the object itself; the foreach just moves the internal pointer.
 *
 */
class CSVParser implements \Iterator {

  /**
   * Holds the data.
   *
   * This is indexed from 1.
   */
  protected $data;

  /**
   * Holds the original headings
   */
  public $headers;

  /**
   * Maps header names to indexes
   */
  protected $header_map;

  /**
   * Holds current pointer.
   *
   * 1 is the first row.
   */
  protected $current_row = 1;


  /**
   * Return reference to this object; the internal pointer is now at the right row.
   */
  public function current() {
    return $this;
  }
  /**
   * Return current row number (from 1)
   */
  public function key() {
    return $this->current_row;
  }
  /**
   * Move to next row
   */
  public function next() {
    if (count($this->data) == $this->current_row) {
      $this->current_row = FALSE;
    }
    else {
      $this->current_row++;
    }
  }
  /**
   * Move back to row 1
   */
  public function rewind() {
    $this->current_row = 1;
  }
  /**
   * Check if valid
   */
  public function valid() {
    return isset($this->data[$this->current_row]);
  }


  /**
   * Returns count of rows
   */
  public function count() :int {
    return (int) count($this->data);
  }

  /**
   * Open and parse an entire CSV file
   */
  public function loadFromFile(string $filename, ?int $max_buffer_length = 0, ?int $headerRow = 1) {

    // Parse CSV file
    $csv_file = fopen($filename, "r");

    // Load data
    $this->data = [];
    $row = 1;
    while (($row_data = fgetcsv($csv_file, $max_buffer_length, ",")) !== FALSE) {
      $this->data[$row] = $row_data;
      $row++;
    }
    // tidy up
    fclose($csv_file);

    if ($headerRow > 0) {
      if ($headerRow > $this->count()) {
        throw new \InvalidArgumentException("Failed to read $headerRow row(s) of CSV from '$filename'");
      }
      $this->extractHeaders($headerRow);
    }

    $this->rewind();
    return $this;
  }

  /**
   * Open and parse an entire TSV file
   */
  public function loadFromTSVFile(string $filename, ?int $max_buffer_length = NULL, ?int $headerRow = 1) {

    // Parse CSV file
    $tsv_file = fopen($filename, "r");

    // Load data
    $this->data = [];
    $row = 1;

    $importRow = function($row_data) use (&$row) {
      $row_data = rtrim($row_data, "\r\n\0\x0B");
      $this->data[$row] = explode("\t", $row_data);
      $row++;
    };

    // For some reason fgets does not accept NULL; treats it as 0, so we need 2 loops.
    if ($max_buffer_length === NULL) {
      while (($row_data = fgets($tsv_file)) !== FALSE) {
        $importRow($row_data);
      }
    }
    else {
      while (($row_data = fgets($tsv_file, $max_buffer_length)) !== FALSE) {
        $importRow($row_data);
      }
    }
    // tidy up
    fclose($tsv_file);

    if ($headerRow > 0) {
      if ($headerRow > $this->count()) {
        throw new \InvalidArgumentException("Failed to read $headerRow row(s) of CSV from '$filename'");
      }
      $this->extractHeaders($headerRow);
    }

    $this->rewind();
    return $this;
  }

  /**
   * Parse an CSV string.
   *
   * NOTE: this might fail if Windows/Mac line endings are found.
   */
  public function loadFromString(string $data, ?int $headerRow = 1) {

    // Parse the string into rows first.
    // This is taken from https://www.php.net/manual/en/function.str-getcsv.php#101888
    $data = str_getcsv($data, "\n");

    // Load data
    $this->data = [];
    $row = 1;
    foreach ($data as $row_data) {
      $row_data = str_getcsv($row_data, ',');
      $this->data[$row] = $row_data;
      $row++;
    }

    if ($headerRow > 0) {
      if ($headerRow > $this->count()) {
        throw new \InvalidArgumentException("Failed to read $headerRow row(s) of CSV from '$filename'");
      }
      $this->extractHeaders($headerRow);
    }

    return $this;
  }

  /**
   * Take headers from the given row (or current row), discard all rows up to
   * that row from the parsed data.
   */
  public function extractHeaders(?int $row = NULL) :CSVParser {

    // this row contains the headers.
    if ($row !== NULL) {
      $this->setRow($row);
    }
    if (!$this->valid()) {
      throw new \InvalidArgumentException("Invalid current row; cannot extractHeaders.");
    }

    $this->setHeaders($this->data[$this->current_row]);
    // Drop the rows, leaving one NULL entry at start.
    array_splice($this->data, 0, $this->current_row, [NULL]);
    // Re-index.
    $this->data = array_values($this->data);
    // Remove the null entry, leaving the other rows starting at offset 1.
    unset($this->data[0]);

    $this->rewind();
    return $this;
  }
  /**
   * Provide named headers.
   *
   * Typically this is set via extractHeaders from the data in the CSV file,
   * but you may wish to set your own.
   *
   * @param array $headers
   * @return this object
   */
  public function setHeaders($headers) :CSVParser {
    $this->headers = $headers;
    $this->header_map = [];
    foreach ($this->headers as $i=>$_) {
      // Trim the header because leading/trailing spaces are pretty much always a mistake.
      $_ = trim($_);
      if ($_) {
        if (isset($this->header_map[$_])) {
          throw new \InvalidArgumentException("Duplicate header name: $_");
        }
        $this->header_map[$_] = $i;
      }
    }
    return $this;
  }
  /**
   * Convert back to CSV string.
   */
  public function toCSVString() :string {
    return CSV::arrayToCsv($this->data, $this->getHeaders());
  }
  /**
   * Factory method to create an object and load a file.
   */
  public static function createFromFile($filename, $max_buffer_length = null, ?int $headerRow = 1) :CSVParser {
    $csv_parser = new static();
    $csv_parser->loadFromFile($filename, $max_buffer_length, $headerRow);
    return $csv_parser;
  }

  /**
   * Factory method to create an object and load a file.
   */
  public static function createFromTSVFile($filename, $max_buffer_length = null, ?int $headerRow = 1) :CSVParser {
    $csv_parser = new static();
    $csv_parser->loadFromTSVFile($filename, $max_buffer_length, $headerRow);
    return $csv_parser;
  }

  /**
   * Factory method to create an object and load CSV from a string.
   */
  public static function createFromString(string $data, ?int $headerRow = 1) :CSVParser {
    $csv_parser = new static();
    $csv_parser->loadFromString($data, $headerRow);
    return $csv_parser;
  }


  /**
   * Magic method to fetch a value by a header
   */
  public function __get($property) {
    if (isset($this->header_map[$property])) {
      $i = $this->header_map[$property];
      return $this->getCell($i);
    }
    throw new \Exception("Unknown property '$property'");
  }
  /**
   * Access by numeric co-ordinates, column[, row]
   *
   * First column is column 0.
   * First row is row 1.
   */
  public function getCell($col_number, $row_number=null) {
    if ($row_number) {
      $this->setRow($row_number);
    }
    if (!$this->valid()) {
      throw new \InvalidArgumentException("Row not found.");
    }

    // If we have headers, use that as the expectation for the number of columns.
    $maxCols = $this->headers ? count($this->headers) : count($this->data[$this->current_row]);
    if ($col_number<0 || $col_number>$maxCols) {
      throw new \InvalidArgumentException("Column $col_number out of bounds.");
    }

    if (!isset($this->data[$this->current_row][$col_number])) {
      return '';
    }
    return $this->data[$this->current_row][$col_number];
  }

  /**
   * Set current row
   *
   * First row is row 1.
   */
  public function setRow(int $row_number) :CSVParser {
    $row_number = (int) $row_number;
    if ($row_number < 1 || $row_number > $this->count()) {
      $this->row_number = FALSE;
      throw new \InvalidArgumentException("Row not found.");
    }
    $this->current_row = $row_number;

    return $this;
  }
  /**
   * Returns an array of column headers.
   */
  public function getHeaders() :array {
    return array_keys($this->header_map);
  }
  /**
   * Returns an associative array for current row.
   *
   * Headers are keys.
   *
   * @return NULL|Array
   */
  public function getRowAsArray() :array {
    $_ = [];
    if ($this->valid()) {
      foreach ($this->header_map as $key => $index) {
        $_[$key] = $this->data[$this->current_row][$index];
      }
      return $_;
    }
  }
  /**
   * Returns current row number in spreadsheet terms.
   *
   * i.e. First row is 1 not 0.
   *
   * @return NULL|int
   */
  public function getRowNumber() {
    return isset($this->data[$this->current_row]) ? $this->current_row : NULL;
  }
}
