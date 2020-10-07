<?php


namespace Bermuda\Http;


/**
 * Class ResponseHeader
 * @package Bermuda\Http
 */
final class ResponseHeader
{
    private function __construct()
    {}

    public const ContentType = 'Content-Type';
    public const ContentLength = 'Content-Length';
    public const ContentDisposition = 'Content-Disposition';
    public const ContentDescription = 'Content-Description';
    public const ContentTransferEncoding = 'Content-Transfer-Encoding';
    public const expires = 'Expires';
    public const location = 'Location';
    public const CacheControl = 'Cache-Control';
    public const pragma = 'Pragma';
}
