<?php

namespace Bermuda\HTTP;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class OutputBufferingMiddleware implements MiddlewareInterface
{
    public const write_mode_prepend = 0;
    public const write_mode_no_write = -1;

    public function __construct(private StreamFactoryInterface $streamFactory, private ?int $mode = null)
    {
    }

    /**
     * @inheritDoc
     * @throws \Throwable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            ob_start();
            $response = $handler->handle($request);
            $output = ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        if (!empty($output) && $this->mode !== self::write_mode_no_write) {
            if ($this->mode === self::write_mode_prepend) {
                $body = $this->streamFactory->createStream($output . $response->getBody());
                $response = $response->withBody($body);
            } else {
                $response->getBody()->write($output);
            }
        }

        return $response;
    }
}
