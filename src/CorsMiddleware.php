<?php

namespace Bermuda\HTTP;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class CorsMiddleware implements MiddlewareInterface
{
    private array $origins = [];
    private array $allowedMethods = [];
    private array $allowedHeaders = [];
    private array $exposedHeaders = [];
    private bool $allowCredential = true;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if (!$request->hasHeader('origin')) {
            return $response;
        }

        if ($this->origins !== []) {
            if (in_array('*', $this->origins)) {
                $response = $response->withHeader('Access-Control-Allow-Origin', '*');
            } elseif (in_array($origin = $request->getHeader('origin')[0], $this->origins)) {
                $response = $response->withHeader('Access-Control-Allow-Origin', $origin)
                    ->withHeader('Vary', $origin);
            }
        }

        if ($this->allowCredential) {
            $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        }

        if ($this->allowedMethods !== []) {
            $response = $response->withHeader('Access-Control-Allow-Methods', implode(',', $this->allowedMethods));
        }

        if ($this->allowedHeaders !== []) {
            $response = $response->withHeader('Access-Control-Allow-Headers', implode(',', $this->allowedHeaders));
        }

        if ($this->exposedHeaders !== []) {
            $response = $response->withHeader('Access-Control-Expose-Headers', implode(',', $this->exposedHeaders));
        }

        return $response;
    }

    /**
     * @param string|string[] $origins
     * @param bool $allowCredential
     * @return static
     */
    public static function for(string|array $origins, bool $allowCredential = true): self
    {
        ($self = new self())->origins($origins);
        $self->allowCredential = $allowCredential;

        return $self;
    }

    /**
     * @param string|array|null $origins
     * @return string[]|string
     */
    public function origins(string|array $origins = null): array|string
    {
        $current = $this->origins === [] ? '*'
            : (count($this->origins) == 1
                ? $this->origins[0] : $this->origins);

        if ($origins == null) {
            return $current;
        }

        $this->origins = [];
        foreach (is_array($origins) ? $origins : [$origins] as $domain) {
            $this->origins[] = $domain;
        }

        return $current;
    }

    /**
     * @param bool|null $mode
     * @return bool
     */
    public function allowCredential(?bool $mode = null): bool
    {
        if ($mode == null) {
            return $this->allowCredential;
        }

        $current = $this->allowCredential;
        $this->allowCredential = $mode;

        return $current;
    }

    /**
     * @param string|string[]|null $methods
     * @return string[]
     */
    public function allowedMethods(string|array $methods = null): array
    {
        $current = $this->allowedMethods;

        if ($methods == null) {
            return $current;
        }

        $this->allowedMethods = [];
        foreach (is_array($methods) ? $methods : [$methods] as $method) {
            $this->allowedMethods[] = $method;
        }

        return $current;
    }

    /**
     * @param string|string[]|null $headers
     * @return string[]
     */
    public function allowedHeaders(string|array $headers = null): array
    {
        $current = $this->allowedHeaders;

        if ($headers == null) {
            return $current;
        }

        $this->allowedHeaders = [];
        foreach (is_array($headers) ? $headers : [$headers] as $header) {
            $this->allowedHeaders[] = $header;
        }

        return $current;
    }

    /**
     * @param string|string[]|null $headers
     * @return string[]
     */
    public function exposedHeaders(string|array $headers = null): array
    {
        $current = $this->exposedHeaders;

        if ($headers == null) {
            return $current;
        }

        $this->exposedHeaders = [];
        foreach (is_array($headers) ? $headers : [$headers] as $header) {
            $this->exposedHeaders[] = $header;
        }

        return $current;
    }
}
