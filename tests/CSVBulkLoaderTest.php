<?php

namespace ilateral\SilverStripe\SlightlyBetterBulkLoader\Tests;

use SilverStripe\ORM\DataObject;
use SilverStripe\Dev\SapphireTest;
use ilateral\SilverStripe\SlightlyBetterBulkLoader\CsvBulkLoader;
use ilateral\SilverStripe\SlightlyBetterBulkLoader\Tests\Data\Player;

class CSVBulkLoaderTest extends SapphireTest
{

    /**
     * Name of csv test dir
     *
     * @var string
     */
    protected $csv_path = null;

    protected static $extra_dataobjects = [
        Player::class
    ];

    protected function setUp()
    {
        parent::setUp();
        $this->csvPath = __DIR__ . '/csv/';
    }

    /**
     * Does the importer correct CSV
     */
    public function testLoad()
    {
        $loader = new CsvBulkLoader(Player::class);
        $filepath = $this->csvPath . 'Player.csv';
        $file = fopen($filepath, 'r');
        $results = $loader->load($filepath);

        $this->assertEquals(
            5,
            $results->CreatedCount(),
            'Test correct count of imported data'
        );

        $obj = DataObject::get_one(
            Player::class,
            [ 'FirstName' => 'John' ]
        );

        $this->assertNotNull($obj);
        $this->assertEquals("He's a good guy", $obj->Biography);
        $this->assertEquals("1988-01-31", $obj->Birthday);
        $this->assertEquals("1", $obj->IsRegistered);
        fclose($file);
    }

    /**
     * Does the importer fail on a duplicate column?
     */
    public function testDuplicateColumn()
    {
        // Remove any existing records
        Player::get()->removeAll();

        $loader = new CsvBulkLoader(Player::class);
        $filepath = $this->csvPath . 'PlayerDuplicate.csv';
        $file = fopen($filepath, 'r');
        $results = $loader->load($filepath);

        $this->assertEquals(
            1,
            $results->ErrorCount(),
            'Test error count on duplicates'
        );

        $obj = DataObject::get_one(
            Player::class,
            [ 'FirstName' => 'John' ]
        );

        $this->assertNull($obj);
        fclose($file);
    }

    /**
     * Does the importer fail on a duplicate column
     */
    public function testMissingRequired()
    {
        // Remove any existing records
        Player::get()->removeAll();

        $loader = new CsvBulkLoader(Player::class);
        $filepath = $this->csvPath . 'PlayerMissingRequired.csv';
        $file = fopen($filepath, 'r');
        $results = $loader->load($filepath);

        $this->assertEquals(
            1,
            $results->ErrorCount(),
            'Test errors generated when required fields missing'
        );

        $obj = DataObject::get_one(
            Player::class,
            ['FirstName' => 'Jane']
        );

        $this->assertNull($obj);
        fclose($file);
    }
}
