<?php

namespace Bermuda\HTTP;

use Bermuda\Detector\FinfoDetector;
use Bermuda\Detector\MimeTypeDetector;
use Bermuda\HTTP\Headers\Header;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ContentTypeInjectionMiddleware implements MiddlewareInterface
{
    public function __construct(private ?MimeTypeDetector $detector = null)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        return self::injectContentType($response, $this->detector);
    }

    public static function injectContentType(ResponseInterface $response, MimeTypeDetector $detector = null): ResponseInterface
    {
        if (!$response->hasHeader(Header::contentLength)
            && ($size = $response->getBody()->getSize()) !== null) {
            $response = $response->withHeader(Header::contentLength, $size);
        }

        if (!$response->hasHeader(Header::contentType)
            && ($size ?? null) !== null) {
            $type = ($detector ?? new FinfoDetector())->detectMimeType((string) $response->getBody());
            return $response->withHeader(Header::contentType, $type);
        }

        return $response;
    }
}
