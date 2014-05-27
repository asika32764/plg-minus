<?php
/**
 * Part of Bamahome project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

define('PLG_SYSTEM_MINUS', __DIR__);

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
	protected $debug = false;

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
		include_once __DIR__ . '/vendor/autoload.php';

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
