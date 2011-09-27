<?php

/**
 * Library Library
 *
 * The Library library is designed to only allow one instance of any library that extends it to exist per application or
 * module. Adapted from the Eventing project <https://github.com/lesshub/eventing>.
 *
 * @category	Eventing
 * @package		Libraries
 * @subpackage	Library
 */

	/**
	 * Library Base Class
	 *
	 * Implementation fo the Singleton design pattern. Instances of classes can be returned with the getInstance()
	 * method, whilst new instances are forbidden.
	 */
	abstract class library {

		/**
		 * Constructor Function
		 *
		 * Every library class must have a constructor function that is defined
		 * using the protected access availability.
		 *
		 * @abstract
		 * @access protected
		 */
		abstract protected function __construct();

		/**
		 * Disallow Cloning
		 *
		 * No point using the singleton pattern if the object can be cloned into a new instance.
		 *
		 * @access public
		 * @return E_USER_ERROR
		 */
		final public function __clone() {
			trigger_error('Cannot clone singleton library.', E_USER_ERROR);
		}

		/**
		 * Get Instance
		 *
		 * @final
		 * @static
		 * @access public
		 * @return object|false
		 */
		final public static function &getInstance() {
			static $objects = array();
			$class = get_called_class();
			if(isset($objects[$class]) && is_object($objects[$class])) {
				return $objects[$class];
			}
			if(!class_exists($class)) {
				return false;
			}
			$objects[$class] = isset($class::$_instance) && is_object($class::$_instance)
				? $class::$_instance
				: new $class;
			return $objects[$class];
		}

	}