<?php
/**
 * Part of minus project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Class Minus_Handler_Css
 *
 * @since 1.0
 */
class Minus_Handler_Css extends Minus_Handler_Base
{
	/**
	 * Property type.
	 *
	 * @var  string
	 */
	protected $type = 'css';

	/**
	 * Constructor.
	 *
	 * @param JDocument $doc
	 */
	public function __construct($doc)
	{
		JLoader::register('CSSmin', PLG_SYSTEM_MINUS . '/src/Cssmin.php');

		parent::__construct($doc);
	}

	/**
	 * getStorage
	 *
	 * @return  array
	 */
	protected function getStorage()
	{
		return $this->doc->_styleSheets;
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
		$this->doc->_styleSheets = array();

		$this->doc->addStyleSheetVersion($path, $md5sum);
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
		return Minify_CSS_Compressor::process($data);
	}

	/**
	 * handleCssFile
	 *
	 * @param string $file
	 * @param string $url
	 *
	 * @return  string
	 */
	protected function handleFile($file, $url)
	{
		$that = $this;

		$path = dirname($url);
		$path = str_replace(trim(JUri::root(), '/'), JPATH_ROOT, $path);

		// Rewrite Url
		$newFile = Minify_CSS_UriRewriter::rewrite(
			$file,
			$path,
			$_SERVER['DOCUMENT_ROOT']
		);

		// Handle Imports
		$newFile = preg_replace_callback(
			'/@import\\s*url\(([^)]+)\)/',
			function ($matches) use ($that)
			{
				return $that->prepareAssetData(trim($matches[1], "\"'"), 'css');
			},
			$newFile
		);

		return $newFile;
	}
}
