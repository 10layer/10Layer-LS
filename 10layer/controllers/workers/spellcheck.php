<?php
	/**
	 * Spellcheck Codeigniter class
	 * 
	 * Codeigniter class to use with TinyMCE Spellcheck plugin. Uses php5_enchant instead of php5_aspell.
	 *
	 * © Jason Norwood-Young, 10Layer Software Development
	 * jason@10layer.com
	 * http://10layer.com
	 *
	 * Licence: WTFPL 
	 * This program is free software. It comes without any warranty, to
	 * the extent permitted by applicable law. You can redistribute it
	 * and/or modify it under the terms of the Do What The Fuck You Want
	 * To Public License, Version 2, as published by Sam Hocevar. See
	 * http://sam.zoy.org/wtfpl/COPYING for more details.
	 *
	 * @extends Controller
	 * @package 10Layer
	 * @subpackage Controllers
	 */
	class Spellcheck extends CI_Controller {
	
		protected $broker;
		protected $dict;

		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
		}
		
		/**
		 * index function.
		 * 
		 * @access public
		 * @return void
		 */
		public function index() {
			$raw=file_get_contents("php://input");
			if (empty($raw)) {
				$output=array(
					"result"=>false,
					"id"=>false,
					"error"=>array(
						"errstr"=>"Oops, can't get info from browser",
						"errfile"=>"",
						"errline"=>false,
						"errcontext"=>"",
						"level"=>"FATAL"
					),
				);
				print json_encode($output);
				return true;
			}
			$data=json_decode($raw);
			if (method_exists($this, $data->method)) {
				$this->_initDict($data->params[0]);
				$result=call_user_func_array(array($this, $data->method), $data->params);
				$output = array(
					"id" => $data->id,
					"result" => $result,
					"error" => false,
				);
				$this->_closeDict();
				print json_encode($output);
				return true;
			} else {
				$output=array(
					"result"=>false,
					"id"=>false,
					"error"=>array(
						"errstr"=>"Oops, can't find method {$data->method}",
						"errfile"=>"",
						"errline"=>null,
						"errcontext"=>"",
						"level"=>"FATAL"
					)
				);
				print json_encode($output);
				return true;
			}
		}
				
		/**
		 * checkWords function.
		 * 
		 * @access protected
		 * @param String $lang
		 * @param Array $words
		 * @return Array Array of misspelt words
		 */
		protected function checkWords($lang, $words) {
			$result=array();
			foreach ($words as $word) {
				if (!enchant_dict_check($this->dict, trim($word)))
					$result[] = $word;
			}
			return $result;
		}

		/**
		 * getSuggestions function.
		 * 
		 * @access protected
		 * @param String $lang
		 * @param String $word
		 * @return Array Array of suggestions
		 */
		protected function getSuggestions($lang, $word) {
			return enchant_dict_suggest($this->dict, $word);
		}

		/**
		 * Opens a link to Enchant
		 */
		protected function _initDict($lang) {
			if (!function_exists("enchant_broker_init")) {
				$output=array(
					"result"=>false,
					"id"=>false,
					"error"=>array(
						"errstr"=>"Oops, Enchant is not installed",
						"errfile"=>"",
						"errline"=>null,
						"errcontext"=>"",
						"level"=>"FATAL"
					)
				);
				print json_encode($output);
				return true;
			}
			$this->broker = enchant_broker_init();
			if (!enchant_broker_dict_exists($this->broker, $lang)) {
				$output=array(
					"result"=>false,
					"id"=>false,
					"error"=>array(
						"errstr"=>"Oops, can't find method {$data->method}",
						"errfile"=>"",
						"errline"=>null,
						"errcontext"=>"",
						"level"=>"FATAL"
					)
				);
				print json_encode($output);
				return true;
			}
			$this->dict = enchant_broker_request_dict($this->broker, $lang);
			return true;
		}
		
		/**
		 * Closes a link to Enchant
		 */
		protected function _closeDict() {
			enchant_broker_free_dict($this->dict);
			enchant_broker_free($this->broker);
		}
		
	}

/* End of file spellcheck.php */
/* Location: ./system/application/controllers/ */