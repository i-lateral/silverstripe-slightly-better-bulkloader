<?php

namespace ilateral\SilverStripe\SlightlyBetterBulkLoader;

use SilverStripe\Core\Extension;

/**
 * Add required fields to BulkLoader
 */
class BulkLoaderExtension extends Extension
{
    /**
     * Try to find any fields that may be required by the current object
     *
     * @return array
     */
    public function getRequiredFields($use_field_labels = false)
    {
        // Check if the import data is valid first
        $class = $this->getOwner()->objectClass;
        $obj = $class::singleton();
        $required = [];
        $custom = [];

        // If a "required_fields" config variable is set
        // add it's fields
        $config = $obj->config()->get("required_fields");
        
        if (isset($config) && is_array($config)) {
            $required = array_merge(
                $required,
                $config
            );
        }

        // Get any fields required by CMS validator if available
        if (method_exists($obj, "getCMSValidator")) {
            $required = array_merge(
                $required,
                $obj->getCMSValidator()->getRequired()
            );
        }

        // Ensure we use a field label that would have been exported
        foreach ($required as $field) {
            $custom[] = $field;
        }

        return $custom;
    }
}
