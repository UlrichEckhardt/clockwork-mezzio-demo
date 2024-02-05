<?php

declare(strict_types=1);

use Clockwork\Clockwork;
use Clockwork\DataSource\XdebugDataSource;
use Clockwork\Storage\FileStorage;
use Clockwork\Storage\StorageInterface;
use Clockwork\Support\Vanilla\Clockwork as VanillaClockwork;
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Laminas\ServiceManager\ServiceManager;

// Load configuration
$config = require __DIR__ . '/config.php';

$dependencies                       = $config['dependencies'];
$dependencies['services']['config'] = $config;
$dependencies['abstract_factories'] = [
    ReflectionBasedAbstractFactory::class
];

// register some services for Clockwork
$dependencies['factories'][StorageInterface::class] = static function (ServiceManager $creationContext, string $resolvedName, ?array $options) {
    return new FileStorage(__DIR__ . '/../
    ');
};
$dependencies['factories'][VanillaClockwork::class] = static function (ServiceManager $creationContext, string $resolvedName, ?array $options) {
    $res = new class extends VanillaClockwork
    {
        // TODO: All these methods here need to be integrated into the
        // upstream VanillaClockwork.
        public function isEnabled()
        {
            return $this->config['enable'];
        }

        public function getApiPath()
        {
            return $this->config['api'];
        }

        public function isWebEnabled()
        {
            return $this->config['web']['enable'];
        }

        public function getWebPath()
        {
            // TODO: Clarify what the meaning here is.
            // return $this->config['web']['uri'];
            return '/web';
        }

        public function isAuthenticationEnabled()
        {
            return $this->config['authentication'];
        }
    };
    $res->getClockwork()->storage($creationContext->get(StorageInterface::class));
    $res->getClockwork()->addDataSource($creationContext->get(XdebugDataSource::class));
    return $res;
};
$dependencies['factories'][Clockwork::class] = static function (ServiceManager $creationContext, string $resolvedName, ?array $options) {
    // just retrieve it from the helper, which unfortunately doesn't allow us to inject it
    return $creationContext->get(VanillaClockwork::class)->getClockwork();
};

// Build container
return new ServiceManager($dependencies);
