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
        return self::attachment . '; filename="' . $filename .'"';
    }

    /**
     * @param string $filename
     * @return string
     */
    public static function inline(string $filename): string
    {
        return self::inline . '; filename="' . $filename .'"';
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
