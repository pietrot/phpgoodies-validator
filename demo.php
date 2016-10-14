<?php

define('ROOT_PATH', dirname(__FILE__));

require_once(ROOT_PATH . '/src/ValidatorResult.php');
require_once(ROOT_PATH . '/src/Validator.php');

use PHPGoodies\Validator;

$input = array(
    'name' => 'bobby',
    'age'  => 1000
);

$result = Validator::validate($input)
    ->verify('id', 'required')
    ->verify('name', 'required')
    ->verify('age', 'min:18|max:200', array('min' => 'Whooah! Too young.', 'max' => 'Whooah! Too old.'))
    ->run();

var_dump($result->toArray());
