<?php

namespace Windwalker\View\Layout;

use Windwalker\DI\Container;

/**
 * The file layout for windwalker.
 *
 * @since 2.0
 */
class FileLayout extends \JLayoutFile
{
	/**
	 * Refresh the list of include paths
	 *
	 * @return  void
	 */
	protected function refreshIncludePaths()
	{
		$app = Container::getInstance()->get('app');

		// Reset includePaths
		$this->includePaths = array();

		// (1 - lower priority) Frontend base layouts
		$this->addIncludePaths(JPATH_ROOT . '/layouts');

		// (2) Windwalker layouts.
		$this->addIncludePaths(WINDWALKER . '/Resource/layouts');

		// (3) Standard Joomla! layouts overriden
		$this->addIncludePaths(JPATH_THEMES . '/' . $app->getTemplate() . '/html/layouts');

		// Component layouts & overrides if exist
		$component = $this->options->get('component', null);

		if (!empty($component))
		{
			// (4) Component path
			if ($this->options->get('client') == 0)
			{
				$this->addIncludePaths(JPATH_SITE . '/components/' . $component . '/layouts');
			}
			else
			{
				$this->addIncludePaths(JPATH_ADMINISTRATOR . '/components/' . $component . '/layouts');
			}

			// (5) Component template overrides path
			$this->addIncludePath(JPATH_THEMES . '/' . $app->getTemplate() . '/html/layouts/' . $component);
		}

		// (6 - highest priority) Received a custom high priority path ?
		if (!is_null($this->basePath))
		{
			$this->addIncludePath(rtrim($this->basePath, DIRECTORY_SEPARATOR));
		}
	}
}
