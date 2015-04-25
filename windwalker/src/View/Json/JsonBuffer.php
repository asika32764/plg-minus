<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\View\Json;

use Windwalker\Data\Data;

/**
 * JSON Response object.
 *
 * This class serves to provide the Windwalker with a common interface to access
 * response variables for e.g. Ajax requests.
 *
 * @since 2.0
 */
class JsonBuffer extends Data
{
	/**
	 * Determines whether the request was successful
	 *
	 * @var  boolean
	 */
	public $success = true;

	/**
	 * The main response message
	 *
	 * @var  string
	 */
	public $message = null;

	/**
	 * Array of messages gathered in the JApplication object
	 *
	 * @var  array
	 */
	public $messages = null;

	/**
	 * The response data
	 *
	 * @var  mixed
	 */
	public $data = null;

	/**
	 * Constructor
	 *
	 * @param   mixed    $response        The Response data
	 * @param   string   $message         The main response message
	 * @param   boolean  $error           True, if the success flag shall be set to false, defaults to false
	 * @param   boolean  $ignoreMessages  True, if the message queue shouldn't be included, defaults to false
	 */
	public function __construct($response = null, $message = null, $error = false, $ignoreMessages = false)
	{
		$this->message = $message;

		// Get the message queue if requested and available
		$app = \JFactory::$application;

		if (!$ignoreMessages && !is_null($app) && is_callable(array($app, 'getMessageQueue')))
		{
			$messages = $app->getMessageQueue();

			// Build the sorted messages list
			if (is_array($messages) && count($messages))
			{
				foreach ($messages as $message)
				{
					if (isset($message['type']) && isset($message['message']))
					{
						$lists[$message['type']][] = $message['message'];
					}
				}
			}

			// If messages exist add them to the output
			if (isset($lists) && is_array($lists))
			{
				$this->messages = $lists;
			}
		}

		// Check if we are dealing with an error
		if ($response instanceof \Exception)
		{
			// Prepare the error response
			$this->success = false;
			$this->message = $response->getMessage();
		}
		else
		{
			// Prepare the response data
			$this->success = !$error;
			$this->data    = $response;
		}
	}

	/**
	 * Magic toString method for sending the response in JSON format
	 *
	 * @return  string  The response in JSON format
	 */
	public function __toString()
	{
		return json_encode($this);
	}
}
