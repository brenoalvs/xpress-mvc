<?php
/**
 * XPress MVC Model Class
 *
 * @package    XPress
 * @subpackage MVC
 * @author     Trasgo Furioso
 * @license    GPLv2
 * @since      0.2.0
 */

abstract class XPress_MVC_Model implements XPress_Model_CRUD {
	/**
	 * Model schema. MUST define in subclass.
	 *
	 * @since 0.2.0
	 * @var array
	 */
	static protected $schema;

	/**
	 * Model attributes.
	 *
	 * @since 0.2.0
	 * @var array
	 */
	protected $attributes;

	/**
	 * Model valid.
	 *
	 * @since 0.2.0
	 * @var boolean
	 */
	protected $is_valid;

	/**
	 * Model errors.
	 *
	 * @since 0.2.0
	 * @var array
	 */
	protected $errors = array();

	/**
	 * Modified attribute keys.
	 *
	 * @since 0.3.0
	 * @var array
	 */
	protected $modified_attributes_keys = array();

	/**
	 * Class constructor. Reads schema and sets attributes.
	 *
	 * @since 0.2.0
	 *
	 * @return XPress_MVC_Model instance.
	 */
	public function __construct( $attributes = array() ) {
		if ( ! empty( $attributes ) ) {
			$this->update( $attributes );
		}
	}

	/**
	 * Returns a new model instance.
	 *
	 * @since 0.2.0
	 *
	 * @return XPress_MVC_Model instance.
	 */
	static function new( $attributes = array() ) {
		return new static( $attributes );
	}

	/**
	 * Returns true if attribute is present in valid_attributes
	 *
	 * @param string $attribute Attribute slug.
	 *
	 * @since 0.2.0
	 *
	 * @return boolean
	 */
	public function __isset( $attribute ) {
		return array_key_exists( $attribute, static::$schema );
	}

	/**
	 * Sets attribute value.
	 *
	 * @param string $attribute Attribute slug.
	 *
	 * @param mixed $value Attribute value.
	 *
	 * @since 0.2.0
	 *
	 * @return null
	 */
	public function __set( $attribute, $value ) {
		if ( $this->__isset( $attribute ) ) {
			$this->attributes[ $attribute ] = $value;
			$this->modified_attributes_keys[] = $attribute;
		} else {
			throw new XPressInvalidModelAttributeException( $attribute );
		}
	}

	/**
	 * Gets attribute value.
	 *
	 * @param string $attribute Attribute slug.
	 *
	 * @since 0.2.0
	 *
	 * @return mixed Attribute value.
	 */
	public function __get( $attribute ) {
		if ( $this->__isset( $attribute ) ) {
			return isset( $this->attributes[ $attribute ] ) ? $this->attributes[ $attribute ] : null;
		} else {
			throw new XPressInvalidModelAttributeException( $attribute );
		}
	}

	/**
	 * Updates attributes value.
	 *
	 * @param array $attributes Attributes to update.
	 *
	 * @since 0.2.0
	 *
	 * @return boolean
	 */
	public function update( $attributes ) {
		$invalid_attributes = array();
		foreach ( $attributes as $attribute => $value ) {
			if ( ! $this->__isset( $attribute ) ) {
				$invalid_attributes[] = $attribute;
			}
		}
		if ( empty( $invalid_attributes ) ) {
			$this->attributes = $attributes;
			return true;
		} else {
			throw new XPressInvalidModelAttributeException( join( ', ', $invalid_attributes ) );
		}
	}

	/**
	 * Returns the model schema
	 *
	 * @since 0.2.0
	 *
	 * @return array Model schema
	 */
	static function get_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => str_replace( '_', '-', sanitize_title_with_dashes( static::class ) ),
			'type'       => 'object',
			'properties' => static::$schema,
		);
		return $schema;
	}

	/**
	 * Validates model against schema
	 *
	 * @since 0.2.0
	 *
	 * @return boolean
	 */
	public function is_valid() {
		$this->errors = array();
		foreach ( static::get_schema()['properties'] as $attribute => $definition ) {
			// Validate required fields
			if ( isset( $definition['required'] ) && true === $definition['required'] && empty( $this->attributes[ $attribute ] ) ) {
				$this->errors[ $attribute ] = sprintf( __( '%s is required.' ), $definition['description'] );
			} else {
				// If required is met then perform other validations
				if ( is_array( $this->attributes ) && array_key_exists( $attribute, $this->attributes ) ) {
					$value = $this->attributes[ $attribute ];
					$field_name = $definition['description'];
					$param_valid = rest_validate_value_from_schema( $value, $definition, $field_name );
					if ( is_wp_error( $param_valid ) ) {
						$this->errors[ $attribute ] = $param_valid->errors['rest_invalid_param'][0];
					} elseif ( isset( $definition['validate_callback'] ) ) {
						$param_valid = call_user_func( $definition['validate_callback'], $value );
						if ( is_wp_error( $param_valid ) ) {
							// XPress MVC will pick the error text from the WP_Error 'xpress_mvc_invalid_param' error key.
							$this->errors[ $attribute ] = $param_valid->errors['xpress_mvc_invalid_param'][0];
						}
					}
				}
			}
		}
		if ( empty( $this->errors ) ) {
			$this->is_valid = true;
		} else {
			$this->is_valid = false;
		}
		return $this->is_valid;
	}

	/**
	 * Returns the model errors
	 *
	 * @since 0.2.0
	 *
	 * @return array Model errors
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * Returns modified attribute keys.
	 *
	 * @since 0.3.0
	 *
	 * @return array Modified attributes.
	 */
	public function modified_attributes() {
		return array_filter( $this->attributes, function( $key ) {
			return in_array( $key, $this->modified_attributes_keys );
		}, ARRAY_FILTER_USE_KEY );
	}
} // XPress_MVC_Model
