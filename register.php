<?php
require_once(dirname(__FILE__).'/class/Database.php');

ini_set("display_errors", TRUE);


$responses = [
    400 => "Bad Request",
    404 => "Not Found",
    405 => "Method Not Allowed",
    500 => "Internal server error"
];

//Function to send custom errors back to the client
function send_error($code, $message){
    global $responses;
    $response = $code . " - " . $responses[400] . ": " . $message;
    header($_SERVER['SERVER_PROTOCOL'] . " " . $response);
    $errors["error"] = $response; 
    print(json_encode($errors));
    Die();
}


// Check that the request method is POST and not empty
if($_SERVER["REQUEST_METHOD"] != "POST"){
    send_error(405, "Server only accepts POST requests");
} elseif (empty($_POST)){
    send_error(400, "POST data cannot be empty");
}


header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

/**
 * The following functions server to validate each data field
 * returning appropriate errors if the data is not valid
 */
function validate_name($name, &$results){
    $nameData = $_POST[$name];
    if(strlen($nameData) <= 2 || strlen($nameData) >= 100){
        send_error(400, "name must be longer than 2 characters and less than 100 characters");
        die();
    } elseif(!preg_match("/^[a-zA-Z'-]+$/", $nameData)) {
        send_error(400, "Invalid name");
    } else{
        $results[$name] = $nameData;
    }
}

function validate_age($age, &$results){
    $ageData = $_POST[$age];
    if($ageData > 13 && $ageData < 130){
        $results[$age] = $ageData;
    } else {
        send_error(400, "Age must be greater than 13 and less than 130");
    }
}

function validate_email($email, &$results){
    if(preg_match("/^[a-zA-Z\-]([\w\-.]+)?@([\w\-]+\.)+[\w]+$/", $_POST[$email])){
        $results[$email] = $_POST[$email];
    } else {
        send_error(400, "Invalid email");
    }
}

function validate_phone($phone, &$results){
    $phoneData = $_POST["phone"];
    if(strlen($phoneData) !== 0 && strlen($phoneData) !== 10){
        send_error(400, "Invalid phone number");
    } else {
        $results["phone"] = $phoneData;
    };
}

/**
 * This function loops through each provided parameter and passes
 * them to validation methods
 */
function check_parameter($param, &$results){

    if (isset($_POST[$param])) {
        switch ($param){
            case "name":
                validate_name($param, $results);
                break;
            case "age":
                validate_age($param, $results);
                break;
            case "email":
                validate_email($param, $results);
                break;
        }
    } else {
       send_error(400, $param . " cannot be empty");
    }
}

// arrays for required json response data
$results = array();
$errors = array();

/**
 * The following calls the above validation methods and writes
 * valid data to database
 */

check_parameter("name", $results);
check_parameter("age", $results);
check_parameter("email", $results);
validate_phone("phone", $results);

// If validations pass this line assigns user_id
$user_id = rand(10000, 99999);
$results["user_id"] = $user_id;

// Create new user from user
$data = new Database($results);

// Write the user to the databse
$pathToData = dirname(__FILE__)."/database/userData.txt";
$usersFile = fopen($pathToData, "a");
if(is_writable($pathToData)){
    $userJson = json_encode($data->jsonSerialize());
    fwrite($usersFile, $userJson);
    fclose($usersFile);
} else {
    print("Error accessing Database");
}

// send final response to the client on successfull completion of the above
print(json_encode(array('user_id'=>$user_id)));