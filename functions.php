<?php
function formatPrice(float $price){
	return number_format($price, 2, ",", " ");
}
function post($key){
	return str_replace("'", "", $_POST[$key]);
}
function get($key){
	return str_replace("'", "", $_GET[$key]);
}
?>