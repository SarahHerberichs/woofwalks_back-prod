<?php

// namespace App;

// use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
// use Symfony\Component\HttpKernel\Kernel as BaseKernel;

// class Kernel extends BaseKernel
// {
//     use MicroKernelTrait;
    
// }
// <?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Loader\LoaderInterface;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function __construct(string $environment, bool $debug)
    {
        parent::__construct($environment, $debug);
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        $loader->load($this->getProjectDir().'/config/packages/*.yaml', 'glob');
        $loader->load($this->getProjectDir().'/config/packages/'.$this->environment.'/*.yaml', 'glob');

        if (is_file(\dirname(__DIR__).'/config/services.yaml')) {
            $loader->load($this->getProjectDir().'/config/services.yaml');
            $loader->load($this->getProjectDir().'/config/{services}_'.$this->environment.'.yaml', 'glob');
        }

        if ($this->environment === 'dev') {
            $container->register('Symfony\\Bundle\\MakerBundle\\MakerBundle');
        }
    }

    protected function configureRoutes(\Symfony\Component\Routing\RouteCollectionBuilder $routes)
    {
        $routes->import($this->getProjectDir().'/config/{routes}/'.$this->environment.'/*.yaml', '/', 'glob');
        $routes->import($this->getProjectDir().'/config/routes/*.yaml', '/', 'glob');
    }
}
