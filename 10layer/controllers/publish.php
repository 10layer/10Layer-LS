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
			$data["options"] = $this->make_nested_list($this->make_nest($collection_type));
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
			$list = $this->mongo_db->where_in('content_type', $content_types)->order_by(array('start_date'=>'desc'))->limit(100)->get('content');
			$item['available_items'] = $list;
			echo json_encode($item);

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

			$menu  = "<ul class='menu'>";

			foreach($options as $option) {
				
				$menu .= "<li class='collection_selector' id='".$option->_id."' ><a>".$option->title."</a>";
					
					if (isset($option->submenu)) {
					
					$menu .= "<ul>";
					
					foreach($option->submenu as $sub) {
						$attrs = array('class'=>'collection_selector', 'id'=>$sub->_id);	
						$menu .= "<li class='collection_selector' id='".$sub->_id."'><a>".$sub->title."</a></li>";
					
					}
					$menu .= "</ul>";
					}
				$menu .= "</li>";
			}
			$menu .= "</ul>";
			return $menu;
		}

	}

/* End of file .php */
/* Location: ./system/application/controllers/ */