<?php

namespace Bermuda\HTTP;

use JsonException;
use Bermuda\String\Json;
use Bermuda\Headers\Header;
use Psr\Container\ContainerInterface;
use Bermuda\Detector\MimeTypeDetector;
use Bermuda\Detector\MimeTypes\Text;
use Bermuda\Detector\MimeTypes\Application;
use Psr\Http\Message\{ResponseFactoryInterface, ResponseInterface, StreamInterface};

final class Responder
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private ?MimeTypeDetector        $detector = null,
    )
    {
        $this->detector = $detector ?? new FinfoDetector();
    }

    /**
     * @param string $content
     * @return ResponseInterface
     */
    public function html(string $content): ResponseInterface
    {
        return $this->respond(content: $content, contentType: Text::html);
    }

    /**
     * @param string|null $content
     * @param string|null $contentType
     * @return ResponseInterface
     */
    public function notFound($content = null): ResponseInterface
    {
        if (is_string($content) || $content instanceof Stringable || $content === null) {
            return $this->respond(404, $content);
        }
        
        return $this->json(404, $content);
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
     * @param null $content
     * @return ResponseInterface
     * @throws JsonException
     */
    public function serverError($content = null): ResponseInterface
    {
        if (is_string($content) || $content instanceof Stringable || $content === null) {
            return $this->respond(500, $content);
        }
        
        return $this->json(500, $content);
    }

    /**
     * @param int $code
     * @param string|null $content
     * @param string|null $contentType
     * @return ResponseInterface
     */
    public function respond(int $code = 200, ?string $content = null, ?string $contentType = null): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($code);

        if ($content !== null) {
            ($response = $response->withHeader(Header::contentType, $contentType ??
                $this->detector->detectMimeType($content)))
                ->getBody()->write($content);

            $response = $response->withHeader(Header::contentLength, (int) $response->getBody()->getSize());
        }

        return $response;
    }

    /**
     * @param string $location
     * @param bool $permanent
     * @return ResponseInterface
     */
    public function redirect(string $location, bool $permanent = false): ResponseInterface
    {
        return $this->respond($permanent ? 301 : 302)->withHeader(Header::location, $location);
    }

    /**
     * @param $content
     * @return ResponseInterface
     * @throws JsonException
     */
    public function ok($content): ResponseInterface
    {
        if (is_string($content) || $content instanceof Stringable || $content === null) {
            return $this->respond(200, $content);
        }
        
        return $this->json(200, $content);
    }

    /**
     * @param $content
     * @return ResponseInterface
     * @throws JsonException
     */
    public function bad($content): ResponseInterface
    {
        if (is_string($content) || $content instanceof Stringable || $content === null) {
            return $this->respond(400, $content);
        }
        
        return $this->json(400, $content);
    }

    /**
     * @param int $code
     * @param $content
     * @return ResponseInterface
     * @throws JsonException
     */
    public function json(int $code, $content): ResponseInterface
    {
        if (!Json::isJson($content)) {
            $content = Json::encode($content);
        }

        return $this->respond($code, $content, Application::json);
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
        if (!$inline) {
            $disposition = ContentDisposition::attachment($filename);
        }

        return $this->respond(content: (string) $stream)
            ->withHeader(Header::contentDescription, 'File-transfer')
            ->withHeader(Header::contentDisposition, $disposition ?? ContentDisposition::inline($filename))
            ->withHeader(Header::contentTransferEncoding, 'binary')
            ->withHeader(Header::expires, 0)
            ->withHeader(Header::cacheControl, 'must-revalidate')
            ->withHeader(Header::pragma, 'public');
    }

    public function nginx(string $filename): ResponseInterface
    {
        return $this->respond()->withHeader('X-Accel-Redirect', $filename);
    }

    /**
     * @param string $content
     * @return ResponseInterface
     */
    public function text(string $content): ResponseInterface
    {
        return $this->respond(200, $content, Text::plain);
    }
}
