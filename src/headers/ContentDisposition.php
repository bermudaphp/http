<?php

namespace Bermuda\HTTP\Headers;

final class ContentDisposition
{
    public const inline = 'inline';
    public const attachment = 'attachment';
    public const formData = 'form-data';

    /**
     * @param string $filename
     * @return string
     */
    public static function attachment(string $filename): string
    {
        return self::attachment . self::ensureValidName($filename);
    }

    /**
     * @param string $filename
     * @return string
     */
    public static function inline(string $filename): string
    {
        return self::inline . self::ensureValidName($filename);
    }

    private static function ensureValidName(string $filename): string
    {
        return'filename="' . preg_replace('/[\x00-\x1F\x7F\"]/', ' ', $filename) . '"'
            . "; filename*=UTF-8''" . rawurlencode($filename);
    }

    /**
     * @param array $fields
     * @return string
     */
    public static function formData(array $fields): string
    {
        $string = self::formData . ';';

        foreach ($fields as $name => $value) {
            $string .= $name .'="'. $value .'";';
        }

        return $string;
    }
}
