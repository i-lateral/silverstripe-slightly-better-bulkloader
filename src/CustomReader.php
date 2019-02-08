<?php

namespace ilateral\SilverStripe\SlightlyBetterBulkLoader;

use League\Csv\Reader;

class CustomReader extends Reader
{
    /**
     * Should this reader ignore column headers that are null when importing?
     *
     * Sometimes Excel adds null headers to the end of a CSV and this can result in the
     * CSV failing to parse.
     *
     * @var boolean
     */
    protected $ignore_empty_headers = true;

    /**
     * Validates the array to be used by the fetchAssoc method
     *
     * @param array $keys
     *
     * @throws InvalidArgumentException If the submitted array fails the assertion
     *
     * @return array
     */
    protected function validateKeys(array $keys)
    {
        if ($this->getIgnoreEmptyHeaders()) {
            $last = $this->findLastHeaderPos($keys);
            $new_keys = [];
            
            for ($i = 0; $i < count($keys); $i++) {
                if ($i <= $last) {
                    $new_keys[] = $keys[$i];
                }
            }

            $keys = $new_keys;
        }

        return parent::validateKeys($keys);
    }

    /**
     * Attempt to find the position of the last legitimate
     * header column (a column that is not null and is the last
     * item in the list that is not null).
     *
     * @param array $keys
     *
     * @return int
     */
    protected function findLastHeaderPos(array $keys)
    {
        $last = 0;
        $count = count($keys);

        for ($i = 0; $i < $count; $i++) {
            $curr = trim($keys[$i]);
            $j = $i;

            // If the current field isn't empty, skip
            if (!empty($curr)) {
                $last = $i;
                continue;
            }

            // If the column is empty, the previous one isn't
            // and the next few are or don't exist, assume
            // we have hit the last item. Set and break.
            if (empty($curr) && isset($keys[$i-1])) {
                $temp = 0;
                for ($j = $i; $j < $count; $j++) {
                    if (!empty($keys[$j])) {
                        $temp = $keys[$j];
                    }
                }

                if ($temp > 0) {
                    $last = $i;
                }
            }
        }

        return $last;
    }

    /**
     * Get the ignore_empty_headers param
     *
     * @return boolean
     */
    public function getIgnoreEmptyHeaders()
    {
        return $this->ignore_empty_headers;
    }

    /**
     * Set the ignore_empty_headers param
     *
     * @param boolean $ignore_empty_headers Ignore header keys that are blank?
     *
     * @return self
     */
    public function setIgnoreEmptyHeaders(boolean $ignore_empty_headers)
    {
        $this->ignore_empty_headers = $ignore_empty_headers;
        return $this;
    }
}
