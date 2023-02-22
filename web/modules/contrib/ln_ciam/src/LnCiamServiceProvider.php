<?php

namespace Drupal\ln_ciam;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\ln_ciam\Utility\LinkGenerator;

class LnCiamServiceProvider extends ServiceProviderBase{
  public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('link_generator');
    $definition->setClass(LinkGenerator::class);
  }
}
