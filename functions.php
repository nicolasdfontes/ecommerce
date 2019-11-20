<?php
use \Hcode\Model\User;

function formatPrice($price){
	if ($price<=0) $price=0;
	return number_format($price, 2, ",", ".");
}
function checkLogin($inadmin=false, $redirect=false){
	return User::verifyLogin($inadmin, $redirect);
}
function getUserName(){
	$user=User::getFromSession();
	return $user->getdesperson();
}
function post($key){
	return str_replace("'", "", $_POST[$key]);
}
function get($key){
	return str_replace("'", "", $_GET[$key]);
}
?>