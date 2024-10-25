<?php
session_start();
include "koneksi.php";

$username = $_POST['username'];
$password = $_POST['password'];

$query = "SELECT * FROM user WHERE username='$username' AND password='$password'";

$sql = mysqli_query($konek,$query);
$data = mysqli_fetch_array($sql);

if ($data) {
  $_SESSION['username'] = $username;
  $_SESSION['id_user'] = $data['id_user'];
  $_SESSION['status'] = "login";
  if($data['status']=='admin'){
    header("location:halamanAdmin.php");
  }
  else{
    header("location:halamanMitra.php");
  }
} else{
  header("location:login.php?pesan=gagal");
}