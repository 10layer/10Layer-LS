<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * 10Layer Memcached class
 *
 * Syncs content to the memcache
 *
 * @package 10Layer
 * @subpackage	Libraries
 * @category	Libraries
 * @author		Jason Norwood-Young
 * @link		http://10layer.com
 * 
 */
 
class Memcacher {
	protected $ci;
	protected $enabled=true;
	protected $memcache;
	protected $reset;
	protected $write;
	
	public function __construct() {
		$this->ci=&get_instance();
		if (!$this->ci->config->item("memcache_enable")) {
			$this->enabled=false;
			return true;
		}
		$this->reset=$this->ci->config->item("memcache_reset");
		$this->write=$this->ci->config->item("memcache_write");
		$this->memcached = new Memcached;
		$servers=$this->ci->config->item("memcache_servers");
		if (!is_array($servers)) {
			$server=array("server"=>"localhost","port"=>11211);
			$this->memcached->addServer($server["server"],$server["port"]);
		} else {
			foreach($servers as $server) {
				$this->memcached->addServer($server["server"],$server["port"]);
			}
		}
	}
	
	public function add($key, $data, $ttl=0) {
		if (!$this->enabled) {
			return true;
		}
		$this->memcached->set($key, $data, $ttl);
	}
	
	public function clear($key) {
		if (!$this->enabled) {
			return true;
		}
		$this->memcached->delete($key);
	}
	
	public function get($key) {
		if (!$this->enabled) {
			return false;
		}
		return $this->memcached->get($key);
	}
	
	public function increment($key, $ttl=0, $val=1) {
		if (!$this->enabled) {
			return false;
		}
		$result=$this->memcached->increment($key, $val);
		if ($this->memcached->getResultCode()!=Memcached::RES_SUCCESS) {
			$result=0;
			$this->memcached->add($key, 0, $ttl);
		}
		return $result;
	}
	
	public function getInfo() {
		
	}
	
	public function isOnline() {
		$result=array();
		$servers=$this->ci->config->item("memcache_servers");
		if (!is_array($servers)) {
			$server=array("server"=>"localhost","port"=>11211);
			$result[$server["server"]]=$this->memcached->getServerStatus($server["server"],$server["port"]);
		} else {
			foreach($servers as $server) {
				$result[$server["server"]]=$this->memcached->getServerStatus($server["server"],$server["port"]);
			}
		}
		return $result;
	}
	
	public function addById($contenttype_urlid,$urlid) {
		if (!$this->enabled) {
			return true;
		}
		if ($this->write) {
			$content_type=$this->ci->model_content->getContentType($contenttype_urlid);
			$this->ci->load->model($content_type->model, "content");
			$content=$this->ci->content->getByIdORM($urlid, $contenttype_urlid)->getFull();
			$this->memcached->set($this->cacheKey($contenttype_urlid,$urlid), $content);
			//print $this->cacheKey($contenttype_urlid,$urlid);
			return true;
		}
	}
	
	public function clearById($contenttype_urlid, $urlid) {
		if (!$this->enabled) {
			return true;
		}
		if ($this->reset) {
			$this->memcached->delete($this->cacheKey($contenttype_urlid,$urlid));
		}
	}
	
	public function getById($contenttype_urlid,$urlid) {
		//$this->memcached->flush();
		if (!$this->enabled) {
			$content_type=$this->ci->model_content->getContentType($contenttype_urlid);
			$this->ci->load->model($content_type->model, "content");
			$content=$this->ci->content->getByIdORM($urlid, $contenttype_urlid)->getFull();
			return $content;
		}
		$result=$this->memcached->get($this->cacheKey($contenttype_urlid,$urlid));
		if ($this->memcached->getResultCode()==Memcached::RES_NOTFOUND) {
			$this->addById($contenttype_urlid, $urlid);
			$result=$this->memcached->get($this->cacheKey($contenttype_urlid,$urlid));
		} elseif ($this->memcached->getResultCode()!=Memcached::RES_SUCCESS) {
			show_error("Memcached error: ".$this->memcached->getResultMessage());
		}
		return $result;
	}
	
	public function picFilename($contenttype_urlid,$urlid) {
		if (!$this->enabled) {
			$this->ci->load->library("tlpicture");
			$result=$this->ci->tlpicture->findPicture($urlid, $contenttype_urlid);
			return $result;
		}
		$key="pic-".$this->cacheKey($contenttype_urlid,$urlid);
		$result=$this->memcached->get($key);
		//$this->memcache->flush();
		if ($result===false) {
			$this->ci->load->library("tlpicture");
			$result=$this->ci->tlpicture->findPicture($urlid, $contenttype_urlid);
			if (empty($result) || !file_exists(".".$result)) {
				$result="";
			}
			$this->memcached->set($key, $result);
		}
		if (empty($result)) {
			return false;
		}
		return $result;
	}
	
	public function clearPic($contenttype_urlid,$urlid) {
		if (!$this->enabled) {
			return true;
		}
		$key="pic-".$this->cacheKey($contenttype_urlid,$urlid);
		$this->memcached->delete($key);
	}
	
	protected function cacheKey($contenttype_urlid,$urlid) {
		$content_type=$this->ci->model_content->getContentType($contenttype_urlid);
		$this->ci->load->model($content_type->model, "content");
		$content=$this->ci->content->getByIdORM($urlid, $contenttype_urlid)->getFull();
		return $content_type->urlid."-".$content->urlid;
	}
	
	
}
?>