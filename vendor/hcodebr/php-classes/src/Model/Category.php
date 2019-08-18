<?php 
namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Category extends Model {
	public static function listAll() {
		$sql=new Sql();
		return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");
	}
	public function save() {
		$sql=new Sql();
		$r=$sql->select("CALL sp_categories_save(:idcategory, :descategory)", [":idcategory"=>$this->getidcategory(), ":descategory"=>$this->getdescategory()]);
		$this->setData($r[0]);
		Category::updateFile();
	}
	public function get($idcategory) {
		$sql=new Sql();
		$r=$sql->select("SELECT * FROM tb_categories WHERE idcategory=:idcategory", [":idcategory"=>$idcategory]);
		$this->setData($r[0]);
	}
	public function delete($idcategory) {
		$sql=new Sql();
		$sql->query("DELETE FROM tb_categories WHERE idcategory=:idcategory", [":idcategory"=>$idcategory]);
		Category::updateFile();
	}
	public static function updateFile() {
		$categories=Category::listAll();
		$html=[];
		foreach ($categories as $row) {
			array_push($html, '<li><a href="/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
			file_put_contents($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."categories-menu.html", implode("", $html));
		}
	}
	public function getProducts($idcategory,$related=true) {
		$sql=new Sql();
		if ($related===true) {
			return $sql->select("SELECT * FROM tb_products WHERE idproduct IN(SELECT p.idproduct FROM tb_products p
				INNER JOIN tb_productscategories pc ON p.idproduct=pc.idproduct WHERE pc.idcategory=:idcategory);", [":idcategory"=>$idcategory]);
		} else {
			return $sql->select("SELECT * FROM tb_products WHERE idproduct NOT IN(SELECT p.idproduct FROM tb_products p
				INNER JOIN tb_productscategories pc ON p.idproduct=pc.idproduct WHERE pc.idcategory=:idcategory);", [":idcategory"=>$idcategory]);
		}
	}
	public function getProductsPage($idcategory, $pages=1, $itemsPPage=8) {
		$start=($pages-1)*$itemsPPage;
		$sql=new Sql();
		$r=$sql->select("SELECT SQL_CALC_FOUND_ROWS * FROM tb_products p
			INNER JOIN tb_productscategories pc ON p.idproduct=pc.idproduct
			INNER JOIN tb_categories c ON c.idcategory=pc.idcategory
			WHERE c.idcategory=:idcategory LIMIT $start, $itemsPPage;", [":idcategory"=>$idcategory]);
		$rows=$sql->select("SELECT FOUND_ROWS() AS rows;");
		return ["data"=>Product::checkList($r),
				"total"=>(int)$rows[0]["rows"],
				"pages"=>ceil($rows[0]["rows"]/$itemsPPage)];
	}
	public function addProduct($idcategory, $idproduct) {
		$sql=new Sql();
		$sql->query("INSERT INTO tb_productscategories VALUES(:idcategory, :idproduct)", [":idcategory"=>$idcategory, ":idproduct"=>$idproduct]);
	}
	public function removeProduct($idcategory, $idproduct) {
		$sql=new Sql();
		$sql->query("DELETE FROM tb_productscategories WHERE idcategory=:idcategory AND idproduct=:idproduct", [":idcategory"=>$idcategory, ":idproduct"=>$idproduct]);
	}
}
?>