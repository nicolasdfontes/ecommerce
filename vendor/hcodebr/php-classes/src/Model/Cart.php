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
        //$this->setData($r[0]);
	}
}
?>