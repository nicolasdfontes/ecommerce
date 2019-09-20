<?php
namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Model\User;

class Cart extends Model{
    public static function getFromSession(){
        $cart=new Cart();
        if (isset($_SESSION["Cart"])&&isset($_SESSION["Cart"]["idcart"])){
            $sql=new Sql();
            $r=$sql->select("SELECT * FROM tb_carts WHERE idcart=:idcart", [":idcart"=>(int)$_SESSION["Cart"]["idcart"]]);
            if (count($r)>0){$cart->setData($r[0]);}
            else{
                print_r($_SESSION["Cart"]);
                echo "Deu ruim!";
            }
        }else{
            $sql=new Sql();
		    $r=$sql->select("SELECT * FROM tb_carts WHERE dessessionid=:dessessionid", [":dessessionid"=>session_id()]);
            if (count($r)>0){$cart->setData($r[0]);}
            else{
                $user=User::getFromSession();
                $cart->setData(["dessessionid"=>session_id(), "iduser"=>$user->getiduser()]) ;
                $cart->save();
                $_SESSION["Cart"]=$cart->getValues();
            }
        }
        return $cart;
    }
	public function save(){
        $sql=new Sql();
		$r=$sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", [
            ":idcart"=>$this->getidcart(),
            ":dessessionid"=>$this->getdessessionid(),
            ":iduser"=>$this->getiduser(),
            ":deszipcode"=>$this->getdeszipcode(),
            ":vlfreight"=>$this->getvlfreight(),
            ":nrdays"=>$this->getnrdays()
            ]);
        $this->setData($r[0]);
    }
    public function addProduct($idproduct){
        $sql=new Sql();
        $sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES(:idcart, :idproduct)", [":idcart"=>$this->getidcart(), ":idproduct"=>$idproduct]);
    }
    public function removeProduct($idproduct, $all=false){
        $sql=new Sql();
        if ($all){
            $sql->query("UPDATE tb_cartsproducts SET dtremoved=NOW() WHERE idcart=:idcart AND idproduct=:idproduct AND dtremoved IS NULL",
            [":idcart"=>$this->getidcart(), ":idproduct"=>$idproduct]);
        }else{
            $sql->query("UPDATE tb_cartsproducts SET dtremoved=NOW() WHERE idcart=:idcart AND idproduct=:idproduct AND dtremoved IS NULL LIMIT 1",
            [":idcart"=>$this->getidcart(), ":idproduct"=>$idproduct]);
        }
        
    }
    public function getProducts(){
        $sql=new Sql();
        return Product::checkList($sql->select("SELECT p.idproduct, p.desproduct, p.vlprice, p.desurl, COUNT(*) AS qtd, SUM(p.vlprice) AS vltotal FROM tb_cartsproducts cp
        INNER JOIN tb_products p ON cp.idproduct=p.idproduct WHERE cp.idcart=:idcart AND cp.dtremoved IS NULL GROUP BY p.idproduct ORDER BY p.desproduct",
        [":idcart"=>$this->getidcart()]));
    }
}
?>