<?php

/**
 * Downgrade sustainabil-it/phpstan-higher-order-collections to PHP 7.1
 */

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::SETS, [
        SetList::DOWNGRADE_PHP74,
        SetList::DOWNGRADE_PHP73,
        SetList::DOWNGRADE_PHP72,
    ]);
};
