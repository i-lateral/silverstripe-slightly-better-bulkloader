<?php

namespace ilateral\SilverStripe\SlightlyBetterBulkLoader\Tests\Data;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class Player extends DataObject implements TestOnly
{
    private static $db = array(
        'FirstName' => 'Varchar(255)',
        'Biography' => 'HTMLText',
        'Birthday' => 'Date',
        'ExternalIdentifier' => 'Varchar(255)',
        'IsRegistered' => 'Boolean'
    );

    private static $required_fields = [
        'FirstName',
        'Birthday'
    ];
}
