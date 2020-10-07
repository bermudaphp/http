<?php


namespace Bermuda\Http;


use Bermuda\Router\GeneratorInterface;
use Bermuda\Templater\RendererInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;


use function Bermuda\service;


/**
 * Class Response
 * @package Bermuda\Http
 */
final class Response
{
    private function __construct()
    {}

    /**
     * @param ResponseInterface $response
     * @param string $content
     * @param string $contentType
     * @return ResponseInterface
     */
    public static function write(ResponseInterface $response, string $content, string $contentType): ResponseInterface
    {
        static::isWritable($stream = $response->getBody());
        $stream->write($content);
        return $response->withHeader(ResponseHeader::ContentType, $contentType);
    }

    /**
     * @param StreamInterface $stream
     */
    private static function isWritable(StreamInterface $stream): void
    {
        if (!$stream->isWritable())
        {
            throw new \RuntimeException('Response is un writable');
        }
    }

    /**
     * @param int $code
     * @param string $reasonPhrase
     * @return ResponseInterface
     */
    public static function make(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return service(ResponseFactoryInterface::class)->createResponse($code, $reasonPhrase);
    }

    /**
     * @param string $content
     * @return ResponseInterface
     */
    public static function json(string $content): ResponseInterface
    {
        return self::writeJson(self::make(), $content);
    }

    /**
     * @param string $content
     * @return ResponseInterface
     */
    public static function html(string $content): ResponseInterface
    {
        return self::writeHtml(self::make(), $content);
    }

    /**
     * @param string $filename
     * @param string|null $mimeType
     * @return ResponseInterface
     */
    public static function file(string $filename, ?string $mimeType = null): ResponseInterface
    {
        return self::writeFile(self::make(), $filename, $mimeType);
    }

    /**
     * @param ResponseInterface $response
     * @param string $content
     * @return ResponseInterface
     */
    public static function writeJson(ResponseInterface $response, string $content): ResponseInterface
    {
        return self::write($response, $content, ContentType::json);
    }

    /**
     * @param ResponseInterface $response
     * @param string $filename
     * @param string|null $mimeType
     * @return ResponseInterface
     * @throws \RuntimeException
     */
    public static function writeFile(ResponseInterface $response, string $filename, ?string $mimeType = null): ResponseInterface
    {
        self::isWritable($body = $response->getBody());

        $filesize = 0;

        if ($resource = fopen($filename, 'r'))
        {
            while (!feof($resource))
            {
                $filesize += $response->getBody()->write(fread($resource, 8192));
            }

            fclose($resource);
        }

        return $response->withHeader(ResponseHeader::ContentType, $mimeType ?? mime_content_type($filename))
            ->withHeader(ResponseHeader::ContentLength, $filesize);
    }

    /**
     * @param ResponseInterface $response
     * @param string $content
     * @return ResponseInterface
     */
    public static function writeHtml(ResponseInterface $response, string $content): ResponseInterface
    {
        return self::write($response, $content, ContentType::html);
    }

    /**
     * @param string $filename
     * @param string|null $mimeType
     * @return ResponseInterface
     */
    public static function serverSendFile(string $filename, ?string $mimeType = null): ResponseInterface
    {
        return self::make();
    }

    /**
     * @param string $filename
     * @param array|null $options
     * @param ResponseInterface|null $response
     * @return ResponseInterface
     */
    public static function sendFile(string $filename, ?array $options = [], ?ResponseInterface $response = null): ResponseInterface
    {
        return self::writeFile($response ?? self::make(), $filename, $options['mimeType'] ?? null)
            ->withHeader(ResponseHeader::ContentDescription, 'File Transfer')
            ->withHeader(ResponseHeader::ContentDisposition, ContentDisposition::attachment($options['filename'] ?? basename($filename)))
            ->withHeader(ResponseHeader::ContentTransferEncoding, 'binary')
            ->withHeader(ResponseHeader::expires, '0')
            ->withHeader(ResponseHeader::CacheControl, 'must-revalidate')
            ->withHeader(ResponseHeader::pragma, 'public');
    }

    /**
     * @param string $template
     * @param array $params
     * @return ResponseInterface
     */
    public function view(string $template, array $params = []): ResponseInterface
    {
        return self::html(service(RendererInterface::class)->render($template, $params));
    }

    /**
     * @param string $name
     * @param array $attributes
     * @param bool $movedPermanently
     * @return ResponseInterface
     */
    public function route(string $name, array $attributes = [], bool $movedPermanently = false): ResponseInterface
    {
        return self::redirect(service(GeneratorInterface::class)->generate($name, $attributes), $movedPermanently);
    }

    /**
     * @param ResponseInterface $response
     * @param string $location
     * @param bool $movedPermanently
     * @return ResponseInterface
     */
    public static function location(ResponseInterface $response, string $location, bool $movedPermanently = false): ResponseInterface
    {
        return $response->withHeader(ResponseHeader::location, $location)
            ->withStatus($movedPermanently ? 301 : 302);
    }

    /**
     * @param string $location
     * @param bool $movedPermanently
     * @return ResponseInterface
     */
    public static function redirect(string $location, bool $movedPermanently = false): ResponseInterface
    {
        return self::location(self::make(), $location, $movedPermanently);
    }
}
