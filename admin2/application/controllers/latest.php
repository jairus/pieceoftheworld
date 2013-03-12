<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class latest extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->database();
	}
	
	public function index(){
		$data = array();
		//$sql = "select `a`.*, `b`.`login` as `username` from `logs` as `a` left join `users` as `b` on (`a`.`user_id`=`b`.`id`) where 1 order by `id` desc limit 100 " ;
		//$q = $this->db->query($sql);
		//$logs = $q->result_array();
		$logs = array();
		$data['logs'] = $logs;
		$data['content'] = $this->load->view('latest/main', $data, true);
		$this->load->view('layout/main', $data);
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */