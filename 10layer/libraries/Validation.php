<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	/**
	 * Validation class.
	 * 
	 * Used to validate data.
	 * Not as overbearing as CI's Form_Validation class but shares
	 * some of the same methods, plus some extras
	 *
	 * @author Jason Norwood-Young
	 * @package 10Layer
	 * @subpackage Libraries
	 * 
	 */
	class Validation {
		protected $ci;
		public $passed=true;
		public $failed_fields=array();
		public $failed_names=array();
		public $failed_messages=array();
		
		public function __construct() {
			$this->ci=&get_instance();
			$this->ci->load->helper("password");
		}
		
		/**
		 * validate function.
		 * 
		 * @access public
		 * @param string $fieldname
		 * @param string $name
		 * @param string $value
		 * @param array $ruleset. (default: array())
		 * @return void
		 */
		public function validate($fieldname,$name,$value,$ruleset=array()) {
			if (!is_array($ruleset)) {
				$ruleset=explode("|",$ruleset);
			}
			$tmparr=array();
			foreach($ruleset as $key=>$rulevalue) {
				if (strpos($rulevalue,"=")!==false) {
					$tmp=explode("=",$rulevalue);
					$tmparr[$tmp[0]]=$tmp[1];
				} elseif(!is_numeric($key)) {
					$tmparr[$key]=$rulevalue;
				} else {
					$tmparr[$rulevalue]=true;
				}
			}
			$ruleset=$tmparr;
			//print_r($ruleset);
			foreach($ruleset as $key=>$rulevalue) {
				
				$result=$this->$key($value,$rulevalue);
				if (!$result) {
					$this->passed=false;
					$this->failed_fields[]=$fieldname;
					$this->failed_names[]=$name;
					$this->failed_messages[]=$this->getmessage($key,$name,$rulevalue);
				}
			}
		}
		
		/**
		 * results function.
		 *
		 * Returns a list of passed and failed fields.
		 * 
		 * @access public
		 * @return array $results
		 */
		public function results() {
			return array("passed"=>$this->passed,"failed_fields"=>$this->failed_fields,"failed_names"=>$this->failed_names,"failed_messages"=>$this->failed_messages);
		}
		
		
		/**
		 * display_errors function.
		 * 
		 * Returns a list of errors, split by $deliminator
		 *
		 * @access public
		 * @param string $deliminator. (default: "<br />")
		 * @return string
		 */
		public function display_errors($deliminator="<br />") {
			if ($this->passed) {
				return "";
			}
			return implode($deliminator,$this->failed_messages);
		}
		
		/**
		 * min_count function.
		 * 
		 * @access public
		 * @param string $value
		 * @param int $var
		 * @return boolean
		 */
		public function min_count($value, $var){
			if (is_array($value[0])) {
				$value=$value[0];
			}
			if(!$this->is_blank_array($value)){
				return (sizeof($value) >= $var);
			}else{
				return true;
			}
		}
		
		/**
		 * max_count function.
		 * 
		 * @access public
		 * @param string $value
		 * @param int $var
		 * @return boolean
		 */
		public function max_count($value, $var){
			if (is_array($value[0])) {
				$value=$value[0];
			}
			if(!$this->is_blank_array($value)){
				return (sizeof($value) <= $var);
			} else {
				return true;
			}
		}
		
		/**
		 * required function.
		 * 
		 * @access public
		 * @param string $value
		 * @param int $var. (default: false)
		 * @return boolean
		 */
		public function required($value,$var=false) {
			if (is_array($value)) {
				return (!$this->is_blank_array($value));
			} else {
				return (!empty($value));
			}
		}
		
		/**
		 * is_blank_array function.
		 * 
		 * @access public
		 * @param array $array
		 * @return boolean
		 */
		function is_blank_array($array){
			$check = "";
			if(is_array($array)){
				foreach($array as $item){
					if($item != ""){
						$check = $item;
					}
				}
				return ($check == "");
			}else{
				return (!empty($array));
			}

		}
		
		/**
		 * minlen function.
		 * 
		 * @access public
		 * @param string $value
		 * @param int $var
		 * @return boolean
		 */
		public function minlen($value,$var) {
			return (strlen($value) >= $var);
		}
		
		/**
		 * maxlen function.
		 * 
		 * @access public
		 * @param string $value
		 * @param int $var
		 * @return boolean
		 */
		public function maxlen($value,$var) {
			return (strlen($value) <= $var);
		}
		
		/**
		 * password_strength function.
		 * 
		 * @access public
		 * @param string $value
		 * @param int $var
		 * @return boolean
		 */
		public function password_strength($value,$var) {
			return ($this->check_password_strength($value) >= $var);
		}
		
		/**
		 * valid_email function.
		 * 
		 * @access public
		 * @param string $value
		 * @param bool $var. (default: false)
		 * @return boolean
		 */
		public function valid_email($value,$var=false) {
			if (empty($value)) {
				return true;
			}
			return ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $value)) ? FALSE : TRUE;
		}
		
		/**
		 * database_nodupe function.
		 * 
		 * Checks that there isn't a duplication in a table
		 *
		 * USAGE: database_nodupe={row} in {table}
		 *
		 * @access public
		 * @param string $value
		 * @param string $var
		 * @return bool
		 */
		public function database_nodupe($value,$var) {
			$dbdata=explode(" in ",strtolower($var));
			$result=$this->ci->db->get_where($dbdata[1],array($dbdata[0]=>$value));
			return ($result->num_rows()==0);
		}
		
		/**
	 	* Validate IP Address
	 	*
	 	* @access	public
		* @param	string
	 	* @return	bool
	 	*/
		public function valid_ip($ip,$var=false) {
			return $this->CI->input->valid_ip($ip);
		}
	
		/**
		* Alpha
	 	*
	 	* @access	public
	 	* @param	string
	 	* @return	bool
	 	*/		
		public function alpha($str,$var=false) {
			return ( ! preg_match("/^([a-z])+$/i", $str)) ? FALSE : TRUE;
		}
	
		/**
	 	* Alpha-numeric
	 	*
	 	* @access	public
	 	* @param	string
	 	* @return	bool
	 	*/	
		public function alpha_numeric($str,$var=false) {
			return ( ! preg_match("/^([a-z0-9])+$/i", $str)) ? FALSE : TRUE;
		}
	
		/**
	 	* Alpha-numeric with underscores and dashes
	 	*
	 	* @access	public
	 	* @param	string
	 	* @return	bool
	 	*/	
		public function alpha_numeric_dash($str,$var=false) {
			return ( ! preg_match("/^([-a-z0-9_-])+$/i", $str)) ? FALSE : TRUE;
		}
		
		/**
	 	* Alpha with underscores and dashes
	 	*
	 	* @access	public
	 	* @param	string
	 	* @return	bool
	 	*/	
		public function alpha_dash($str,$var=false) {
			return ( ! preg_match("/^([-a-z_-])+$/i", $str)) ? FALSE : TRUE;
		}
		
		/**
	 	* Alpha-numeric with underscores, dashes and spaces
	 	*
	 	* @access	public
	 	* @param	string
	 	* @return	bool
	 	*/	
		public function alpha_numeric_dash_space($str,$var=false) {
			return ( ! preg_match("/^([a-z0-9_-\s])+$/i", $str)) ? FALSE : TRUE;
		}
		
		/**
	 	* Alpha with underscores, dashes and spaces
	 	*
	 	* @access	public
	 	* @param	string
	 	* @return	bool
	 	*/	
		public function alpha_dash_space($str,$var=false) {
			return ( ! preg_match("/^([a-z_-\s])+$/i", $str)) ? FALSE : TRUE;
		}
	
		/**
	 	* Numeric
	 	*
	 	* @access	public
	 	* @param	string
	 	* @return	bool
	 	*/	
		public function numeric($str,$var=false) {
			return (bool)preg_match( '/^[\-+]?[0-9]*\.?[0-9]+$/', $str);
		}

    	/**
     	* Is Numeric
     	*
     	* @access    public
     	* @param    string
     	* @return    bool
     	*/
    	public function is_numeric($str,$var=false) {
	        return ( ! is_numeric($str)) ? FALSE : TRUE;
    	}
	
		/**
		 * Integer
		 *
		 * @access	public
		 * @param	string
		 * @return	bool
		 */	
		public function integer($str,$var=false) {
			return (bool)preg_match( '/^[\-+]?[0-9]+$/', $str);
		}
		
    	/**
    	 * Is a Natural number  (0,1,2,3, etc.)
    	 *
    	 * @access	public
    	 * @param	string
    	 * @return	bool
    	 */
    	public function is_natural($str,$var=false) {   
   			return (bool)preg_match( '/^[0-9]+$/', $str);
    	}
		
    	/**
    	 * Is a Natural number, but not a zero  (1,2,3, etc.)
    	 *
    	 * @access	public
    	 * @param	string
    	 * @return	bool
    	 */
		public function is_natural_no_zero($str,$var=false) {
    		if ( ! preg_match( '/^[0-9]+$/', $str)) {
    			return FALSE;
    		}
    		if ($str == 0) {
    			return FALSE;
    		}
   			return TRUE;
    	}
		
		/**
		 * Valid Base64
		 *
		 * Tests a string for characters outside of the Base64 alphabet
		 * as defined by RFC 2045 http://www.faqs.org/rfcs/rfc2045
		 *
		 * @access	public
		 * @param	string
		 * @return	bool
		 */
		public function valid_base64($str,$var=false) {
			return (bool) ! preg_match('/[^a-zA-Z0-9\/\+=]/', $str);
		}
		
		public function valid_url($url,$var=false) {
			return (preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url));
		}
		
		public function match($str,$var=false) {
			return $str==$var;
		}

		
		/**
		 * check_password_strength function.
		 * 
		 * Checks a password strength
		 *
		 * 1 = weak
		 * 2 = okay
		 * 3 = acceptable
		 * 4 = strong
		 *
		 * @access protected
		 * @param string $password
		 * @return integer
		 */
		protected function check_password_strength($password) {
		   $strength = 0;
		   $patterns = array('#[a-z]#','#[A-Z]#','#[0-9]#','/[Â!"£$%^&*()`{}\[\]:@~;\'#<>?,.\/\\-=_+\|]/');
		   foreach($patterns as $pattern) {
		       if(preg_match($pattern,$password,$matches)) {
	    	       $strength++;
	    	   }
	   		}
		   return $strength;
		}
		
		/**
		 * getmessage function.
		 * 
		 * @access protected
		 * @param string $rule
		 * @param string $name
		 * @param string $var. (default: false)
		 * @return string
		 */
		protected function getmessage($rule,$name,$var=false) {
			$s="";
			switch($rule) {
				case "required": $s="$name is required";
					break;
				case "required_list": $s="$name is required";
					break;
				case "min_count": $s="$name field must have at least $var item(s)";
					break;
				case "max_count": $s="$name field must have $var or less item(s)";
					break;
				case "minlen": $s="$name must be at least $var characters long";
					break;
				case "maxlen": $s="$name must be less than $var characters long";
					break;
				case "password_strength": $s="$name is a weak password";
					break;
				case "valid_email": $s="$name is not a valid email address";
					break;
				case "valid_ip": $s="$name is not a valid IP address";
					break;
				case "alpha": $s="$name can only contain alphabetical characters";
					break;
				case "alpha_numeric": $s="$name can only contain alpha-numeric characters";
					break;
				case "alpha_numeric_dash": $s="$name can only contain alpha-numeric characters, underscores and dashes";
					break;
				case "alpha_dash_space": $s="$name can only contain alphabetical characters, underscores, dashes and spaces";
					break;
				case "numeric": $s="$name can only contain numeric characters";
					break;
				case "is_numeric": $s="$name can only contain numeric characters";
					break;
				case "integer": $s="$name must be an integer";
					break;
				case "is_natural": $s="$name must be a natural number";
					break;
				case "is_natural_no_zero": $s="$name must be a natural number but not zero";
					break;
				case "valid_base64": $s="$name is not a valid base64";
					break;
				case "valid_url": $s="$name is not a valid url";
					break;
				case "database_nodupe": $s="$name already exists";
					break;
				case "match": $s="$name does not match";
					break;
				default: $s="There is an unknown problem with $name";
				
			}
			return $s;
		}
		
	}

?>