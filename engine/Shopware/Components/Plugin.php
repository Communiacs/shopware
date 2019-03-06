<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Components;

use Enlight\Event\SubscriberInterface;
use Shopware\Components\Console\Application;
use Shopware\Components\Filesystem\PrefixFilesystem;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

abstract class Plugin implements ContainerAwareInterface, SubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $path;

    /**
     * @var bool
     */
    private $isActive;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @param bool   $isActive
     * @param string $namespace
     */
    final public function __construct($isActive, $namespace)
    {
        $this->isActive = (bool) $isActive;
        $this->namespace = $namespace;
    }

    /**
     * @return string
     */
    final public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [];
    }

    /**
     * @return bool
     */
    final public function isActive()
    {
        return $this->isActive;
    }

    /**
     * Registers Commands.
     *
     * @param Application $application An Application instance
     *
     * @deprecated since version 5.5, to be removed in 5.7 - Use console.command tag instead
     */
    public function registerCommands(Application $application)
    {
    }

    /**
     * This method can be overridden
     *
     * @param InstallContext $context
     */
    public function install(InstallContext $context)
    {
    }

    /**
     * This method can be overridden
     *
     * @param UpdateContext $context
     */
    public function update(UpdateContext $context)
    {
        $context->scheduleClearCache(InstallContext::CACHE_LIST_DEFAULT);
    }

    /**
     * This method can be overridden
     *
     * @param ActivateContext $context
     */
    public function activate(ActivateContext $context)
    {
        $context->scheduleClearCache(InstallContext::CACHE_LIST_DEFAULT);
    }

    /**
     * This method can be overridden
     *
     * @param DeactivateContext $context
     */
    public function deactivate(DeactivateContext $context)
    {
        $context->scheduleClearCache(InstallContext::CACHE_LIST_DEFAULT);
    }

    /**
     * This method can be overridden
     *
     * @param UninstallContext $context
     */
    public function uninstall(UninstallContext $context)
    {
        $context->scheduleClearCache(InstallContext::CACHE_LIST_DEFAULT);
    }

    /**
     * Builds the Plugin.
     *
     * It is only ever called once when the cache is empty.
     *
     * This method can be overridden to register compilation passes,
     * other extensions, ...
     *
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function build(ContainerBuilder $container)
    {
        $container->setParameter($this->getContainerPrefix() . '.plugin_dir', $this->getPath());
        $container->setParameter($this->getContainerPrefix() . '.plugin_name', $this->getName());
        $this->registerFilesystems($container);
        $this->loadFiles($container);
    }

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    final public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Returns the Plugin name (the class short name).
     *
     * @return string The Plugin name
     */
    final public function getName()
    {
        if ($this->name !== null) {
            return $this->name;
        }

        $name = get_class($this);
        $pos = strrpos($name, '\\');

        return $this->name = $pos === false ? $name : substr($name, $pos + 1);
    }

    /**
     * @return string
     */
    public function getContainerPrefix()
    {
        return $this->camelCaseToUnderscore($this->getName());
    }

    /**
     * Gets the Plugin directory path.
     *
     * @return string The Plugin absolute path
     */
    final public function getPath()
    {
        if ($this->path === null) {
            $reflected = new \ReflectionObject($this);
            $this->path = dirname($reflected->getFileName());
        }

        return $this->path;
    }

    /**
     * @param ContainerBuilder $container
     */
    final protected function loadFiles(ContainerBuilder $container)
    {
        if (!is_file($this->getPath() . '/Resources/services.xml')) {
            return;
        }

        $loader = new XmlFileLoader(
            $container,
            new FileLocator()
        );

        $loader->load($this->getPath() . '/Resources/services.xml');
    }

    /**
     * @param string $string
     *
     * @return string
     */
    private function camelCaseToUnderscore($string)
    {
        return strtolower(ltrim(preg_replace('/[A-Z]/', '_$0', $string), '_'));
    }

    /**
     * @param ContainerBuilder $container
     */
    private function registerFilesystems(ContainerBuilder $container)
    {
        $this->registerFilesystem($container, 'private');
        $this->registerFilesystem($container, 'public');
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $key
     */
    private function registerFilesystem(ContainerBuilder $container, $key)
    {
        $parameterKey = sprintf('shopware.filesystem.%s', $key);
        $serviceId = sprintf('%s.filesystem.%s', $this->getContainerPrefix(), $key);

        $filesystem = new Definition(
            PrefixFilesystem::class,
            [
                new Reference($parameterKey),
                'plugins/' . $this->getContainerPrefix(),
            ]
        );

        $container->setDefinition($serviceId, $filesystem);
    }
}
