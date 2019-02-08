<?php

namespace ilateral\SilverStripe\SlightlyBetterBulkLoader\Tests;

use SilverStripe\Dev\SapphireTest;
use ilateral\SilverStripe\SlightlyBetterBulkLoader\BulkLoader_Result;
use ilateral\SilverStripe\SlightlyBetterBulkLoader\Tests\Data\Player;

class BulkLoader_ResultTest extends SapphireTest
{
    protected static $extra_dataobjects = [
        Player::class
    ];

    public function testResultErrors()
    {
        $results = BulkLoader_Result::create();
        $results->addError("Error With Record", 1);
        $this->assertEquals($results->ErrorCount(), 1);
        $this->assertSame(
            "Error With Record",
            $results->getErrors()[0],
            'The record 1 should be marked as error'
        );
    }

    public function testGetCreated()
    {
        $results = BulkLoader_Result::create();

        $player = Player::create(['FirstName' => 'Rangi', 'Status' => 'Possible']);
        $player->write();
        $results->addCreated($player, 'Speedster');

        $this->assertCount(1, $results->getCreated());
    }

    public function testGetUpdated()
    {
        $results = BulkLoader_Result::create();

        Player::create(
            [
                'FirstName' => 'Vincent',
                'Status' => 'Available'
            ]
        )->write();

        $player = Player::get()->find('FirstName', 'Vincent');
        $player->Status = 'Unavailable';
        $player->write();
        $results->addUpdated($player, 'Injured');

        $this->assertCount(1, $results->getUpdated());
    }

    public function testGetDeleted()
    {
        $results = BulkLoader_Result::create();

        Player::create(
            [
                'FirstName' => 'Vincent',
                'Status' => 'Available'
            ]
        )->write();

        $player = Player::get()->find('FirstName', 'Vincent');
        $results->addDeleted($player, 'Retired');
        $player->delete();

        $this->assertCount(1, $results->getDeleted());
    }

    public function testGetErrors()
    {
        $results = BulkLoader_Result::create();
        $results->addError("Error With Record", 1);

        $this->assertCount(1, $results->getErrors());
    }

    public function testGetTotal()
    {
        $results = BulkLoader_Result::create();

        Player::create(
            [
                'FirstName' => 'Vincent',
                'Status' => 'Available'
            ]
        )->write();

        $player = Player::get()->find('FirstName', 'Vincent');
        $player->Status = 'Unavailable';
        $player->write();
        $results->addUpdated($player, 'Injured');

        $this->assertEquals($results->getTotal(), 1);

        $player = Player::create(['FirstName' => 'Rangi', 'Status' => 'Possible']);
        $player->write();
        $results->addCreated($player, 'Speedster');

        $this->assertEquals($results->getTotal(), 2);

        $player = Player::get()->find('FirstName', 'Vincent');
        $results->addDeleted($player, 'Retired');
        $player->delete();

        $this->assertEquals($results->getTotal(), 3);

        $results->addError("Error With Record", 1);

        $this->assertEquals($results->getTotal(), 4);
    }
}
