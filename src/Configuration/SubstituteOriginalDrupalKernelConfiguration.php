<?php

declare(strict_types=1);

/*
 * This file is part of the ekino Drupal Debug project.
 *
 * (c) ekino
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekino\Drupal\Debug\Configuration;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class SubstituteOriginalDrupalKernelConfiguration implements ConfigurationInterface
{
    /**
     * @var string
     */
    public const ROOT_KEY = 'substitute_original_drupal_kernel';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->root(self::ROOT_KEY);

        $rootNode
            ->info("It is recommended to disable the original DrupalKernel substitution to run your tests.\nTo programmatically toggle it, use the two dedicated composer commands.")
            ->canBeDisabled()
            ->children()
                ->scalarNode('composer_autoload_file_path')
                    ->cannotBeEmpty()
                    ->defaultValue('vendor/autoload.php')
                ->end()
                ->scalarNode('cache_directory_path')
                    ->info('If not specified, it fall backs to the default cache directory path.')
                    ->defaultNull()
                ->end()
          ->end();

        return $treeBuilder;
    }
}