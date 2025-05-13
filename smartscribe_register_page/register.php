<?php
$conn = new mysqli("localhost", "root", "123", "smartscribe_db");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = $_POST["email"];
  $password = $_POST["password"];
  $confirm = $_POST["confirm_password"];

  if ($password !== $confirm) {
    die("Passwords do not match.");
  }

  $hashed = password_hash($password, PASSWORD_DEFAULT);
  $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
  $stmt->bind_param("ss", $email, $hashed);

  if ($stmt->execute()) {
    echo "Registered successfully!";
  } else {
    echo "Error: " . $stmt->error;
  }

  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  
}
?>
