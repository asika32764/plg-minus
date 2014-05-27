<?php
/**
 * Part of bamahome project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Class Minus_Handler_Js
 *
 * @since 1.0
 */
class Minus_Handler_Js extends Minus_Handler_Base
{
	/**
	 * Property type.
	 *
	 * @var  string
	 */
	protected $type = 'js';

	/**
	 * Constructor.
	 *
	 * @param JDocument $doc
	 */
	public function __construct($doc)
	{
		JLoader::register('JSMin', JPATH_LIBRARIES . '/minify/JSMin.php');

		parent::__construct($doc);
	}

	/**
	 * getStorage
	 *
	 * @return  array
	 */
	protected function getStorage()
	{
		return $this->doc->_scripts;
	}

	/**
	 * addAsset
	 *
	 * @param string $path
	 * @param string $md5sum
	 *
	 * @return  void
	 */
	protected function addAsset($path, $md5sum)
	{
		// Clean assets list
		$this->doc->_scripts = array();

		$this->doc->addScriptVersion($path, $md5sum);
	}

	/**
	 * doCompress
	 *
	 * @param string $data
	 *
	 * @return  string
	 */
	protected function doCompress($data)
	{
		return JSMin::minify($data);
	}

	/**
	 * implodeData
	 *
	 * @param array $data
	 *
	 * @return  string
	 */
	protected function implodeData($data)
	{
		return implode("\n;\n", $data);
	}
}
