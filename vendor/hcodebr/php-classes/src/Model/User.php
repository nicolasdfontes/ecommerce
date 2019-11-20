<?php 
namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model{
	const SECRET="HcodePhp7_Secret";
	const SECRET_IV="HcodePhp7_Secret_IV";
	//protected $fields=["iduser", "idperson", "deslogin", "despassword", "inadmin", "dtergister"];

	public static function getFromSession(){
		$user=new User();
        if (isset($_SESSION["User"])&&(int)$_SESSION["User"]["iduser"]>0){$user->setData($_SESSION["User"]);}
		return $user;
	}
	public static function verifyLogin($inadmin=true, $redirect=true){
		if (!isset($_SESSION["User"])||!$_SESSION["User"]||(int)$_SESSION["User"]["iduser"]<=0){
            if ($redirect) {
                if ($inadmin) header("Location: /admin/login");
			    else header("Location: /login");
            } else return false;
		}else{
			if ($redirect) {
                if ($inadmin&&!(bool)$_SESSION["User"]["inadmin"]) {
                    header("Location: /");
                    exit("Acesso não autorizado");
                }
            } else return true;
		}
	}
	public static function login($login, $senha){
		$sql = new Sql();
		$r=$sql->select("SELECT * FROM tb_users WHERE deslogin=:LOGIN",array(":LOGIN"=>$login));
		if (count($r)===0) throw new \Exception("Login inválido.");
		if (password_verify($senha, $r[0]["despassword"])) {
            $user=new User();
            $r[0]['desperson']=utf8_encode($r[0]['desperson']);
			$user->setData($r[0]);
			$_SESSION["User"]=$user->getValues();
			return $user;
		} else throw new \Exception("Senha inválida.");
	}
	public static function logout(){$_SESSION["User"]=NULL;}
	public static function listAll(){
		$sql=new Sql();
		return $sql->select("SELECT * FROM tb_users u INNER JOIN tb_persons p USING(idperson) ORDER BY p.desperson");
	}
	public function save(){
		$sql=new sql();
		$r=$sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":desperson"=>utf8_decode($this->getdesperson()),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>User::getPasswordHash($this->getdespassword()),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));
		$this->setData($r[0]);
	}
	public function get($iduser){
		$sql=new Sql();
        $r=$sql->select("SELECT * FROM tb_users u INNER JOIN tb_persons p USING(idperson) WHERE u.iduser=:iduser", array(":iduser"=>$iduser));
        $r[0]['desperson']=utf8_encode($r[0]['desperson']);
		$this->setData($r[0]);
	}
	public function update(){
		$sql=new sql();
		$r=$sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":iduser"=>$this->getiduser(),
			":desperson"=>utf8_decode($this->getdesperson()),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>User::getPasswordHash($this->getdespassword()),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));
		$this->setData($r[0]);
	}
	public function delete(){
		$sql=new sql();
		$sql->query("CALL sp_users_delete(:iduser)", array(":iduser"=>$this->getiduser()));
	}
	public static function getForgot($email, $inadmin=true){
		$sql=new Sql();
		$r=$sql->select("SELECT * FROM tb_persons p INNER JOIN tb_users u USING(idperson) WHERE p.desemail=:email", array(":email"=>$email));
		if (count($r)===0) throw new \Exception("Email não cadastrado!");
		else {
			$r2=$sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(":iduser"=>$r[0]["iduser"], ":desip"=>$_SERVER["REMOTE_ADDR"]));
			if (count($r2)===0) throw new \Exception("Não foi possível recuperar a senha.");
			else {
				$code=base64_encode(openssl_encrypt($r2[0]["idrecovery"], "AES-128-CBC", pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV)));
				if ($inadmin===true) $link="http://www.hcodecommerce.com.br:8080/admin/forgot/reset?code=$code";
				else $link="http://www.hcodecommerce.com.br:8080/forgot/reset?code=$code";
				$mailer=new Mailer($r[0]["desemail"], $r[0]["desperson"], "Redefinir senha da Hcode store", "forgot", array("name"=>$r[0]["desperson"], "link"=>$link));				
				$mailer->send();
				return $r[0];
			}
		}
	}
	public static function validForgotDecrypt($code){
		$idrecovery=openssl_decrypt(base64_decode($code), 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));
		$sql=new Sql();
		$r=$sql->select("SELECT * FROM tb_userspasswordsrecoveries upr INNER JOIN tb_users u USING(iduser) INNER JOIN tb_persons p USING(idperson)
			WHERE upr.idrecovery=:idrecovery AND upr.dtrecovery IS NULL AND DATE_ADD(upr.dtregister, INTERVAL 1 HOUR) >= NOW();", array(":idrecovery"=>$idrecovery));
		if (count($r)===0) throw new \Exception("Não foi possível recuperar a senha.");
		else {return $r[0];}
	}
	public static function setForgotUsed($idrecovery){
		$sql = new Sql();
		$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery=:idrecovery", array(":idrecovery"=>$idrecovery));
	}
	public function setPassword($senha){
		$sql = new Sql();
		$sql->query("UPDATE tb_users SET despassword=:senha WHERE iduser = :iduser", array(":senha"=>$senha, ":iduser"=>$this->getiduser()));
	}
	// public static function setError($msg){
	// 	$_SESSION["UserError"]=$msg;
	// }
	public static function getError(){
		$msg=(isset($_SESSION["UserError"])) ? $_SESSION["UserError"] : "";
		$_SESSION["UserError"]=null; //clearError
		return $msg;
	}
	// public static function setErrorRegister($msg){
	// 	$_SESSION["UserErrorRegister"]=$msg;
	// }
    public static function getErrorRegister(){
		$msg=(isset($_SESSION["UserErrorRegister"])) ? $_SESSION["UserErrorRegister"] : "";
		$_SESSION["UserErrorRegister"]=null; //clearErrorRegister
		return $msg;
	}
	public static function checkLoginExist($login){
		$sql=new Sql();
		$r=$sql->select("SELECT * FROM tb_users WHERE deslogin=:deslogin",[":deslogin"=>$login]);
		return (count($r)>0);
	}
    public static function getPasswordHash($senha){
        return password_hash($senha, PASSWORD_DEFAULT, ['cost'=>12]);
    }
}
?>