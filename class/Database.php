<?php

class Database implements JsonSerializable{
    private $name;
    private $age;
    private $email;
    private $phone;
    private $user_id;
    private $fields = array(
        "name",
        "age",
        "email",
        "phone",
        "user_id"
    );
    
    function __construct($user){
        // Set the object property for each parameter provided in the array
        foreach ($user as $key => $value){
            foreach ($this -> fields as $field){
                if($key == $field){
                    $this -> {$key} = $value;
                }
            }
        }

    }


    public function jsonSerialize()
    {  
        // Create array with user_id as the key and array of
        // of remaining object properties as value
        $response = array($this -> user_id => Array());
        foreach ($this -> fields as $field){
            if($field !== 'user_id')
                $response[$this -> user_id][$field] = $this -> $field;
            }
        return $response;
    }

}