<?php

$servername = "localhost";
$user = "root";
$password = "";
$dbname = "Student_Management_System";

$conn = new mysqli($servername, $user, $password, $dbname);

if($conn->connect_error){
    die("Connection Failed: ".$conn->connect_error);

}
// header("Location: read.php");
// echo "Connected Successfully";

// $create = "CREATE DATABASE Student_Management_System";
// if($conn->query($create)===TRUE){
//     Echo "Database Created Successfully";
// }
// else{
//     echo "Error: ".$conn ->error;
// }

$sql = " CREATE TABLE if not EXISTS students(
    id INT(6) AUTO_INCREMENT PRIMARY KEY,
     name VARCHAR(244) NOT NULL,
     marks int(11) NOT NULL,
     roll_no VARCHAR(255) NOT NULL,
     password varchar(255) not null,
     last_qualification varchar(255) not null,
     programme varchar(255) not null,
     gender varchar(255) not null,
     address varchar(255) not null,
        result_card varchar(255) not null
     )";


// if($conn->query($sql)=== TRUE){
//     echo "Table Created Successfully";
// }
// else{
//     echo "Error: ".$conn ->error;	
// }

?>