<?php

define('ROOT_PATH', dirname(__FILE__));

require_once(ROOT_PATH . '/src/ValidatorResult.php');
require_once(ROOT_PATH . '/src/Validator.php');

use PHPGoodies\Validator;

$input = array(
    'name'   => 'bobby1',
    'age'    => 1000,
    'weight' => "It's none of your concern!"
);

$result = Validator::validate($input)
    ->verify('id', 'required')
    ->verify('name', 'required|alpha')
    ->verify('age', 'min:18|max:200', array('min' => 'Whooah! Too young.', 'max' => 'Whooah! Too old.'))
    ->verify('height', 'int|min:0|max:20', array('min' => 'Whooah! Are you real?', 'max' => 'Whooah! What were you fed as a child?'))
    ->verify('weight', 'decimal:8:2')
    ->run();

var_dump($result->toArray());
