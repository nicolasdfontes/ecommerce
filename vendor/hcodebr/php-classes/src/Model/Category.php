<?php 
namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Category extends Model{
	public static function listAll(){
		$sql=new Sql();
		return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");
	}
	public function save(){
		$sql=new Sql();
		$r=$sql->select("CALL sp_categories_save(:idcategory, :descategory)", [":idcategory"=>$this->getidcategory(), ":descategory"=>$this->getdescategory()]);
		$this->setData($r[0]);
		Category::updateFile();
	}
	public function get($idcategory){
		$sql=new Sql();
		$r=$sql->select("SELECT * FROM tb_categories WHERE idcategory=:idcategory", [":idcategory"=>$idcategory]);
		$this->setData($r[0]);
	}
	public function delete($idcategory){
		$sql=new Sql();
		$sql->query("DELETE FROM tb_categories WHERE idcategory=:idcategory", [":idcategory"=>$idcategory]);
		Category::updateFile();
	}
	public static function updateFile(){
		$categories=Category::listAll();
		$html=[];
		foreach ($categories as $row) {
			array_push($html, '<li><a href="/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
			file_put_contents($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."categories-menu.html", implode("", $html));
		}
	}
}
?>