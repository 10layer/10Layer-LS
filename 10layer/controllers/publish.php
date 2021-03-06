<?php
	/**
	 * Publish class
	 * 
	 * @extends Controller
	 */
	class Publish extends CI_Controller {

		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
			$this->load->model('Model_collections', 'collection');
		}


		public function _remap() {
			$collection_type=$this->uri->segment(2);
			if(method_exists($this, $collection_type)){
				$query=$this->uri->segment(3);
				$this->$collection_type($query);
			}else{
				$this->load_publisher($collection_type);
			}
			
			
		}

		public function load_publisher($collection_type){
			$data["collection_id"] = $this->uri->segment(3);
			$data["collection"]=$this->model_collections->get($data["collection_id"]);
			if (empty($data["collection"])) {
				show_404();
			}
			$data["collections"] = $this->model_collections->get_options($collection_type);
			$data["options"] = $this->make_nested_list($this->make_nest($collection_type));
			$data['collection_type'] = $collection_type;
			$this->load->view('templates/header',$data);
			$this->load->view("publish/interface");
			$this->load->view("templates/footer");
		}

		function get_collection($collection_id){
			$item = $this->mongo_db->get_light($collection_id);
			if(isset($item['zones']) AND sizeof($item['zones']) > 0){
				$zones = array();
				foreach ($item['zones'] as $zone) {
					array_push($zones, $this->mongo_db->get_light($zone));
				}
				$item['zones'] = $zones;
			}
			echo json_encode($item);
		}

		function get_zone($zone_id){
			$item = $this->mongo_db->get_light($zone_id);
			$content_types = explode(',', $item['content_types']);
			$content = array();
			$temp = array();
			if(isset($item['content'])){
				$content = $item['content'];
				foreach ($item['content'] as $an_item) {
					array_push($temp, $this->mongo_db->get_light($an_item));
				}
			}
			
			$item['content'] = $temp;

			$list = '';

			if ($this->input->get('criteria')) {
				
				if($this->input->get('start_date')){
					$start_date = strtotime($this->input->get('start_date'));
					$this->mongo_db->where_gte('start_date', $start_date);
				}

				if($this->input->get('end_date')){
					$end_date = strtotime($this->input->get('end_date'));
					$this->mongo_db->where_lte('start_date', $end_date);
				}
				
				if($this->input->get('searchstr')){
					$search_str = $this->input->get('searchstr');
					$this->mongo_db->like('title', $search_str);
				}

				$list = $this->mongo_db->where_in('content_type', $content_types)->order_by(array('start_date'=>'desc'))->limit(100)->get('content');
			}else{

				$list = $this->mongo_db->where_in('content_type', $content_types)->where_not_in('_id', $content)->order_by(array('start_date'=>'desc'))->limit(100)->get('content');	
			}
			
			$item['available_items'] = $list;
			echo json_encode($item);

		}

		function save_zone_content($zone_id){
			$item = $this->mongo_db->get_light($zone_id);
			unset($item['_id']);
			$item['content'] = $this->input->get('published');
			$where = array('_id' => $zone_id);
			$this->mongo_db->where($where)->update('content', $item);
		}

		function auto_switch(){
			$item['auto'] = $this->input->get('switch');
			$zone_id = $this->input->get('zone_id');

			if($this->input->get('switch') == 1){
				$this->automate($zone_id);
			}
			$where = array('_id' => $zone_id);
			$this->mongo_db->where($where)->update('content', $item);

			

			$item = $this->mongo_db->get_light($zone_id);

			$content = array();
			$temp = array();
			if(isset($item['content'])){
				$content = $item['content'];
				foreach ($item['content'] as $an_item) {
					array_push($temp, $this->mongo_db->get_light($an_item));
				}
			}

			$item['content'] = $temp;
			$content_types = explode(',', $item['content_types']);
			$item['available_items'] = $this->mongo_db->where_in('content_type', $content_types)->where_not_in('_id', $content)->order_by(array('start_date'=>'desc'))->limit(100)->get('content');



			$results = array('info' => 'results','message' => 'Auto switch succesful', 'item' => $item);	
			echo json_encode($results);
		}

		function automate($zone_id){
			$item = $this->mongo_db->get_light($zone_id);
			$content_types = explode(',', $item['content_types']);
			$list = $this->mongo_db->where(array('section' => $item['section']))->where_in('content_type', $content_types)->order_by(array('start_date'=>'desc'))->limit($item['auto_limit'])->get('content');
			$content = array();
			foreach($list as $i){
				array_push($content, $i->_id);
			}
			$item['content'] = $content;
			unset($item['_id']);
			$where = array('_id' => $zone_id);
			$this->mongo_db->where($where)->update('content', $item);
		}


		function make_nest($collection_type){

				$options=$this->model_collections->get_options($collection_type);
				
				//Here we check if we have nested collections
				$tmp=array();
				foreach($options as $option) {
					if (!isset($option->{$collection_type})) {
						$tmp[$option->_id]=$option;
					}
				}
				foreach($options as $option) {
					if (isset($option->{$collection_type}) && is_string($option->{$collection_type})) {
						if (array_key_exists($option->{$collection_type}, $tmp)) {
							$tmp[$option->{$collection_type}]->submenu[]=$option;
						}
					}
				}
				$options=$tmp;
				return $options;
		}

		function make_nested_list($options){

			$menu  = "<div class='dropdown clearfix'>
                <ul class='dropdown-menu' role='menu' aria-labelledby='dropdownMenu' style='display: block; position: static; margin-bottom: 5px; *width: 180px;'>";



			foreach($options as $option) {

				if (isset($option->submenu)) {
					$menu .= "<li class='dropdown-submenu'>
                    			<a tabindex='-1' href='#'>".$option->title."</a>
                    			<ul class='dropdown-menu'>";
                    			foreach($option->submenu as $sub) {
                    				$menu .= "<li ><a id='".$sub->_id."' class='collection_selector clickable' tabindex='-1' href='#'>".$sub->title."</a></li>";
                    			}
                    			$menu .='</ul>';
				}else{
					$menu .= "<li><a tabindex='-1' id='".$option->_id."' class='collection_selector clickable'>".$option->title."</a></li>";
				}
			}

			$menu .= '</ul></div>';

			return $menu;
		}


		function fix_dates(){
			$count = $this->mongo_db->count('content');
			$batches = round($count/1000);
			echo $batches;

			for ($i=0; $i < $batches ; $i++) { 
				$offset = $i*1000;
				$batch = $this->mongo_db->limit(1000,$offset)->get('content');
				foreach($batch as $item){
					//echo date('Y-m-d', $item->start_date).'<br />';

					$update['start_date'] = strtotime($item->start_date);
					$update['end_date'] = strtotime($item->end_date);
					$update['last_modified'] = strtotime($item->last_modified);
					$update['timestamp'] = strtotime($item->timestamp);

					$where = array('_id' => $item->_id);
					$this->mongo_db->where($where)->update('content', $update);
				}
			}
			echo 'done';
		}


	}


/* End of file .php */
/* Location: ./system/application/controllers/ */