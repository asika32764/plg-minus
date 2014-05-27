<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Html;

/**
 * Html Elements collection.
 *
 * @since 2.0
 */
class HtmlElements extends \ArrayObject
{
	/**
	 * Convert all elements to string.
	 *
	 * @return  string
	 */
	public function __toString()
	{
		$return = '';

		foreach ($this as $element)
		{
			$return .= (string) $element;
		}

		return $return;
	}
}
