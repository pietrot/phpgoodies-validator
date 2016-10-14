<?php namespace PHPGoodies;

use PHPGoodies\ValidatorResult;
use Exception;

/**
 * Validator.
 *
 * Helper class that allows you to easily validate data.
 *
 * @package PHPGoodies
 */
class Validator {
    /**
     * Data.
     *
     * @var array
     */
    protected $_data;
    
    /**
     * Ruleset.
     *
     * @var array
     */
    protected $_ruleset;

    /**
     * Errors.
     *
     * @var array
     */
    protected $_errors;

    /**
     * Default constructor.
     *
     * @param  array $data
     * @return void
     */
    public function __construct($data = array()) {
        $this->_data    = $data;
        $this->_ruleset = array();
        $this->_errors  = array();
    }
    
    /**
     * Validator factory method.
     *
     * @param  array $data
     * @return PHPGoodies\Validator
     */
    public static function validate(array $data) {
        return new self($data);
    }

    /**
     * Add rule.
     *
     * @param  string $field
     * @param  string $rule
     * @param  array  $messages - (Optional) Custom messages.
     * @return PHPGoodies\Validator
     */
    public function verify($field, $rule, array $messages = array()) {
        if (empty($field) === true || empty($rule) === true) {
            throw new Exception('Missing field/rule!');
        }

        $this->_ruleset[] = array(
            'field'    => $field,
            'rule'     => $rule,
            'messages' => $messages);

        return $this;
    }

    /**
     * Run validator.
     *
     * @return PHPGoodies\ValidatorResult
     */
    public function run() {
        // Prep result.
        $result = new ValidatorResult();

        // Return if no rules are provided.
        if (empty($this->_ruleset) === true) {
            $result->success(true);
            return $result;
        }
        
        // Review rules.
        foreach ($this->_ruleset as $item) {
            $field = $item['field'];
            $rules = explode('|', $item['rule']);

            foreach ($rules as $rule_definition) {
                $args    = explode(':', $rule_definition);
                $rule    = array_shift($args);
                $message = '';
                
                // Skip if an error has already been found for the given field.
                if (isset($this->_errors[$field])) {
                    continue;
                }
                
                if (array_key_exists($rule, $item['messages'])) {
                    $message = $item['messages'][$rule];
                }
                
                $this->_verifyRule($field, $rule, $args, $message);
            }
        }
        
        // Any errors?
        if (empty($this->_errors) === false) {
            // If so,
            $result->success(false);
            $result->fields($this->_errors);
        } else {
            // Otherwise,
            $result->success(true);
        }

        return $result;
    }

    /**
     * Verify rule
     *
     * @param  string $field
     * @param  string $rule
     * @param  array  $args    - (Optional)
     * @param  string $message - (Optional)
     * @return void
     */
    protected function _verifyRule($field, $rule, array $args = array(), $message = '') {
        // Map rule to verify func.
        $func_name = preg_replace('/[-_]/i', ' ', $rule);
        $func_name = ucwords($func_name);
        $func_name = preg_replace('/\s/i', '', $func_name);
        $func_name = "_verify{$func_name}";   
        
        // Call func. 
        call_user_func_array(array($this, $func_name), array($field, $rule, $args, $message));
    }

    /**
     * Verify if the field value satisfies the constraint "required".
     *
     * @param  string $field
     * @param  string $rule
     * @param  array  $args    - (Optional)
     * @param  string $message - (Optional)
     * @return void
     */
    protected function _verifyRequired($field, $rule, array $args = array(), $message = '') {
        if ($this->_hasValue($field) === false || trim($this->_data[$field]) === '') {
            if ($message != '') {
                $this->_errors[$field] = $message;
            } else {
                $this->_errors[$field] = 'Field is required!';
            }
        }
    }

    /**
     * Verify if the field value (if provided) satisfies the constraint "equal" some value (defined in $args).
     *
     * @param  string $field
     * @param  string $rule
     * @param  array  $args    - (Optional)
     * @param  string $message - (Optional)
     * @return void
     */
    protected function _verifyEqual($field, $rule, array $args = array(), $message = '') {
        // Return if the value for the field is not provided.
        if ($this->_hasValue($field) === false) {
            return;
        }
        
        if (count($args) !== 1) {
            throw new Exception('Missing argument to validate against!');
        }

        if ($this->_data[$field] != $args[0]) {
            if ($message != '') {
                $this->_errors[$field] = $message;
            } else {
                $this->_errors[$field] = "Value must be equal to {$args[0]}!";
            }
        }
    }
    
    /**
     * Verify if the field value (if provided) satisfies the constraint "min".
     *
     * @param  string $field
     * @param  string $rule
     * @param  array  $args    - (Optional)
     * @param  string $message - (Optional)
     * @return void
     */
    protected function _verifyMin($field, $rule, array $args = array(), $message = '') {
        // Return if the value for the field is not provided.
        if ($this->_hasValue($field) === false) {
            return;
        }
        
        if (count($args) !== 1) {
            throw new Exception('Missing argument to validate against!');
        }

        if ($this->_data[$field] < $args[0]) {
            if ($message != '') {
                $this->_errors[$field] = $message;
            } else {
                $this->_errors[$field] = "Value is less than {$args[0]}!";
            }
        }
    }
    
    /**
     * Verify if the field value (if provided) satisfies the constraint "max".
     *
     * @param  string $field
     * @param  string $rule
     * @param  array  $args    - (Optional)
     * @param  string $message - (Optional)
     * @return void
     */
    protected function _verifyMax($field, $rule, array $args = array(), $message = '') {
        // Return if the value for the field is not provided.
        if ($this->_hasValue($field) === false) {
            return;
        }
        
        if (count($args) !== 1) {
            throw new Exception('Missing argument to validate against!');
        }
        
        if ($this->_data[$field] > $args[0]) {
            if ($message != '') {
                $this->_errors[$field] = $message;
            } else {
                $this->_errors[$field] = "Value is greater than {$args[0]}!";
            }
        }
    }
    
    /**
     * Verify if the field value (if provided) satisfies the constraint "alpha".
     *
     * @param  string $field
     * @param  string $rule
     * @param  array  $args    - (Optional)
     * @param  string $message - (Optional)
     * @return void
     */
    protected function _verifyAlpha($field, $rule, array $args = array(), $message = '') {
        // Return if the value for the field is not provided.
        if ($this->_hasValue($field) === false) {
            return;
        }
        
        if (!self::isAlphabetic($this->_data[$field])) {
            if ($message != '') {
                $this->_errors[$field] = $message;
            } else {
                $this->_errors[$field] = 'Value must contain only alphabetic characters!';
            }
        }
    }
    
    /**
     * Verify if the field value (if provided) satisfies the constraint "alpha-num".
     *
     * @param  string $field
     * @param  string $rule
     * @param  array  $args    - (Optional)
     * @param  string $message - (Optional)
     * @return void
     */
    protected function _verifyAlphaNum($field, $rule, array $args = array(), $message = '') {
        // Return if the value for the field is not provided.
        if ($this->_hasValue($field) === false) {
            return;
        }
        
        if (!self::isAlphaNumeric($this->_data[$field])) {
            if ($message != '') {
                $this->_errors[$field] = $message;
            } else {
                $this->_errors[$field] = 'Value must contain only alphanumeric characters!';
            }
        }
    }
    
    /**
     * Verify if the field value (if provided) satisfies the constraint "int".
     *
     * @param  string $field
     * @param  string $rule
     * @param  array  $args    - (Optional)
     * @param  string $message - (Optional)
     * @return void
     */
    protected function _verifyInt($field, $rule, array $args = array(), $message = '') {
        // Return if the value for the field is not provided.
        if ($this->_hasValue($field) === false) {
            return;
        }
        
        if (!self::isInteger($this->_data[$field])) {
            if ($message != '') {
                $this->_errors[$field] = $message;
            } else {
                $this->_errors[$field] = 'Value must be an integer!';
            }
        }
    }
    
    /**
     * Verify if the field value (if provided) satisfies the constraint "decimal".
     *
     * @param  string $field
     * @param  string $rule
     * @param  array  $args    - (Optional)
     * @param  string $message - (Optional)
     * @return void
     */
    protected function _verifyDecimal($field, $rule, array $args = array(), $message = '') {
        // Return if the value for the field is not provided.
        if ($this->_hasValue($field) === false) {
            return;
        }
        
        $precision = (isset($args[0])) ? $args[0] : null; 
        $scale     = (isset($args[1])) ? $args[1] : null; 
        
        if (!self::isDecimal($this->_data[$field], $precision, $scale)) {
            if ($message != '') {
                $this->_errors[$field] = $message;
            } else {
                $this->_errors[$field] = 'Value must be a decimal!';
            }
        }
    }

    /**
     * Does the input only contain alphabetic chars?
     *
     * @param  mixed $input
     * @return bool
     */
    public static function isAlphabetic($input) {
        return (!preg_match("/^([a-z])+$/i", $input)) ? false : true;
    }
    
    /**
     * Does the input only contain alphnumeric chars?
     *
     * @param  mixed  $input
     * @param  string $symbols - (Optional) Allowed symbols.
     * @return bool
     */
    public static function isAlphaNumeric($input, $symbols = '') {
        return (!preg_match("/^([a-z0-9".$addition_symbols."])+$/i", $input)) ? false : true;
    }
        
    /**
     * Is the input an integer?
     *
     * @param  mixed $input
     * @return bool
     */
    public static function isInteger($input) {
        return is_int($input);
    }
    
    /**
     * Is the input a decimal?
     *
     * @param  mixed  $input
     * @param  int    $precision - (Optional) Refers to the maximum number of digits that are present in the number.
     * @param  $scale $scale     - (Optional) Refers to the maximum number of decimal places.
     * @return bool
     */
    public static function isDecimal($input, $precision = null, $scale = null) {
        if (!is_null($precision) && $precision < 1) {
            throw new Exception('Precision must be larger than 1.');
        }    
        
        if (!is_null($scale) && $scale < 1) {
            throw new Exception('Scale must be larger than 1.');
        }    
        
        if ((!is_null($scale) && is_null($precision)) || (!is_null($scale) && $scale > $precision)) {
            throw new Exception('Scale can not be larger than the total precision of the number.');
        }
        
        $whole_num_len = null;

        if (!is_null($precision))  {
            $whole_num_len = $precision;
            
            if (!is_null($scale)) {
                $whole_num_len -= $scale;
            }
        }
        
        return (bool)preg_match('/^[\-+]?'.
            '([0-9]' . ((!is_null($whole_num_len)) ? "{1,$whole_num_len}" : '+') . ')\.' .
            '([0-9]' . ((!is_null($scale)) ? "{1,$scale}" : '+') . ')$/', $input);
    }
    
    /**
     * Check if the field has a value.
     *
     * @param  string  $field
     * @param  boolean $incl_null - (Optional) Including null values?
     * @return boolean
     */
    protected function _hasValue($field, $incl_null = false) {
        if (array_key_exists($field, $this->_data) === false
            || ($incl_null === false && is_null($this->_data[$field]) === true)) {
            return false;
        }

        return true;
    }

}
