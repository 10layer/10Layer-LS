<?php
	/**
	 * ExportDB class
	 *
	 * Exports the DB as separate files for each table to ./database/tables, and creates a database.sql for an entire import
	 * 
	 * @extends Controller
	 * @package 10Layer
	 * @subpackage Controllers
	 */
	class exportdb extends CI_Controller {

		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
		}
		
		public function index() {
			print "<h2>Exporting DB</h2>";
			$tables=$this->db->list_tables();
			$username=$this->db->username;
			$password=$this->db->password;
			$database=$this->db->database;
			exec("rm ./database/tables/*.sql");
			$s="-- 10Layer DB Dump\n--\n-- DATABASE: $database\n\n";
			
			foreach($tables as $table) {
				print "Creating ./database/{$table}.sql<br />\n";
				$cmd= "mysqldump --opt --compact -u{$username} -p{$password} {$database} {$table} > ./database/tables/{$table}.sql";
				exec($cmd);
				$s.="source tables/{$table}.sql\n";
			}
			file_put_contents("./database/10layer.sql",$s);
		}
	}

/* End of file exportdb.php */
/* Location: ./system/application/controllers/workers/ */