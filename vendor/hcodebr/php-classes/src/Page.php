<?php 
namespace Hcode;

use Rain\Tpl;

class Page {
	private $tpl;
	private $options=[];
	private $defaults=[
		"header"=>true,
		"footer"=>true,
		"data"=>[]
	];
	public function __construct($opts=array(), $tpl_dir = "/views/"){
		//$this->defaults["data"]["session"] = $_SESSION;
		$this->options=array_merge($this->defaults, $opts);
		$config=array(
		    //"base_url"=>null,
		    "tpl_dir"=>$_SERVER['DOCUMENT_ROOT'].$tpl_dir,
		    "cache_dir"=>$_SERVER['DOCUMENT_ROOT']."/views-cache/",
		    "debug"=>false
		);
		Tpl::configure($config);
		$this->tpl = new Tpl;
		//if ($this->options['data']) {
		foreach($this->options['data'] as $key=>$value){$this->tpl->assign($key, $value);}
		//}
		if ($this->options['header']===true) $this->tpl->draw("header");
	}
	public function __destruct(){
		if ($this->options['footer']===true) $this->tpl->draw("footer");
	}
	/*private function setData($data = array()){
		foreach($data as $key => $value){
			$this->tpl->assign($key, $value);
		}
	}*/
	public function setTpl($name, $data=array(), $returnHTML=false){
		foreach($data as $key=>$value){$this->tpl->assign($key, $value);}
		return $this->tpl->draw($name, $returnHTML);
	}
}
?>