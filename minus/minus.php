<?php
/**
 * Part of Bamahome project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Class PlgSystemMinus
 *
 * @since 1.0
 */
class PlgSystemMinus extends JPlugin
{
	/**
	 * Property debug.
	 *
	 * @var  bool
	 */
	protected $debug = true;

	/**
	 * onBeforeRender
	 *
	 * @return  bool
	 */
	public function onBeforeCompileHead()
	{
		$app = JFactory::getApplication();

		if ($app->isAdmin())
		{
			return true;
		}

		jimport('joomla.filesystem.file');
		JLoader::registerNamespace('Minus', __DIR__);
		JLoader::registerNamespace('Minify', JPATH_LIBRARIES . '/minify');

		$doc = JFactory::getDocument();

		if ($this->debug)
		{
			return true;
		}

		$css = new Minus_Handler_Css($doc);
		$js  = new Minus_Handler_Js($doc);

		$css->compress();
		$js->compress();

		return true;
	}
}
