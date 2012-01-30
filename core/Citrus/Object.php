<?php

namespace core\Citrus;

class Object {

	public function __construct() {
	}

	public function __toString() {
		return $this->Cast_text();
	}

	/**
	 * Cast this object to a particular type
	 *
	 * @param string $type Target type
	 * @param mixed $format Optional format parameters
	 * @return mixed
	 */
	public function Cast( $type, $format = null ) {
		$method = 'Cast_' . $type;
		return $this->$method( $format );
	}
	/**
	 * Indicate whether or not this object can be casted to the specific type
	 * @param string $type Target type
	 * @return boolean
	 */
	public function IsCastable( $type ) {
		return method_exists( $this, 'cast_' . $type );
	}

	protected function Cast_text() {
		return "[" . get_class( $this ) . "]";
	}
	protected function Cast_json() {
		return json_encode( $this );
	}

}
