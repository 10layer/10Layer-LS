<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
/**
 * Exists class.
 * 
 * Checks if a CodeIgniter thing exists before trying to load it
 * Also searches through Packages.
 * 
 * @package 10Layer
 * @subpackage Libraries
 */
class Exists {

	/**
	 * _docheck function.
	 * 
	 * @access protected
	 * @param string $directory
	 * @param string $file
	 * @param string $ext. (default: ".php")
	 * @return boolean
	 */
	protected function _docheck($directory, $file, $ext=".php") {
		$path = APPPATH.$directory.'/'.$file.$ext;
		if (file_exists($path) || file_exists(strtolower($path))) {
			return file_exists($path) || file_exists(strtolower($path));
		}
		
		// Check packages
		// Code from Loader core
		if (defined('ENVIRONMENT') AND file_exists(APPPATH.'config/'.ENVIRONMENT.'/autoload.php')) {
			include(APPPATH.'config/'.ENVIRONMENT.'/autoload.php');
		} else {
			include(APPPATH.'config/autoload.php');
		}
		if (isset($autoload['packages'])) {
			foreach ($autoload['packages'] as $package_path) {
				$path=$package_path."/".$directory.'/'.$file.$ext;
					if (file_exists($path) || file_exists(strtolower($path))) {
						if (file_exists($path) || file_exists(strtolower($path))) {
							return $path;
						}
					}
				}
			}
		return false;
	}

	/**
	 * view function.
	 * 
	 * Checks if a View exists
	 *
	 * @access public
	 * @param string $view
	 * @return boolean
	 */
	public function view($view) {
		return $this->_docheck('views', $view);
	}
	
	/**
	 * controller function.
	 * 
	 * Checks if a Controller exists
	 *
	 * @access public
	 * @param string $controller
	 * @return boolean
	 */
	public function controller($controller) {
		return $this->_docheck('controllers', $controller);
	}
	
	/**
	 * model function.
	 * 
	 * Checks if a Model exists
	 *
	 * @access public
	 * @param string $model
	 * @return boolean
	 */
	public function model($model) {
		return $this->_docheck('models', $model);
	}
	
	/**
	 * library function.
	 * 
	 * Checks if a Library exists
	 *
	 * @access public
	 * @param string $library
	 * @return boolean
	 */
	public function library($library) {
		return $this->_docheck('libraries', $library);
	}
	
	/**
	 * helper function.
	 * 
	 * Checks if a Helper exists
	 *
	 * @access public
	 * @param string $helper
	 * @return boolean
	 */
	public function helper($helper) {
		return $this->_docheck('helpers', $helper);
	}
	
	/**
	 * stylesheet function.
	 * 
	 * Checks if a CSS file exists (under resources/css)
	 *
	 * @access public
	 * @param string stylesheet
	 * @return boolean
	 */
	public function stylesheet($stylesheet) {
		return $this->_docheck("./resources/css/",$stylesheet,".css");
	}
	
	/**
	 * javascript function.
	 * 
	 * Checks if a JS file exists (under resources/js)
	 *
	 * @access public
	 * @param string $javascript
	 * @return boolean
	 */
	public function javascript($javascript) {
		return file_exists("./resources/js/".$javascript.".js");
	}
}