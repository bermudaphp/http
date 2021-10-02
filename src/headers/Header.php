<?php

namespace Bermuda\HTTP\Headers;

final class Header
{
    public const acceptCharset      = 'Accept-Charset';
    public const acceptEncoding     = 'Accept-Encoding';
    public const acceptLanguage     = 'Accept-Language';
    public const acceptRanges       = 'Accept-Ranges';
    public const age                = 'Age';
    public const allow              = 'Allow';
    public const alternates         = 'Alternates';
    public const authorization      = 'Authorization';
    public const cacheControl       = 'Cache-Control';
    public const connection         = 'Connection';
    public const contentDisposition = 'Content-Disposition';
    public const contentDescription = 'Content-Description';
    public const contentEncoding    = 'Content-Encoding';
    public const contentTransferEncoding = 'Content-Transfer-Encoding';
    public const contentLanguage    = 'Content-Language';
    public const contentLength      = 'Content-Length';
    public const contentLocation    = 'Content-Location';
    public const contentMD5         = 'Content-MD5';
    public const contentRange       = 'Content-Range';
    public const contentType        = 'Content-Type';
    public const contentVersion     = 'Content-Version';
    public const date               = 'Date';
    public const derivedFrom        = 'Derived-From';
    public const eTag               = 'ETag';
    public const expect             = 'Expect';
    public const expires            = 'Expires';
    public const from               = 'From';
    public const host               = 'Host';
    public const ifMatch            = 'If-Match';
    public const ifModifiedSince    = 'If-Modified-Since';
    public const ifNoneMatch        = 'If-None-Match';
    public const ifRange            = 'If-Range';
    public const ifUnmodifiedSince  = 'If-Unmodified-Since';
    public const lastModified       = 'Last-Modified';
    public const link               = 'Link';
    public const location           = 'Location';
    public const maxForwards        = 'Max-Forwards';
    public const mimeVersion        = 'MIME-Version';
    public const pragma             = 'Pragma';
    public const proxyAuthenticate  = 'Proxy-Authenticate';
    public const proxyAuthorization = 'Proxy-Authorization';
    public const public             = 'Public';
    public const range              = 'Range';
    public const referer            = 'Referer';
    public const retryAfter         = 'Retry-After';
    public const server             = 'Server';
    public const title              = 'Title';
    public const TE                 = 'TE';
    public const trailer            = 'Trailer';
    public const transferEncoding   = 'Transfer-Encoding';
    public const upgrade            = 'Upgrade';
    public const userAgent          = 'User-Agent';
    public const vary               = 'Vary';
    public const via                = 'Via';
    public const warning            = 'Warning';
    public const wwwAuthenticate    = 'WWW-Authenticate';
    public const nginxRedirect      = 'X-Accel-Redirect';
    
    public static function allow(array $methods): string 
    {
        return implode(', ', array_map('strtoupper', $methods));
    }
}
