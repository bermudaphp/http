<?php

namespace Bermuda\HTTP;

use Bermuda\HTTP\Responder;
use Bermuda\Detector\MimeTypeDetector;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Tuupola\Middleware\CorsMiddleware;

class ConfigProvider extends \Bermuda\Config\ConfigProvider
{
    public const corsKey = 'cors';
    
    protected function getFactories(): array
    {
        return [
            CorsMiddleware::class => static fn(ContainerInterface $c) => new CorsMiddleware($c->get(self::configKey)[self::corsKey]),
            Responder::class => static function(ContainerInterface $c) {
                return new Responder($c->get(ResponseFactoryInterface::class), $c->get(MimeTypeDetector::class));
            }
        ];
    }
}
