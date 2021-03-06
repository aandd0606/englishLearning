<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette;

use Nette;


/**
 * Nette environment and configuration.
 * @deprecated
 */
class Environment
{
	/** environment name */
	const DEVELOPMENT = 'development',
		PRODUCTION = 'production',
		CONSOLE = 'console';

	/** @var bool */
	private static $productionMode;

	/** @var string */
	private static $createdAt;

	/** @var Nette\DI\Container */
	private static $context;


	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new StaticClassException;
	}


	/********************* environment modes ****************d*g**/


	/**
	 * Detects console (non-HTTP) mode.
	 * @return bool
	 */
	public static function isConsole()
	{
		trigger_error(__CLASS__ . ' is deprecated.', E_USER_DEPRECATED);
		return PHP_SAPI === 'cli';
	}


	/**
	 * Determines whether a server is running in production mode.
	 * @return bool
	 */
	public static function isProduction()
	{
		trigger_error(__CLASS__ . ' is deprecated.', E_USER_DEPRECATED);
		if (self::$productionMode === NULL) {
			self::$productionMode = !Nette\Configurator::detectDebugMode();
		}
		return self::$productionMode;
	}


	/**
	 * Enables or disables production mode.
	 * @param  bool
	 * @return void
	 */
	public static function setProductionMode($value = TRUE)
	{
		trigger_error(__CLASS__ . ' is deprecated.', E_USER_DEPRECATED);
		self::$productionMode = (bool) $value;
	}


	/********************* environment variables ****************d*g**/


	/**
	 * Sets the environment variable.
	 * @param  string
	 * @param  mixed
	 * @param  bool
	 * @return void
	 */
	public static function setVariable($name, $value, $expand = TRUE)
	{
		trigger_error(__CLASS__ . ' is deprecated.', E_USER_DEPRECATED);
		if ($expand && is_string($value)) {
			$value = self::getContext()->expand($value);
		}
		self::getContext()->parameters[$name] = $value;
	}


	/**
	 * Returns the value of an environment variable or $default if there is no element set.
	 * @param  string
	 * @param  mixed  default value to use if key not found
	 * @return mixed
	 * @throws InvalidStateException
	 */
	public static function getVariable($name, $default = NULL)
	{
		trigger_error(__CLASS__ . ' is deprecated.', E_USER_DEPRECATED);
		if (isset(self::getContext()->parameters[$name])) {
			return self::getContext()->parameters[$name];
		} elseif (func_num_args() > 1) {
			return $default;
		} else {
			throw new InvalidStateException("Unknown environment variable '$name'.");
		}
	}


	/**
	 * Returns the all environment variables.
	 * @return array
	 */
	public static function getVariables()
	{
		trigger_error(__CLASS__ . ' is deprecated.', E_USER_DEPRECATED);
		return self::getContext()->parameters;
	}


	/**
	 * Returns expanded variable.
	 * @param  string
	 * @return string
	 * @throws InvalidStateException
	 */
	public static function expand($s)
	{
		trigger_error(__CLASS__ . ' is deprecated.', E_USER_DEPRECATED);
		return self::getContext()->expand($s);
	}


	/********************* context ****************d*g**/


	/**
	 * Sets initial instance of context.
	 * @return void
	 */
	public static function setContext(DI\Container $context)
	{
		if (self::$createdAt) {
			throw new Nette\InvalidStateException('Configurator & SystemContainer has already been created automatically by Nette\Environment at ' . self::$createdAt);
		}
		self::$context = $context;
	}


	/**
	 * Get initial instance of context.
	 * @return Nette\DI\Container
	 */
	public static function getContext()
	{
		trigger_error(__CLASS__ . ' is deprecated.', E_USER_DEPRECATED);
		if (self::$context === NULL) {
			self::loadConfig();
		}
		return self::$context;
	}


	/**
	 * Gets the service object of the specified type.
	 * @param  string service name
	 * @return object
	 */
	public static function getService($name)
	{
		return self::getContext()->getService($name);
	}


	/**
	 * Calling to undefined static method.
	 * @param  string  method name
	 * @param  array   arguments
	 * @return object  service
	 */
	public static function __callStatic($name, $args)
	{
		if (!$args && strncasecmp($name, 'get', 3) === 0) {
			return self::getService(lcfirst(substr($name, 3)));
		} else {
			throw new MemberAccessException("Call to undefined static method Nette\\Environment::$name().");
		}
	}


	/**
	 * @return Nette\Http\Request
	 */
	public static function getHttpRequest()
	{
		return self::getContext()->getByType('Nette\Http\IRequest');
	}


	/**
	 * @return Nette\Http\Context
	 */
	public static function getHttpContext()
	{
		return self::getContext()->getByType('Nette\Http\Context');
	}


	/**
	 * @return Nette\Http\Response
	 */
	public static function getHttpResponse()
	{
		return self::getContext()->getByType('Nette\Http\IResponse');
	}


	/**
	 * @return Nette\Application\Application
	 */
	public static function getApplication()
	{
		return self::getContext()->getByType('Nette\Application\Application');
	}


	/**
	 * @return Nette\Security\User
	 */
	public static function getUser()
	{
		return self::getContext()->getByType('Nette\Security\User');
	}


	/**
	 * @return Nette\Loaders\RobotLoader
	 */
	public static function getRobotLoader()
	{
		return self::getContext()->getByType('Nette\Loaders\RobotLoader');
	}


	/********************* service factories ****************d*g**/


	/**
	 * @param  string
	 * @return Nette\Caching\Cache
	 */
	public static function getCache($namespace = '')
	{
		return new Caching\Cache(self::getContext()->getByType('Nette\Caching\IStorage'), $namespace);
	}


	/**
	 * Returns instance of session or session namespace.
	 * @param  string
	 * @return Nette\Http\Session
	 */
	public static function getSession($namespace = NULL)
	{
		return $namespace === NULL
			? self::getContext()->getByType('Nette\Http\Session')
			: self::getContext()->getByType('Nette\Http\Session')->getSection($namespace);
	}


	/********************* global configuration ****************d*g**/


	/**
	 * Loads global configuration from file and process it.
	 * @param  string
	 * @param  string
	 * @return Nette\Utils\ArrayHash
	 */
	public static function loadConfig($file = NULL, $section = NULL)
	{
		trigger_error(__CLASS__ . ' is deprecated.', E_USER_DEPRECATED);
		if (self::$createdAt) {
			throw new Nette\InvalidStateException('Nette\Configurator has already been created automatically by Nette\Environment at ' . self::$createdAt);
		} elseif (!defined('TEMP_DIR')) {
			throw new Nette\InvalidStateException('Nette\Environment requires constant TEMP_DIR with path to temporary directory.');
		}
		$configurator = new Nette\Configurator;
		$configurator
			->setDebugMode(!self::isProduction())
			->setTempDirectory(TEMP_DIR)
			->addParameters(array('container' => array('class' => 'EnvironmentContainer')));
		if ($file) {
			$configurator->addConfig($file, $section);
		}
		self::$context = $configurator->createContainer();

		self::$createdAt = '?';
		foreach (debug_backtrace(FALSE) as $row) {
			if (isset($row['file']) && $row['file'] !== __FILE__ && is_file($row['file'])) {
				self::$createdAt = "$row[file]:$row[line]";
				break;
			}
		}
		return self::getConfig();
	}


	/**
	 * Returns the global configuration.
	 * @param  string key
	 * @param  mixed  default value
	 * @return mixed
	 */
	public static function getConfig($key = NULL, $default = NULL)
	{
		trigger_error(__CLASS__ . ' is deprecated.', E_USER_DEPRECATED);
		$params = Nette\Utils\ArrayHash::from(self::getContext()->parameters);
		if (func_num_args()) {
			return isset($params[$key]) ? $params[$key] : $default;
		} else {
			return $params;
		}
	}

}
