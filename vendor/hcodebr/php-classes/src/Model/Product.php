<?php 
namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Product extends Model{
	public static function listAll(){
		$sql=new Sql();
		return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");
	}
	public function save(){
		$sql=new Sql();
		$r=$sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", [
			":idproduct"=>$this->getidproduct(),
			":desproduct"=>$this->getdesproduct(),
			":vlprice"=>$this->getvlprice(),
			":vlwidth"=>$this->getvlwidth(),
			":vlheight"=>$this->getvlheight(),
			":vllength"=>$this->getvllength(),
			":vlweight"=>$this->getvlweight(),
			":desurl"=>$this->getdesurl()
		]);
		$this->setData($r[0]);
	}
	public function get($idproduct){
		$sql=new Sql();
		$r=$sql->select("SELECT * FROM tb_products WHERE idproduct=:idproduct", [":idproduct"=>$idproduct]);
		$this->setData($r[0]);
	}
	public function delete($idproduct){
		$sql=new Sql();
		$sql->query("DELETE FROM tb_products WHERE idproduct=:idproduct", [":idproduct"=>$idproduct]);
	}
	public function checkPhoto(){
		if (file_exists($_SERVER["DOCUMENT_ROOT"].DIRECTORY_SEPARATOR."res".DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."img".DIRECTORY_SEPARATOR."products".DIRECTORY_SEPARATOR.$this->getidproduct().".jpg")){
			$url="/res/site/img/products/".$this->getidproduct().".jpg";
		}else{
			$url="/res/site/img/product.jpg";
		}
		$this->setdesphoto($url);
	}
	public function getValues(){
		$this->checkPhoto();
		return parent::getValues();
	}
	public function setPhoto($file){
		$ext=explode(".", $file["name"]);
		$ext=end($ext);
		switch ($ext) {
			case 'jpg':
			case 'jpeg':$img=imagecreatefromjpeg($file["tmp_name"]);
			break;
			case 'gif':$img=imagecreatefromgif($file["tmp_name"]);
			break;
			case 'png':$img=imagecreatefrompng($file["tmp_name"]);
			break;
		}
		imagejpeg($img, $_SERVER["DOCUMENT_ROOT"].DIRECTORY_SEPARATOR."res".DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."img".DIRECTORY_SEPARATOR."products".DIRECTORY_SEPARATOR.$this->getidproduct().".jpg");
		imagedestroy($img);
		$this->checkPhoto();
	}
}
?>