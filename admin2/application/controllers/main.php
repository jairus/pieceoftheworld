<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
session_start();
class main extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->database();
	}
	public function index(){
		$this->load->view('layout/main');
	}
	
	public function logout(){
		unset($_SESSION['user']);
		redirect_to(site_url());
	}
	
	public function login(){
		$sql = "select * from `user` where `email`= ".$this->db->escape($_POST['login_email'])." and `password`= '".$_POST['password']."'";
		$q = $this->db->query($sql);
		$r = $q->result_array();			
		
		if($r[0]){
			$_SESSION['user'] = $r[0];
			redirect_to(site_url()."main");
		}
		else{
			redirect_to(site_url()."main/?error=Invalid Login");
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */