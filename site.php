<?php
use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;

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

//Cart
$app->get("/cart", function() {
	$cart=Cart::getFromSession();
	$page=new Page();
	$page->setTpl("cart",[
		"cart"=>$cart->getValues(),
		"products"=>$cart->getProducts(),
		"error"=>Cart::getMsgErro()
	]);
});
$app->get("/cart/:idproduct/add", function($idproduct) {
	$cart=Cart::getFromSession();
	$qtd=(isset($_GET["qty"])) ? (int)$_GET["qty"] : 1;
	for ($i=0; $i<$qtd; $i++){ 
		$cart->addProduct($idproduct);
	}
	header("Location: /cart");
	exit;
});
$app->get("/cart/:idproduct/minus", function($idproduct) {
	$cart=Cart::getFromSession();
	$cart->removeProduct($idproduct);
	header("Location: /cart");
	exit;
});
$app->get("/cart/:idproduct/remove", function($idproduct) {
	$cart=Cart::getFromSession();
	$cart->removeProduct($idproduct, true);
	header("Location: /cart");
	exit;
});
$app->post("/cart/freight", function() {
	$cart=Cart::getFromSession();
	$cart->setFreight($_POST['zipcode']);
	header("Location: /cart");
	exit;
});

//checkout
$app->get("/checkout", function() {
	User::verifyLogin(false);
	$address=new Address();
	$cart=Cart::getFromSession();
	if (isset($_GET['zipcode'])) {
		$_GET['zipcode']=$cart->getdeszipcode();
		$address->loadFromCEP($_GET['zipcode']);
		$cart->setdeszipcode($_GET['zipcode']);
		$cart->save();
		$cart->getCalcTotal();
	}
	if (!$address->getdesaddress()) $address->setdesaddress('');
	if (!$address->getdescomplement()) $address->setdescomplement('');
	if (!$address->getdesdistrict()) $address->setdesdistrict('');
	if (!$address->getdescity()) $address->setdescity('');
	if (!$address->getdesstate()) $address->setdesstate('');
	if (!$address->getdescountry()) $address->setdescountry('');
	if (!$address->getdeszipcode()) $address->setdeszipcode('');
	$page=new Page();
	$page->setTpl("checkout", [
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>Address::getMsgErro()
	]);
});
$app->post("/checkout", function() {
	User::verifyLogin(false);
	if (!isset($_POST['zipcode'])||$_POST['zipcode']==='') {
		$_SESSION["AddressError"]="Informe o CEP";
		header("Location: /checkout");
		exit;
	}
	if (!isset($_POST['desaddress'])||$_POST['desaddress']==='') {
		$_SESSION["AddressError"]="Informe o endereço";
		header("Location: /checkout");
		exit;
	}
	if (!isset($_POST['desdistrict'])||$_POST['desdistrict']==='') {
		$_SESSION["AddressError"]="Informe o bairro";
		header("Location: /checkout");
		exit;
	}
	if (!isset($_POST['descity'])||$_POST['descity']==='') {
		$_SESSION["AddressError"]="Informe a cidade";
		header("Location: /checkout");
		exit;
	}
	if (!isset($_POST['desstate'])||$_POST['desstate']==='') {
		$_SESSION["AddressError"]="Informe o estado";
		header("Location: /checkout");
		exit;
	}
	if (!isset($_POST['descountry'])||$_POST['descountry']==='') {
		$_POST['descountry']="Brasil";
	}
	$user=User::getFromSession();
	$address=new Address();
	$_POST['deszipcode']=$_POST['zipcode'];
	$_POST['idperson']=$user->getidperson();
	$address->setData($_POST);
	$address->save();
	header("Location: /order");
	exit;
});

