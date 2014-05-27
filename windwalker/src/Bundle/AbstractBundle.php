<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Bundle;

use Joomla\DI\Container as JoomlaContainer;
use Joomla\DI\ContainerAwareInterface;
use Windwalker\Console\Application\Console;
use Windwalker\DI\Container;
use Windwalker\Filesystem\Path\PathLocator;

/**
 * Abstract Bundle class.
 *
 * @since 2.0
 */
class AbstractBundle implements ContainerAwareInterface
{
	/**
	 * DI Container.
	 *
	 * @var Container
	 */
	protected $container = null;

	/**
	 * Bundle name.
	 *
	 * @var  string
	 */
	protected $name = null;

	/**
	 * Get the DI container.
	 *
	 * @return  Container
	 *
	 * @since   1.0
	 *
	 * @throws  \UnexpectedValueException May be thrown if the container has not been set.
	 */
	public function getContainer()
	{
		if (!$this->container)
		{
			$this->container = Container::getInstance($this->getName());
		}

		return $this->container;
	}

	/**
	 * Set the DI container.
	 *
	 * @param   JoomlaContainer  $container  The DI container.
	 *
	 * @return  AbstractBundle Return self to support chaining.
	 *
	 * @since   1.0
	 */
	public function setContainer(JoomlaContainer $container)
	{
		$this->container = $container;

		return $this;
	}

	/**
	 * Get bundle name.
	 *
	 * @return  string  Bundle ame.
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Register providers.
	 *
	 * @param JoomlaContainer $container
	 *
	 * @return  void
	 */
	public static function registerProvider(JoomlaContainer $container)
	{
	}

	/**
	 * Register commands to console.
	 *
	 * @param Console $console Windwalker console object.
	 *
	 * @return  void
	 */
	public static function registerCommands(Console $console)
	{
		$reflection = new \ReflectionClass(get_called_class());

		$namespace = $reflection->getNamespaceName();

		$path = dirname($reflection->getFileName()) . '/Command';
		$path = new PathLocator($path);

		foreach ($path as $file)
		{
			if (!$file->isDir())
			{
				continue;
			}

			$class = $namespace . '\\Command\\' . $file->getBasename() . '\\' . $file->getBasename() . 'Command';

			if (class_exists($class) && is_subclass_of($class, 'Joomla\\Console\\Command\\Command') && $class::$isEnabled)
			{
				$console->addCommand(new $class);
			}
		}
	}
}
