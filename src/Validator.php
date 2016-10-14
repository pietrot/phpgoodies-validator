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
     * Verify if the field satisfies the constraint "required".
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
     * Verify if the field satisfies the constraint "equal" some value (defined in $args).
     *
     * @param  string $field
     * @param  string $rule
     * @param  array  $args    - (Optional)
     * @param  string $message - (Optional)
     * @return void
     */
    protected function _verifyEqual($field, $rule, array $args = array(), $message = '') {
        if (count($args) !== 1 || $this->_hasValue($field) === false) {
            throw new Exception('Missing valu(e) to validate against!');
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
     * Verify if the field satisfies the constraint "min".
     *
     * @param  string $field
     * @param  string $rule
     * @param  array  $args    - (Optional)
     * @param  string $message - (Optional)
     * @return void
     */
    protected function _verifyMin($field, $rule, array $args = array(), $message = '') {
        if (count($args) !== 1 || $this->_hasValue($field) === false) {
            throw new Exception('Missing valu(e) to validate against!');
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
     * Verify if the field satisfies the constraint "max.
     *
     * @param  string $field
     * @param  string $rule
     * @param  array  $args    - (Optional)
     * @param  string $message - (Optional)
     * @return void
     */
    protected function _verifyMax($field, $rule, array $args = array(), $message = '') {
        if (count($args) !== 1 || $this->_hasValue($field) === false) {
            throw new Exception('Missing valu(e) to validate against!');
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