//Login & register
$app->get("/login", function() {
	$page=new Page();
	$page->setTpl("login", [
		'error'=>User::getError(),
		'errorRegister'=>User::getErrorRegister(),
		'regVal'=>(isset($_SESSION['regVal'])) ? $_SESSION['regVal'] : ['name'=>'', 'email'=>'', 'phone'=>'']
	]);
});
$app->post("/login", function() {
	try {
		User::login($_POST['login'],$_POST['senha']);
	} catch (Exception $e) {
		$_SESSION["UserError"]=$e->getMessage();
	}
	header("Location: /checkout");
	exit;
});
$app->get("/logout", function() {
	User::logout();
	header("Location: /");
	exit;
});
$app->post("/register", function() {
	$_SESSION['regVal']=$_POST;
	if (!isset($_POST['name'])||$_POST['name']=='') {
		$_SESSION["UserErrorRegister"]="Insira o seu nome.";
		header("Location: /login");
		exit;
	}
	if (!isset($_POST['email'])||$_POST['email']=='') {
		$_SESSION["UserErrorRegister"]="Insira o seu e-mail.";
		header("Location: /login");
		exit;
	}
	if (!isset($_POST['senha'])||$_POST['senha']=='') {
		$_SESSION["UserErrorRegister"]="Insira uma senha.";
		header("Location: /login");
		exit;
	}
	if (User::checkLoginExist($_POST['email'])) {
		$_SESSION["UserErrorRegister"]="E-mail já cadastrado.";
		header("Location: /login");
		exit;
	}
	$user=new User();
	$user->setData([
		"inadmin"=>0,
		"deslogin"=>$_POST['email'],
		"desperson"=>$_POST['name'],
		"desemail"=>$_POST['email'],
		"despassword"=>$_POST['senha'],
		"nrphone"=>$_POST['phone']
	]);
	$user->save();
	User::login($_POST['email'], $_POST['senha']);
	header("Location: /checkout");
	exit("OK");
});

//Forgot the password
$app->get("/forgot", function() {
	$page=new Page();
	$page->setTpl("forgot");
});
$app->post("/forgot", function() {
	User::getForgot($_POST["email"], false);
	header("Location: /forgot/sent");
	exit;
});
$app->get("/forgot/sent", function() {
	$page=new Page();
	$page->setTpl("forgot-sent");
});
$app->get("/forgot/reset", function() {
	$user=User::validForgotDecrypt($_GET["code"]);
	$page=new Page();
	$page->setTpl("forgot-reset", ["name"=>$user["desperson"], "code"=>$_GET["code"]]);
});
$app->post("/forgot/reset", function() {
	$forgot=User::validForgotDecrypt($_POST["code"]);
	User::setForgotUsed($forgot["idrecovery"]);
	$user=new User();
	$user->get((int)$forgot["iduser"]);
	$hash=password_hash($_POST["password"], PASSWORD_DEFAULT, ["cost"=>12]);
	$user->setPassword($hash);
	$page=new Page();
	$page->setTpl("forgot-reset-success");
});

//Profile
$app->get("/profile", function() {
	User::verifyLogin(false);
	$user=User::getFromSession();
	$page=new Page();
	$page->setTpl("profile", [
		'user'=>$user->getValues(),
		'profileMsg'=>User::getSuccess(),
		'profileError'=>User::getError()
	]);
});
$app->post("/profile", function() {
	User::verifyLogin(false);
	if (!isset($_POST['desperson'])||$_POST['desperson']=='') $_SESSION["UserError"]='Insira um nome';
	elseif (!isset($_POST['desemail'])||$_POST['desemail']=='') $_SESSION["UserError"]='Insira um e-mail';
	else{
		$user=User::getFromSession();
		if ($_POST['desemail']!=$user->getdesemail()&&User::checkLoginExists($_POST['desemail']))
			$_SESSION["UserError"]='Este e-mail já está cadastrado';
		else{
			$_POST['inadmin']=$user->getinadmin();
			$_POST['despassword']=$user->getdespassword();
			$user->setData($_POST);
			$user->update();
			$_SESSION["UserSuccess"]='Dados alterados com sucesso';
		}
	}
	header("Location: /profile");
	exit;
});
?>