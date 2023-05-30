<?php
$filepath = realpath(dirname(__FILE__));
include($filepath . '/../config/config.php'); ?>

<?php

global $conn;

function connectDB() {
  
  $host   = DB_HOST;
  $user   = DB_USER;
  $pass   = DB_PASS;
  $dbname = DB_NAME;
  global $conn;
  $conn = mysqli_connect($host, $user, $pass, $dbname);
  echo mysqli_connect_error();
  if (mysqli_connect_errno()) 
  {
      echo "Failed to connect to MySQL: " . mysqli_connect_error();
      exit();
  }
}

// Select or Read data
function select($query)
{
  global $conn;
  connectDB();
  $result = mysqli_query($conn,$query);
  if (mysqli_num_rows($result) > 0) {
    return $result;
  } else {
    return false;
  }
}

// Insert data
function insert($query)
{
  global $conn;
  connectDB();
  $insert_row = mysqli_query($conn,$query);
  if ($insert_row) {
    return $insert_row;
  } else {
    return false;
  }
}

// Update data
function update($query)
{
  global $conn;
  connectDB();
  $update_row = mysqli_query($conn,$query);
  if ($update_row) {
    return $update_row;
  } else {
    return false;
  }
}

// Delete data
function delete($query)
{
  global $conn;
  connectDB();
  $delete_row = mysqli_query($conn,$query);
  if ($delete_row) {
    return $delete_row;
  } else {
    return false;
  }
}

