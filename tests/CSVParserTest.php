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
use \ArtfulRobot\CSVParser;

class CSVParserTest extends \PHPUnit\Framework\TestCase {

    public function testBasicParse()
    {
      $csv = CSVParser::createFromFile(dirname(__FILE__) . '/fixtures/testcase-1.csv');
      $this->basicParseAssertions($csv);
    }

    public function testBasicParseFromString()
    {
      $csv = file_get_contents(dirname(__FILE__) . '/fixtures/testcase-1.csv');
      $csv = CSVParser::createFromString($csv);
      $this->basicParseAssertions($csv);
    }
    public function testParseFileHeaderOnRow2()
    {
      $csv = CSVParser::createFromFile(dirname(__FILE__) . '/fixtures/testcase-4.csv', NULL, 2);
      $this->basicParseAssertions($csv);
    }
    public function testParseStringHeaderOnRow2() {
      $csv = file_get_contents(dirname(__FILE__) . '/fixtures/testcase-4.csv');
      $csv = CSVParser::createFromString($csv, 2);
      $this->basicParseAssertions($csv);
    }
    public function testParseWithManualHeaders()
    {
      $csv = CSVParser::createFromFile(dirname(__FILE__) . '/fixtures/testcase-1.csv', NULL, 0);

      // We expect 6 rows of data
      $this->assertEquals(6, $csv->count());
      $this->assertEquals('Name ', $csv->getCell(0));
      $this->assertEquals('  Age', $csv->getCell(1));

      // Name the cols
      $csv->setHeaders(['A', 'B']);
      $this->assertEquals('Name ', $csv->A);
      $this->assertEquals('  Age', $csv->B);
      $csv->setRow(2);
      $this->assertEquals('Rich', $csv->A);
      $this->assertEquals(40, $csv->B);

      // Set the first row as headers.
      $csv->extractHeaders(1);
      // This should mean we now have headers...
      $this->assertEquals('Name ', $csv->headers[0]);
      $this->assertEquals('  Age', $csv->headers[1]);
      // It should also 'rewind'
      $this->assertEquals(1, $csv->getRowNumber());
      // Which means the first row of data should now be...
      $this->assertEquals('Rich', $csv->Name);
      $this->assertEquals(40, $csv->Age);
    }
    protected function basicParseAssertions($csv) {
      // We should be at the start.
      $this->assertEquals(1, $csv->getRowNumber());
      $this->assertEquals(5, $csv->count());
      $i=0;
      $expectations = [['Rich',  40], ['Fred',  1000], ['Wilma',  0], ['',  56], ['Bam Bam', '']];
      foreach ($csv as $row) {
        $expect = array_shift($expectations);
        $this->assertEquals($expect[0], $row->Name);
        $this->assertEquals($expect[1], $row->Age);
        $i++;
      }
      $this->assertEquals(5, $i, "Expected 5 records, got $i");
    }


    public function testRandomAccess()
    {
      $csv = new CSVParser();
      $csv->loadFromFile(dirname(__FILE__) . '/fixtures/testcase-1.csv');
      $this->assertEquals('Rich', $csv->Name);
      $this->assertEquals('Rich', $csv->getCell(0));
      $this->assertEquals('Rich', $csv->getCell(0,1));
      $this->assertEquals('Fred', $csv->getCell(0,2));
    }
    public function testMultiLine()
    {
      $csv = new CSVParser();
      $csv->loadFromFile(dirname(__FILE__) . '/fixtures/testcase-2.csv');
      $i=0;
      $expectations = [['Fred',  "The Cave\nHovelshire"], ['Betty',  'Elsewhere']];
      foreach ($csv as $row) {
        $expect = array_shift($expectations);
        $this->assertEquals($expect[0], $row->Name);
        $this->assertEquals($expect[1], $row->Address);
        $i++;
      }
      $this->assertEquals(2, $i, "Expected 2 records, got $i");
    }

    /**
     */
    public function testDodgyHeaders()
    {
      $this->expectException(\InvalidArgumentException::class);
      $this->expectExceptionMessage('Duplicate header name: Name');
      $csv = new CSVParser();
      $csv->loadFromFile(dirname(__FILE__) . '/fixtures/testcase-3-dodgy-headers.csv');
    }
}


