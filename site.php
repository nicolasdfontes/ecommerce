<?php
use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;

$app->get('/', function() {
	$products=Product::listAll();
	$page=new Page();
	$page->setTpl("index",["products"=>Product::checkList($products)]);
});
$app->get("/categories/:idcategory", function($idcategory) {
	$pg=(isset($_GET["page"])) ? $_GET["page"] : 1;
	$category=new Category();
	$category->get((int)$idcategory);
	$pagination=$category->getProductsPage($idcategory, $pg);
	$pages=[];
	for ($i=1; $i<=$pagination["pages"]; $i++) { 
		array_push($pages, ["link"=>"/categories/$idcategory", "page"=>$i]);
	}
	$page=new Page();
	$page->setTpl("category",[
		"category"=>$category->getValues(),
		"products"=>$pagination["data"],
		"page"=>$pages
	]);
});
$app->get("/products/:desurl", function($desurl) {
	$product=new Product();
	$product->getFromURL($desurl);
	$page=new Page();
	$page->setTpl("product-detail",["product"=>$product->getValues(), "categories"=>$product->getCategories()]);
});
$app->get("/cart", function(){
	//$_SESSION["Cart"]["dessessionid"]="8sb3hu4jvstidho1s4qgbjgnfe";
	$cart=Cart::getFromSession();
	$page=new Page();
	$page->setTpl("cart",[
		"cart"=>$cart->getValues(),
		"products"=>$cart->getProducts(),
		"error"=>Cart::getMsgErro()
	]);
});
$app->get("/cart/:idproduct/add", function($idproduct){
	$cart=Cart::getFromSession();
	$qtd=(isset($_GET["qty"])) ? (int)$_GET["qty"] : 1;
	for ($i=0; $i<$qtd; $i++){ 
		$cart->addProduct($idproduct);
	}
	header("Location: /cart");
	exit;
});
$app->get("/cart/:idproduct/minus", function($idproduct){
	$cart=Cart::getFromSession();
	$cart->removeProduct($idproduct);
	header("Location: /cart");
	exit;
});
$app->get("/cart/:idproduct/remove", function($idproduct){
	$cart=Cart::getFromSession();
	$cart->removeProduct($idproduct, true);
	header("Location: /cart");
	exit;
});
$app->post("/cart/freight", function(){
	$cart=Cart::getFromSession();
	$cart->setFreight($_POST["zipcode"]);
	header("Location: /cart");
	exit;
});
?>