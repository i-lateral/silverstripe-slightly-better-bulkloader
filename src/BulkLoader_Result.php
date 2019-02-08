<?php

namespace ilateral\SilverStripe\SlightlyBetterBulkLoader;

use SilverStripe\ORM\ValidationResult;
use SilverStripe\Dev\BulkLoader_Result as SS_BulkLoader_Result;

/**
 * Custom bulk loader result that also tracks errors in submission
 */
class BulkLoader_Result extends SS_BulkLoader_Result
{

    /**
     * List of errors tracked, each error should be an error message
     * (as a string).
     *
     * @var array (see {@link $created})
     */
    protected $errors = [];

    /**
     * Return the number of errors
     *
     * @return int
     */
    public function ErrorCount()
    {
        return count($this->errors);
    }

    /**
     * Add an error message to the stack
     *
     * @param string $message The error message
     *
     * @return self
     */
    public function addError($message, $id = null)
    {
        $this->lastChange = [
            'Message' => $message,
            'ID' => $id,
            '_BulkLoaderMessage' => $message
        ];
        $this->errors[] = $message;
        $this->lastChange['ChangeType'] = 'error';

        return $this;
    }

    /**
     * Merges another BulkLoader_Result into this one.
     *
     * @param BulkLoader_Result $other
     */
    public function merge(SS_BulkLoader_Result $other)
    {
        $this->created = array_merge($this->created, $other->getCreated());
        $this->updated = array_merge($this->updated, $other->getUpdated());
        $this->deleted = array_merge($this->deleted, $other->getDeleted());
        $this->errors = array_merge($this->errors, $other->getErrors());
    }

    /**
     * Get he total number of results tracked
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->CreatedCount() + $this->UpdatedCount() + $this->DeletedCount() + $this->ErrorCount();
    }

    /**
     * Get created array
     *
     * @return array
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Get updated array
     *
     * @return array
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Get updated array
     *
     * @return array
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Get errors array
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get an array of all messages stored
     *
     * @return array
     */
    public function getMessagesArray()
    {
        $messages = [];

        if ($this->CreatedCount() > 0) {
            $messages[] = _t(
                'SilverStripe\\Admin\\ModelAdmin.IMPORTEDRECORDS',
                "Imported {count} records.",
                ['count' => $this->CreatedCount()]
            );
        }

        if ($this->UpdatedCount() > 0) {
            $messages[] = _t(
                'SilverStripe\\Admin\\ModelAdmin.UPDATEDRECORDS',
                "Updated {count} records.",
                ['count' => $results->UpdatedCount()]
            );
        }

        if ($this->DeletedCount() > 0) {
            $messages[] = _t(
                'SilverStripe\\Admin\\ModelAdmin.DELETEDRECORDS',
                "Deleted {count} records.",
                ['count' => $results->DeletedCount()]
            );
        }
        
        // Finally include any errors
        return array_merge($messages, $this->getErrors());
    }

    /**
     * Return a string of all messages (that can be rendered into a message window)
     *
     * @param bool $html Add the newline as a HTML "<br/>"
     *
     * @return string
     */
    public function getMessagesString($delimiter = ";")
    {
        return implode($delimiter, $this->getMessagesArray());
    }

    /**
     * Get the "type" of message (using SilverStripe's Validation result)
     *
     * @return string
     */
    public function getMessageType()
    {
        $type = ValidationResult::TYPE_GOOD;

        if (count($results->getErrors()) > 0) {
            $type = ValidationResult::TYPE_ERROR;
        }

        return $type;
    }
}
