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

namespace Ekino\Drupal\Debug\Tests\Unit\Action;

use Ekino\Drupal\Debug\Action\AbstractFileBackendDependantOptions;
use Ekino\Drupal\Debug\Configuration\Model\DefaultsConfiguration;
use Ekino\Drupal\Debug\Exception\NotImplementedException;
use Ekino\Drupal\Debug\Extension\Model\CustomExtensionInterface;
use Ekino\Drupal\Debug\Extension\Model\CustomModule;
use Ekino\Drupal\Debug\Extension\Model\CustomTheme;
use Ekino\Drupal\Debug\Resource\Model\CustomExtensionFileResource;
use Ekino\Drupal\Debug\Resource\Model\ResourcesCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Resource\FileExistenceResource;

class AbstractFileBackendDependantOptionsTest extends TestCase
{
    /**
     * @var string
     */
    private const CUSTOM_EXTENSIONS_DIRECTORY_PATH = __DIR__.'/fixtures/custom_extensions';

    public function testGetCacheFilePath(): void
    {
        $this->assertSame($this->getFileBackendDependantOptions(new ResourcesCollection())->getCacheFilePath(), '/foobar');
    }

    public function testGetResourcesCollection(): void
    {
        $resourcesCollection = new ResourcesCollection();

        $this->assertSame($resourcesCollection, $this->getFileBackendDependantOptions($resourcesCollection)->getResourcesCollection());
    }

    public function testGetFilteredResourcesCollectionWhenACustomExtensionFileResourceCaseIsNotImplemented(): void
    {
        $this->expectException(NotImplementedException::class);
        $this->expectExceptionMessage('The behavior for the "Ekino\Drupal\Debug\Tests\Unit\Action\NotImplementedCustomExtensionTestClass" custom extension file resource class is not implemented.');

        $this->getFileBackendDependantOptions(new ResourcesCollection(array(
            new CustomExtensionFileResource('/foo', new NotImplementedCustomExtensionTestClass()),
        )))->getFilteredResourcesCollection(array(), array());
    }

    public function testGetFilteredResources(): void
    {
        $enabledCustomModule = new CustomModule('/foo/module', 'module_1');
        $enabledCustomTheme = new CustomTheme('/bar/theme', 'theme_1');

        $expectedCustomExtensionFileResources = array(
            new CustomExtensionFileResource('/foo', $enabledCustomModule),
            new CustomExtensionFileResource('/foo', $enabledCustomTheme),
            new FileExistenceResource('/foo'),
        );

        $this->assertEquals(new ResourcesCollection($expectedCustomExtensionFileResources),
            $this->getFileBackendDependantOptions(new ResourcesCollection(\array_merge($expectedCustomExtensionFileResources, array(
                new CustomExtensionFileResource('/foo', new CustomModule('/ccc', 'wowww')),
                new CustomExtensionFileResource('/foo', new CustomTheme('/fcy/foo', 'great_theme')),
            ))))->getFilteredResourcesCollection(array(
                'module_1',
            ), array(
                'theme_1',
            ))
        );
    }

    public function testGetDefault(): void
    {
        $customModuleRootPath = \sprintf('%s/modules/module_ccc', self::CUSTOM_EXTENSIONS_DIRECTORY_PATH);
        $customModule = new CustomModule($customModuleRootPath, 'fcy');
        $customThemeRootPath = \sprintf('%s/themes/a_theme', self::CUSTOM_EXTENSIONS_DIRECTORY_PATH);
        $customTheme = new CustomTheme($customThemeRootPath, 'great_theme');

        $defaultsConfiguration = $this->createMock(DefaultsConfiguration::class);
        $defaultsConfiguration
            ->expects($this->atLeastOnce())
            ->method('getCacheDirectory')
            ->willReturn('/foo/cache');

        $this->assertEquals(new AbstractFileBackendDependantOptionsTestClass('/foo/cache/fcy.php', new ResourcesCollection(array(
            new CustomExtensionFileResource(\sprintf('%s/bar.xml', $customModuleRootPath), $customModule),
            new CustomExtensionFileResource(\sprintf('%s/src/bar/ccc_fcy.yml', $customModuleRootPath), $customModule),
            new CustomExtensionFileResource(\sprintf('%s/src/BarFcyFoo.php', $customModuleRootPath), $customModule),
            new CustomExtensionFileResource(\sprintf('%s/not_existing.file', $customModuleRootPath), $customModule),
            new CustomExtensionFileResource(\sprintf('%s/foo.php', $customThemeRootPath), $customTheme),
            new CustomExtensionFileResource(\sprintf('%s/src/great_theme_bar.yml', $customThemeRootPath), $customTheme),
            new CustomExtensionFileResource(\sprintf('%s/src/not/great_theme/existing.yml', $customThemeRootPath), $customTheme),
        ))), AbstractFileBackendDependantOptionsTestClass::getDefault(self::CUSTOM_EXTENSIONS_DIRECTORY_PATH, $defaultsConfiguration));
    }

    private function getFileBackendDependantOptions(ResourcesCollection $resourcesCollection): AbstractFileBackendDependantOptionsTestClass
    {
        return new AbstractFileBackendDependantOptionsTestClass('/foobar', $resourcesCollection);
    }
}

class AbstractFileBackendDependantOptionsTestClass extends AbstractFileBackendDependantOptions
{
    protected static function getDefaultModuleFileResourceMasks(): array
    {
        return array(
            'bar.xml',
            'src/bar/ccc_%machine_name%.yml',
            'src/Bar%camel_case_machine_name%Foo.php',
            'not_existing.file',
        );
    }

    protected static function getDefaultThemeFileResourceMasks(): array
    {
        return array(
            'foo.php',
            'src/%machine_name%_bar.yml',
            'src/not/%machine_name%/existing.yml',
        );
    }

    protected static function getDefaultCacheFileName(): string
    {
        return 'fcy.php';
    }
}

class NotImplementedCustomExtensionTestClass implements CustomExtensionInterface
{
    public function getRootPath(): string
    {
    }

    public function getMachineName(): string
    {
    }

    public function serialize(): ?string
    {
    }

    public function unserialize($serialized): void
    {
    }
}
