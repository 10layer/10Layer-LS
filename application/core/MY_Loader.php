<?php
	class MY_Loader EXTENDS CI_Loader {
		/**
		 * 10Layer Loader Class
		 *
		 * This class extends the loader to add some clever if_exists stuff
		 *
		 * @package		10Layer
		 * @subpackage	Libraries
		 * @category	Libraries
		 * @author		Jason Norwood-Young
		 * @link		http://10layer.com
		 */
		 public $package_paths=array();
		 
		
		/**
		 * Loads a view if it exists, else returns false
		 *
		 * @access	public
		 * @param	String	Path to view
		 * @return	boolean	True if it finds the view, false if it doesn't
		 */
		public function view_if_exists($path) {
			$ci=& get_instance();
			if ($ci->exists->view($path)) {
				$this->view($path);
				return true;
			}
			return false;
		}
		
		/**
		 * Finds a view based on the uri, using the segments from the last to the first, preceeded by $path
		 *
		 * @access	public
		 * @param	String	Path to view
		 * @param	String	Default view to load if we don't find the view we want
		 * @return	boolean	True if it finds the view, false if it doesn't
		 */
		public function view_by_uri($path,$default=false) {
			$ci=& get_instance();
			if ($path[strlen($path)-1]!=="/") {
				$path.="/";
			}
			$found=false;
			$segments=$ci->uri->segment_array();
			//print_r($segments);
			while(sizeof($segments)>0 && !$found) {
				$vname=$path.implode($segments);
				//print $vname;
				if ($ci->exists->view($vname)) {
					$found=true;
					$this->view($vname);
					break;
				}
				array_pop($segments);
			}
			return $found;
		}
		
		/**
		 * Add Package Path
		 *
		 * Prepends a parent path to the library, model, helper, and config path arrays
		 * CHANGE, JNY: Push packages onto the end of the list rather than the beginning
		 * CHANGE, JNY: Add Views
		 *
		 * @access	public
		 * @param	string
		 * @return	void
		 */
		function add_package_path($path)
		{
			$path = rtrim($path, '/').'/';
		
			array_push($this->_ci_library_paths, $path);
			array_push($this->_ci_model_paths, $path);
			array_push($this->_ci_helper_paths, $path);
			$this->_ci_view_paths[$path."views/"]=true;
			// Add config file path
			$config =& $this->_ci_get_component('config');
			array_push($config->_config_paths, $path);
			array_push($this->package_paths, $path);
		}
		
		// --------------------------------------------------------------------

	/**
	 * Autoloader
	 *
	 * The config/autoload.php file contains an array that permits sub-systems,
	 * libraries, and helpers to be loaded automatically.
	 *
	 * This function is public, as it's used in the CI_Controller class.  
	 * However, there is no reason you should ever needs to use it.
	 *
	 * @param	array
	 * @return	void
	 */
	public function ci_autoloader()
	{
		if (defined('ENVIRONMENT') AND file_exists(APPPATH.'config/'.ENVIRONMENT.'/autoload.php'))
		{
			include_once(APPPATH.'config/'.ENVIRONMENT.'/autoload.php');
		}
		else
		{
			include(APPPATH.'config/autoload.php');
		}
		

		if ( ! isset($autoload))
		{
			return FALSE;
		}

		// Autoload packages
		if (isset($autoload['packages']))
		{
			foreach ($autoload['packages'] as $package_path)
			{
				$this->add_package_path($package_path);
			}
		}

		// Load any custom config file
		if (count($autoload['config']) > 0)
		{
			$CI =& get_instance();
			foreach ($autoload['config'] as $key => $val)
			{
				$CI->config->load($val);
			}
		}

		// Autoload helpers and languages
		foreach (array('helper', 'language') as $type)
		{
			if (isset($autoload[$type]) AND count($autoload[$type]) > 0)
			{
				$this->$type($autoload[$type]);
			}
		}

		// A little tweak to remain backward compatible
		// The $autoload['core'] item was deprecated
		if ( ! isset($autoload['libraries']) AND isset($autoload['core']))
		{
			$autoload['libraries'] = $autoload['core'];
		}

		// Load libraries
		if (isset($autoload['libraries']) AND count($autoload['libraries']) > 0)
		{
			// Load the database driver.
			if (in_array('database', $autoload['libraries']))
			{
				$this->database();
				$autoload['libraries'] = array_diff($autoload['libraries'], array('database'));
			}

			// Load all other libraries
			foreach ($autoload['libraries'] as $item)
			{
				$this->library($item);
			}
		}

		// Autoload models
		if (isset($autoload['model']))
		{
			$this->model($autoload['model']);
		}
	}
		
	}

?>