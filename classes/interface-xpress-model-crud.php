<?php
/**
 * XPress Model CRUD Interface
 *
 * @package    XPress
 * @subpackage MVC
 * @author     Trasgo Furioso
 * @license    GPLv2
 * @since      0.2.0
 */

interface XPress_Model_CRUD  {
	/**
	 * Return a model instance for a specific item.
	 *
	 * @since 0.2.0
	 *
	 * @return XPress_MVC_Model instance.
	 */
	static function get( $id );

	/**
	 * Return a model instance collection filtered by the params.
	 *
	 * @since 0.2.0
	 *
	 * @return array XPress_MVC_Model instance collection.
	 */
	static function find( $params );

	/**
	 * Persists the current model instance.
	 *
	 * @since 0.2.0
	 *
	 * @return XPress_MVC_Model instance.
	 */
	public function save();

	/**
	 * Deleted the current model instance.
	 *
	 * @since 0.2.0
	 *
	 * @return XPress_MVC_Model instance.
	 */
	public function delete();
}
