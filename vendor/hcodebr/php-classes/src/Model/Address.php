<?php
namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Address extends Model{
    public static function getCEP($nrCEP){
        $nrCEP=str_replace("-", "", $nrCEP);
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://viacep.com.br/ws/$nrCEP/json/");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $CEPinfo=json_decode(curl_exec($ch), true);
        curl_close($ch);
        return $CEPinfo;
    }
    public function loadFromCEP($nrCEP){
        $CEPinfo=Address::getCEP($nrCEP);
        if (isset($CEPinfo['logradouro'])&&$CEPinfo['logradouro']){
            $this->setdesaddress($CEPinfo['logradouro']);
            $this->setdescomplement($CEPinfo['complemento']);
            $this->setdesdistrict($CEPinfo['bairro']);
            $this->setdescity($CEPinfo['localidade']);
            $this->setdesstate($CEPinfo['uf']);
            $this->setdescountry('Brasil');
            $this->setdeszipcode(str_replace("-", "", $nrCEP));
        }
    }
    public function save(){
        $sql = new Sql();
        $r=$sql->select("CALL sp_addresses_save(:idaddress, :idperson, :desaddress, :descomplement, :descity, :desstate, 'Brasil', :deszipcode, :desdistrict)",[
            ':idaddress'=>$this->getidaddress(),
            ':idperson'=>$this->getidperson(),
            ':desaddress'=>utf8_decode($this->getdesaddress()),
            ':descomplement'=>utf8_decode($this->getdescomplement()),
            ':descity'=>utf8_decode($this->getdescity()),
            ':desstate'=>utf8_decode($this->getdesstate()),
            ':deszipcode'=>$this->getdeszipcode(),
            ':desdistrict'=>utf8_decode($this->getdesdistrict())
        ]);
        if (count($r)>0){$this->setData($r[0]);}
    }
    //public static function setMsgErro($msg){$_SESSION["AddressError"]=$msg;}
    public static function getMsgErro(){
        $msg=(isset($_SESSION["AddressError"])) ? $_SESSION["AddressError"] : "";
        /*public static function clearMsgErro()*/$_SESSION["AddressError"]=null;
        return $msg;
    }
}
?>