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

namespace Ekino\Drupal\Debug\Tests\Unit\Action\DisplayDumpLocation;

use Ekino\Drupal\Debug\Action\DisplayDumpLocation\DisplayDumpLocationAction;
use Ekino\Drupal\Debug\Action\DisplayDumpLocation\SourceContextProvider as BackPortedSourceContextProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\VarDumper\Dumper\ContextProvider\SourceContextProvider;
use Symfony\Component\VarDumper\VarDumper;

class DisplayDumpLocationActionTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(array(
            'ekino.drupal.debug.debug_kernel.on_kernel_instantiation' => 'process',
        ), DisplayDumpLocationAction::getSubscribedEvents());
    }

    public function testProcess(): void
    {
        VarDumper::setHandler(null);
        (new DisplayDumpLocationAction())->process();
        $this->assertAttributeInstanceOf(\Closure::class, 'handler', VarDumper::class);
    }

    public function testGetSourceContextProvider(): void
    {
        $expectedSourceContextProviderClass = (!\class_exists(SourceContextProvider::class)) ?
            BackPortedSourceContextProvider::class :
            SourceContextProvider::class;

        $displayDumpLocationAction = new DisplayDumpLocationAction();
        $displayDumpLocationActionReflector = new \ReflectionClass(DisplayDumpLocationAction::class);

        $getSourceContextProviderMethod = $displayDumpLocationActionReflector->getMethod('getSourceContextProvider');
        $getSourceContextProviderMethod->setAccessible( true );

        $sourceContextProvider = $getSourceContextProviderMethod->invoke($displayDumpLocationAction);

        $this->assertInstanceOf($expectedSourceContextProviderClass, $sourceContextProvider);
    }
}
