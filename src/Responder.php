<?php

namespace Bermuda\HTTP;

use JsonException;
use Bermuda\Stdlib\Json;
use Bermuda\Stdlib\Arrayable;
use Bermuda\HTTP\Headers\Header;
use Psr\Container\ContainerInterface;
use Bermuda\Detector\FinfoDetector;
use Bermuda\Detector\MimeTypeDetector;
use Bermuda\Detector\MimeTypes\Text;
use Bermuda\Detector\MimeTypes\Application;
use Bermuda\HTTP\Headers\ContentDisposition;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamInterface;

final class Responder
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private ?MimeTypeDetector        $detector = null,
    ) {
        $this->detector = $detector ?? new FinfoDetector;
    }

    /**
     * @param ContainerInterface $container
     * @return static
     */
    public static function fromContainer(ContainerInterface $container): self
    {
        $detector = $container->has(MimeTypeDetector::class) ? $container->get(MimeTypeDetector::class)
            : new FinfoDetector();

        return new self($container->get(ResponseFactoryInterface::class), $detector);
    }

    /**
     * @return ResponseInterface
     * @throws JsonException
     */
    public function noContent(): ResponseInterface
    {
        return $this->respond(204);
    }

    /**
     * @param mixed|null $content
     * @param string|null $contentType
     * @return ResponseInterface
     */
    public function notFound(mixed $content = null, ?string $contentType = null): ResponseInterface
    {
        return $this->respond(404, $content);
    }

    /**
     * @param int $code
     * @param $content
     * @param string|null $contentType
     * @return ResponseInterface
     */
    public function respond(?int $code = null, $content = null, ?string $contentType = null): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($code ?? ($content === null ? 204 : 200));

        if ($content !== null) {
            if ($content instanceof Arrayable) $content = $content->toArray();
            if (!is_string($content) && !$content instanceof \Stringable) {
                $content = Json::encode($content);
                $contentType = Application::json;
            }

            ($response = $response->withHeader(Header::contentType, $contentType ??
                $this->detector->detectMimeType($content)))
                ->getBody()->write((string) $content);

            if ($code === null && Json::isEmpty($content, false)) $response = $response->withStatus(404);
            $response = $response->withHeader(Header::contentLength, (int) $response->getBody()->getSize());
        }

        return $response;
    }

    /**
     * @param string $location
     * @param bool $permanent
     * @return ResponseInterface
     */
    public function redirect(string $location, int $code = 302): ResponseInterface
    {
        return $this->respond(300 > $code || $code > 308 ? 302 : $code)
            ->withHeader(Header::location, $location);
    }

    /**
     * @param StreamInterface $stream
     * @return ResponseInterface
     */
    public function download(StreamInterface $stream): ResponseInterface
    {
        return $this->file($stream, false);
    }

    /**
     * @param StreamInterface $stream
     * @param bool $inline
     * @return ResponseInterface
     */
    public function file(StreamInterface $stream, bool $inline = true): ResponseInterface
    {
        $filename = basename($stream->getMetadata('uri'));
        if (!$inline) $disposition = ContentDisposition::attachment($filename);

        return $this->respond(content: (string) $stream)
            ->withHeader(Header::contentDescription, 'File-transfer')
            ->withHeader(Header::contentDisposition, $disposition ?? ContentDisposition::inline($filename))
            ->withHeader(Header::contentTransferEncoding, 'binary');
    }

    /**
     * @param string $filename
     * @return ResponseInterface
     */
    public function nginx(string $filename): ResponseInterface
    {
        return $this->respond()->withHeader('X-Accel-Redirect', $filename);
    }
}
