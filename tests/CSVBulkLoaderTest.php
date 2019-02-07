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

        var_dump($results);

        // Test that right amount of columns was imported
        $this->assertEquals(
            5,
            $results->CreatedCount(),
            'Test correct count of imported data'
        );

        // Test that columns were correctly imported
        $obj = DataObject::get_one(
            Player::class,
            [ "FirstName" => 'John' ]
        );

        $this->assertNotNull($obj);
        $this->assertEquals("He's a good guy", $obj->Biography);
        $this->assertEquals("1988-01-31", $obj->Birthday);
        $this->assertEquals("1", $obj->IsRegistered);
        fclose($file);
    }

} 