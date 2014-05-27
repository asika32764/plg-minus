<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Data;

/**
 * Interface DataInterface
 */
interface DatasetInterface
{
	/**
	 * Bind an array contains multiple data into this object.
	 *
	 * @param   array    $dataset      The data array or object.
	 *
	 * @return  Data Return self to support chaining.
	 */
	public function bind($dataset);

	/**
	 * Is this data set empty?
	 *
	 * @return  boolean Tru if empty.
	 */
	public function isNull();
}
