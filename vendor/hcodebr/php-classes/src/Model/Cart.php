<?php
namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Model\User;

class Cart extends Model{
    public static function getFromSession(){
        $cart=new Cart();
        if (isset($_SESSION["Cart"]["idcart"])&&$_SESSION["Cart"]["idcart"]=3/*>0*/){
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
                $cart->setData(["dessessionid"=>session_id(), "iduser"=>$user->getiduser()]);
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
        getCalcTotal();
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
        getCalcTotal();
    }
    public function getProducts(){
        $sql=new Sql();
        return Product::checkList($sql->select("SELECT p.idproduct, p.desproduct, p.vlprice, p.desurl, COUNT(*) AS qtd, SUM(p.vlprice) AS vltotal FROM tb_cartsproducts cp
        INNER JOIN tb_products p ON cp.idproduct=p.idproduct WHERE cp.idcart=:idcart AND cp.dtremoved IS NULL GROUP BY p.idproduct ORDER BY p.desproduct",
        [":idcart"=>$this->getidcart()]));
    }
    public function getProductsTotals(){
        $sql=new Sql();
        $r=$sql->select("SELECT SUM(p.vlprice) AS vlprice, SUM(p.vlwidth) AS vlwidth, SUM(p.vlheight) AS vlheight, SUM(p.vllength) AS vllength, SUM(p.vlweight) AS vlweight, COUNT(*) AS qtd FROM tb_cartsproducts cp
        INNER JOIN tb_products p ON cp.idproduct=p.idproduct WHERE cp.idcart=:idcart AND cp.dtremoved IS NULL",
        [":idcart"=>$this->getidcart()]);
        if (count($r)>0){return $r[0];}
        else{
            echo "Erro ao somar os totais";
            return [];
        }
    }
    public function setFreight($cep){
        $cep=str_replace("-", "", $cep);
        $totals=$this->getProductsTotals();
        if ($totals["qtd"]>0){
            if ($totals["vlheight"]<2) $totals["vlheight"]=2;
            if ($totals["vllength"]<16) $totals["vllength"]=16;
            $qs=http_build_query([
                "nCdEmpresa"=>"",
                "sDsSenha"=>"",
                "nCdServico"=>"40010",
                "sCepOrigem"=>"09853120",
                "sCepDestino"=>$cep,
                "nVlPeso"=>$totals["vlweight"],
                "nCdFormato"=>1,
                "nVlComprimento"=>$totals["vllength"],
                "nVlAltura"=>$totals["vlheight"],
                "nVlLargura"=>$totals["vlwidth"],
                "nVlDiametro"=>0,
                "sCdMaoPropria"=>"N",
                "nVlValorDeclarado"=>0,
                "sCdAvisoRecebimento"=>"N"
            ]);
            $xml=simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?$qs");
            $r=$xml->Servicos->cServico;
            if ($r->MsgErro!=""){
                $_SESSION["CartError"]=$r->MsgErro;
                echo $r->MsgErro;
            }else{
                $_SESSION["CartError"]=null;
            }
            $this->setnrdays($r->PrazoEntrega);
            $this->setvlfreight((float)str_replace(',', '.', str_replace('.', '', $r->Valor)));
            $this->setdeszipcode($cep);
            $this->save();
            return $r;
        }else{
            echo "Carrinho vazio";
        }
    }
    // public static function setMsgErro($msg){
    //     $_SESSION["CartError"]=$msg;
    // }
    public static function getMsgErro(){
        $msg=(isset($_SESSION["CartError"])) ? $_SESSION["CartError"] : "";
        $_SESSION["CartError"]=null;
        return $msg;
    }
    public function getValues(){
        $this->getCalcTotal();
        return parent::getValues();
    }
    public function getCalcTotal(){
        if ($this->getdeszipcode()!=''){$this->setFreight($this->getdeszipcode());}
        $totals=$this->getProductsTotals();
        $this->setvlsubtotal($totals['vlprice']);
        $this->setvltotal($totals['vlprice']+$this->getvlfreight());
    }
}
?>