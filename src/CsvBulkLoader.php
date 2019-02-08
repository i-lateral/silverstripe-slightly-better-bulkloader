<?php

namespace ilateral\SilverStripe\SlightlyBetterBulkLoader;

use SilverStripe\Control\Director;
use SilverStripe\Dev\CsvBulkLoader as SS_CsvBulkLoader;

/**
 * Custom CSV importer that removes/de-duplicates blank header columns and also
 * tracks errors while importing.
 */
class CsvBulkLoader extends SS_CsvBulkLoader
{
    /**
     * @param string $filepath
     * @param boolean $preview
     *
     * @return null|BulkLoader_Result
     */
    protected function processAll($filepath, $preview = false)
    {
        $previousDetectLE = ini_get('auto_detect_line_endings');
        ini_set('auto_detect_line_endings', true);

        $result = BulkLoader_Result::create();

        try {
            $filepath = Director::getAbsFile($filepath);
            $csvReader = CustomReader::createFromPath($filepath, 'r');

            $tabExtractor = function ($row, $rowOffset, $iterator) {
                foreach ($row as &$item) {
                    // [SS-2017-007] Ensure all cells with leading tab and then [@=+] have the tab removed on import
                    if (preg_match("/^\t[\-@=\+]+.*/", $item)) {
                        $item = ltrim($item, "\t");
                    }
                }
                return $row;
            };

            if (isset($this->columnMap) && count($this->columnMap)) {
                $headerMap = $this->getNormalisedColumnMap();
                $remapper = function ($row, $rowOffset, $iterator) use ($headerMap, $tabExtractor) {
                    $row = $tabExtractor($row, $rowOffset, $iterator);
                    foreach ($headerMap as $column => $renamedColumn) {
                        if ($column == $renamedColumn) {
                            continue;
                        }
                        if (array_key_exists($column, $row)) {
                            if (strpos($renamedColumn, '_ignore_') !== 0) {
                                $row[$renamedColumn] = $row[$column];
                            }
                            unset($row[$column]);
                        }
                    }
                    return $row;
                };
            } else {
                $remapper = $tabExtractor;
            }

            $rows = null;

            if ($this->hasHeaderRow) {
                $rows = $csvReader->fetchAssoc(0, $remapper);
            } elseif ($this->columnMap) {
                $rows = $csvReader->fetchAssoc($headerMap, $remapper);
            }

            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $this->processRecord($row, $this->columnMap, $result, $preview);
                }
            }
        } catch (\Exception $e) {
            $failedMessage = sprintf("Failed to parse %s", $filepath);
            if (Director::isDev()) {
                $failedMessage = sprintf($failedMessage . " because %s", $e->getMessage());
            }
            $result->addError($failedMessage);
        } finally {
            ini_set('auto_detect_line_endings', $previousDetectLE);
        }

        return $result;
    }

    /**
     * Process a single record
     *
     * @todo Better messages for relation checks and duplicate detection
     * Note that columnMap isn't used.
     *
     * @param array $record
     * @param array $columnMap
     * @param BulkLoader_Result $results
     * @param boolean $preview
     *
     * @return int
     */
    protected function processRecord($record, $columnMap, &$results, $preview = false)
    {
        $required = $this->getRequiredFields();
        $current_row = $results->getTotal() + 1;
        $obj = singleton($this->objectClass);
        $missing = [];

        foreach ($required as $field) {
            $valid = false;
            $label = $obj->fieldLabel($field);

            // Is the field label used instead of the field name and is it set?
            if (isset($record[$label]) && !empty($record[$label])) {
                $valid = true;
            }

            // Is required data missing? If so track an error
            if (!$valid && (isset($record[$field]) && !empty($record[$field]))) {
                $valid = true;
            }

            if (!$valid) {
                $missing[] = $label . "/" . $field;
            }
        }

        // If we have missing data, add an error
        if (count($missing) > 0) {
            $results->addError(
                _t(
                    __CLASS__ . '.Required',
                    'Required fields "{fields}" not set on row "{row}"',
                    [
                        'fields' => implode(", ", $missing),
                        'row' => $current_row
                    ]
                )
            );
            return null;
        }

        // If validation passed, process as usual
        return parent::processRecord($record, $columnMap, $results, $preview);
    }
}
