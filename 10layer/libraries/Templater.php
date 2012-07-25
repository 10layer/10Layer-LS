<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	/**
	 * Templater class.
	 * 
	 * A template parser using object-based chained language structure
	 *
	 * @author Jason Norwood-Young
	 * @package 10Layer
	 * @subpackage Libraries
	 *
	 */
	 
class Templater {
	protected $ci;
	protected $sections;
	protected $s;
	protected $name;
	protected $type;
	protected $table;
	protected $caching=false;
	protected $link=false;
	protected $field="";
	protected $outline=false;
	protected $isalternate=false;
	protected $isphoto=false;
	protected $photo_size=array(0,0);
	protected $group=false;
	protected $repeats=10;
	public static $repeat_count;
	public static $alternate_options=array();
	protected $cmdchain=array();

	
	public function __construct() {
		$this->ci=&get_instance();
	}
	
	public function setName($name="") {
		$this->cmdchain["setName"]=$name;
		if (is_array($name)) {
			$name=implode("-",$name);
		}
		$this->name=$name;
		return $this;
	}
	
	public function setType($type) {
		$this->cmdchain["setType"]=$type;
		$this->type=$type;
		$this->ci->load->model("model_content");
		$contenttype=$this->ci->model_content_deprecated->get_content_type_by_urlid($type);
		$this->table=$contenttype->table_name;
		return $this;
	}
	
	public function link($link=true) {
		$this->cmdchain["link"]="";
		$this->link=$link;
		return $this;
	}
	
	public function start() {
		$this->caching=true;
		$this->cmdchain=array();
		ob_start();
		return true;
	}
	
	public function end() {
		$cache=ob_get_contents();
		ob_end_clean();
		if ($this->group) {
			$groupstr="";
			for($x=0;$x<$this->repeats;$x++) {
				$groupstr.='<?php self::$repeat_count='.$x.'; ?>'; 
				$groupstr.=$cache;
			}
			$this->group=false;
			
			eval('?>'.$groupstr. '<?');
		} else {
			print $cache;
		}
		$this->cmdchain=array();
		$this->caching=false;
		$this->link=false;
		$this->group=false;
		return true;
	}
	
	public function outline() {
		$this->cmdchain["outline"]="";
		$this->outline=true;
		return $this;
	}
	
	public function content($content) {
		$this->cmdchain["content"]=$content;
		$this->field=$content;
		return $this;
	}
	
	public function draw() {
		$this->cmdchain["draw"]="";
		if ($this->group) {
			return $this->drawCmdChain();
		}
		if ($this->isphoto) {
			return $this->drawPhoto();
		}
		if ($this->outline) {
			return ucfirst($this->name." ".$this->type." ".$this->field." ".self::$repeat_count);
		} else {
			//Render actual data
			return "To be implemented:: {$this->field}";
		}
	}
	
	protected function drawPhoto() {
		$this->isphoto=false;
		if ($this->outline) {
			return photo_emulator($this->photo_size["width"],$this->photo_size["height"]);
		} else {
			return "Photo to be implemented";
		}
	}
	
	public function drawCmdChain() {
		$s='<?= $this->';
		$cmds=array();
		foreach($this->cmdchain as $func=>$vars) {
			
			$cmd=$func;
			if (empty($vars)) {
				$cmd.="()";
			} else {
				$cmd.="('";
				if (is_array($vars)) {		
					$cmd.=implode("','",$vars);
				} else {
					$cmd.=$vars;
				}
				$cmd.="')";
			}
			$cmds[]=$cmd;
		}
		$s.=implode("->",$cmds);
		$s.=" ?>";
		$this->cmdchain=array();
		return $s;
	}
	
	public function parse($view, $data=false) {
		$this->s=$this->ci->load->view($view,"",true);
		$this->_findSections();
		$this->ci->output->append_output($this->s);
		return $this->s;
	}
	
	public function photo($x,$y) {
		$this->photo_size=array("width"=>$x,"height"=>$y);
		$this->cmdchain["photo"]=array($x,$y);
		$this->isphoto=true;
		return $this;
	}
	
	public function repeat($repeats=10) {
		$this->group=true;
		$this->repeats=$repeats;
		return $this;
	}
	
	public function alternate() {
		if ($this->group) {
			return '<?= self::$alternate_options[self::$repeat_count % sizeof(self::$alternate_options)] ?>';
		} else {
			return self::$alternate_options[0];
		}
	}
	
	public function setRepeatCount() {
		self::$repeat_count++;
	}
	
	public function setAlternates($alternates) {
		if (!is_array($alternates)) {
			show_error("$alternates must be an array");
		}
		self::$alternate_options=$alternates;
	}
	
	protected function _findSections() {
		preg_match_all("/\{\{(.*)\}\}/eis",$this->s,$matches);
		foreach($matches as $match) {
			$this->sections=$match;
		}
	}
}
?>