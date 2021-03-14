<?php

namespace Bermuda\HTTP;

/**
 * Class ContentDisposition
 * @package Bermuda\HTTP
 */
final class ContentDisposition
{
    private function __construct()
    {
    }

    const inline = 'inline';
    const attachment = 'attachment';
    const formData = 'form-data';

    /**
     * @param string $filename
     * @return string
     */
    public static function attachment(string $filename): string
    {
        return self::attachment . '; filename="' . $filename .'"';
    }

    /**
     * @param string $fieldName
     * @param string $filename
     * @return string
     */
    public static function formData(string $fieldName, string $filename): string
    {
        return self::formData . '; name="'. $fieldName .'"; filename="' . $filename . '"';
    }
}
