<?php
	$hostname	= "localhost"; //bawaan
	$username	= "root"; //bawaan
	$password	= ""; //kosong
	$database	= "radjaweb"; //nama database yang akan dikoneksikan

	$konek	= new mysqli($hostname, $username, $password, $database); //query koneksi

	if($konek->connect_error) { //cek error
		die("Error : ".$konek->connect_error);
	}
?>