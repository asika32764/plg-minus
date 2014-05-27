<?php
/**
 * Part of minus project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Class Minus_Handler_Base
 *
 * @since 1.0
 */
abstract class Minus_Handler_Base
{
	/**
	 * Property doc.
	 *
	 * @var  JDocument
	 */
	protected $doc = null;

	/**
	 * Property type.
	 *
	 * @var  string
	 */
	protected $type = '';

	/**
	 * Constructor.
	 *
	 * @param JDocument $doc
	 */
	public function __construct($doc)
	{
		$this->doc = $doc;
	}

	/**
	 * compress
	 *
	 * @return  void
	 */
	public function compress()
	{
		// Get assets list from JDocument
		$list = $this->getStorage();

		// Build assets hash per page.
		$name = $this->buildHash(array_keys($list));

		// Cache file path.
		$path = $this->getCachePath($name);

		$assetPath = JPATH_ROOT . '/' . $path;
		$sumPath   = dirname(JPATH_ROOT . '/' . $path) . '/' . $this->type . '.MD5SUM';

		// Prepare to minify and combine files.
		if (!is_file($assetPath))
		{
			// Combine data by file list.
			$data = $this->combineData(array_keys($list));

			$data = $this->doCompress($data);

			// Write data into file and SUM file
			JFile::write($assetPath, $data);

			$sum = md5($data);
			JFile::write($sumPath, $sum);
		}

		// Get MD5SUM
		$md5sum = is_file($sumPath) ? file_get_contents($sumPath) : md5(uniqid());

		// Add file to JDocument
		$this->addAsset(JUri::root(true) . '/' . $path, $md5sum);
	}

	/**
	 * doCompress
	 *
	 * @param string $data
	 *
	 * @return  string
	 */
	abstract protected function doCompress($data);

	/**
	 * getStorage
	 *
	 * @return  mixed
	 */
	abstract protected function getStorage();

	/**
	 * addAsset
	 *
	 * @param string $path
	 * @param string $md5sum
	 *
	 * @return  mixed
	 */
	abstract protected function addAsset($path, $md5sum);


	/**
	 * buildHash
	 *
	 * @param array  $list
	 *
	 * @return  string
	 */
	protected function buildHash($list)
	{
		$hash = '';

		foreach ($list as $name)
		{
			$hash .= $name;
		}

		return md5($hash) . '.' . $this->type;
	}

	/**
	 * getCachePath
	 *
	 * @param string $hash
	 *
	 * @return  string
	 */
	protected function getCachePath($hash)
	{
		return 'cache/assets/' . $hash;
	}

	/**
	 * combineData
	 *
	 * @param array  $list
	 *
	 * @return  string
	 */
	protected function combineData($list)
	{
		$data = array();

		foreach ($list as $url)
		{
			// Wired bug...
			if (strpos($url, 'modals/js/script') !== false)
			{
				continue;
			}

			$data[] = $this->prepareAssetData($url);
		}

		return $this->implodeData($data);
	}

	/**
	 * prepareAssetData
	 *
	 * @param string $url
	 *
	 * @return  string
	 */
	public function prepareAssetData($url)
	{
		// Convert url to relative path
		$file = $this->regularizeUrl($url);

		// Init Http
		if (IS_WIN)
		{
			$file = str_replace('localhost', '127.0.0.1', $file);
		}

		$http = JHttpFactory::getHttp(new JRegistry, 'curl');

		$content = $http->get($file)->body;

		// Using handle method to prepare file
		$content = $this->handleFile($content, $file);

		return "\n\n/* File: {$url} */\n\n" . $content;
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
		return implode("\n\n", $data);
	}

	/**
	 * regularizeUrl
	 *
	 * @param string $file
	 *
	 * @return  string
	 */
	protected function regularizeUrl($file)
	{
		// Convert url to local path.
		if (substr($file, 0, 1) == '/')
		{
			$subfolderLength = strlen(JUri::root(true)) + 1;

			$file = substr($file, $subfolderLength);

			$file = JUri::root() . trim($file, '/');
		}

		// Absolute path.
		elseif (substr($file, 0, 4) != 'http')
		{
			$file = JUri::root() . trim($file, '/');
		}

		return $file;
	}

	/**
	 * handleFile
	 *
	 * @param string $file
	 * @param string $url
	 *
	 * @return  string
	 */
	protected function handleFile($file, $url)
	{
		return $file;
	}
}
