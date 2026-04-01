<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\LaravelCas\Config;

use EcPhp\CasLib\Configuration\Properties as CasProperties;
use EcPhp\CasLib\Contract\Configuration\PropertiesInterface;
use const FILTER_VALIDATE_URL;
use Illuminate\Routing\Router as RouterInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class Laravel implements PropertiesInterface
{
    private PropertiesInterface $properties;

    public function __construct(
        ParameterBag $properties,
        private readonly RouterInterface $router
    ) {
        $this->properties = new CasProperties(
            $this->routeToUrl(
                $properties->all()
            )
        );
    }

    public function jsonSerialize(): array
    {
        return $this->properties->jsonSerialize();
    }

    /**
     * Transform Symfony routes into absolute URLs.
     *
     * @param array<string, mixed> $properties
     *                                         The properties.
     *
     * @return array<string, mixed>
     *                              The updated properties.
     */
    private function routeToUrl(array $properties): array
    {
        $properties = $this->updateDefaultParameterRouteToUrl(
            $properties,
            'pgtUrl'
        );

        return $this->updateDefaultParameterRouteToUrl(
            $properties,
            'service'
        );
    }

    /**
     * @param array<string, mixed> $properties
     *
     * @return array<string, mixed>
     */
    private function updateDefaultParameterRouteToUrl(array $properties, string $key): array
    {
        foreach ($properties['protocol'] as $protocolKey => $protocol) {
            if (false === isset($protocol['default_parameters'][$key])) {
                continue;
            }

            $route = $protocol['default_parameters'][$key];

            if (false === filter_var($route, FILTER_VALIDATE_URL)) {
                $route = $this
                    ->router
                    ->generate(
                        $route,
                        [],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );

                $properties['protocol'][$protocolKey]['default_parameters'][$key] = $route;
            }
        }

        return $properties;
    }
}
