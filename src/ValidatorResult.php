<?php namespace PHPGoodies;

/**
 * ValidatorResult
 * 
 * Structured resultset for the Validator.
 *
 * @package PHPGoodies
 */
class ValidatorResult {
    /**
     * Success
     *
     * @var boolean
     */
    protected $_success;

    /**
     * Fields
     *
     * @var array
     */
    protected $_fields;

    /**
     * Default constructor
     *
     * @param  $success - (Optional)
     * @param  $fields  - (Optional)
     * @return void
     */
    public function __construct($success = false, $fields = array()) {
        $this->_success = $success;
        $this->_fields  = $fields;
    }

    /**
     * Is success?
     *
     * @param  bool|null $val - (Optional)
     * @return bool
     */
    public function success($val = null) {
        if (!is_null($val)) {
            $this->_success = (bool) $val;
        }
        
        return $this->_success;
    }

    /**
     * Get/Set fields.
     *
     * @param  array|null $val - (Optional)
     * @return bool
     */
    public function fields(array $val = null) {
        if (!is_null($val)) {
            $this->_fields = $val;
        }
        
        return $this->_fields;
    }

    /**
     * Convert to a friendly array.
     *
     * @return array
     */
    public function toArray() {
        return array(
            'success' => $this->success(),
            'fields'  => $this->fields()
        );
    }
}
