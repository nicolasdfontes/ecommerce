<?php
use \Hcode\Page;
use \Hcode\Model\Category;
use \Hcode\Model\Product;

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
	$page->setTpl("category",["category"=>$category->getValues(),
		"products"=>$pagination["data"],
		"page"=>$pages
	]);
});
?>