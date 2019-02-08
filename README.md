# Slightly Better Bulkloader

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/i-lateral/silverstripe-slightly-better-bulkloader/badges/quality-score.png?b=1.0)](https://scrutinizer-ci.com/g/i-lateral/silverstripe-slightly-better-bulkloader/?branch=1.0)
[![Build Status](https://travis-ci.org/i-lateral/silverstripe-slightly-better-bulkloader.svg?branch=1.0)](https://travis-ci.org/i-lateral/silverstripe-slightly-better-bulkloader)

It's the SilverStripe bulk loader, but ever so slightly better!

This module expands the default SS version and adds error logging per import row and attempts to solve issues where Excel sometimes
adds blank columns to the end of a CSV (which then fails to import)

## Install

Instalation via composer:

    # composer require i-lateral/silverstripe-slightly-better-bulkloader

## Usage

By default this module should automatically replace `BulkLoader_Result` with a custom version
that track errors.

However to make the most of this, you need to also implement your own version of `CSVBulkLoader` for example:

### ModelAdmin

Adding the Custom CSV uploader via ModelAdmin

```PHP
namespace App\Admin;

use SilverStripe\Admin\ModelAdmin;
use ilateral\SilverStripe\SlightlyBetterBulkLoader\CsvBulkLoader;

class MyModelAdmin extends ModelAdmin
{
    private static $managed_models = [
        MyDataObject::class
    ];

    private static $model_importers = [
        MyDataObject::class => CsvBulkLoader::class
    ];
}
```

### Manualy Called

An example of adding a custom CSV import after a form has been submitted.

```PHP

use SilverStripe\Forms\Form;
use ilateral\SilverStripe\SlightlyBetterBulkLoader\CsvBulkLoader;

class MyImportForm extends Form
{
    public function import($data, $form)
    {
        $loader = CsvBulkLoader::create();
        $results = $loader->load($_FILES['_CsvFile']['tmp_name']);

        $form->sessionMessage(
            $results->getMessageString("</br>"),
            $results->getMessageType(),
            ValidationResult::CAST_HTML
        );

        return $this->redirectBack();
    }
}
```