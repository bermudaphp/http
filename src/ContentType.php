<?php


namespace Bermuda\Http;


/**
 * Class ContentType
 * @package Bermuda\Http
 */
final class ContentType
{
    private function __construct()
    {
    }

    public const header = 'Content-Type';

    public const html = 'text/html';
    public const json = 'application/json';
}
