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
		if(!$_SESSION['user']['affiliate']){
			unset($_SESSION['user']);
			redirect_to(site_url());
		}
		else{
			unset($_SESSION['user']);
			redirect_to(site_url()."affiliates/");
		}
		
	}
	
	public function login(){
		if(strpos($_SERVER['HTTP_REFERER'], 'http://pieceoftheworld.com/admin2/affiliates')===0){
			$sql = "select * from `affiliates` where `email`= ".$this->db->escape($_POST['login_email'])." and `password`= '".$_POST['password']."'";
			$q = $this->db->query($sql);
			$r = $q->result_array();	
			if($r[0]){
				$_SESSION['user'] = $r[0];
				$_SESSION['user']['affiliate'] = true;
				redirect_to(site_url()."affiliates/");
			}
			else{
				redirect_to(site_url()."affiliates/?error=Invalid Login");
			}
		}
		else{
			$sql = "select * from `user` where `email`= ".$this->db->escape($_POST['login_email'])." and `password`= '".$_POST['password']."'";
			$q = $this->db->query($sql);
			$r = $q->result_array();	
			if($r[0]){
				unset($_SESSION['user']);
				$_SESSION['user'] = $r[0];
				redirect_to(site_url()."main");
			}
			else{
				redirect_to(site_url()."main/?error=Invalid Login");
			}
		}
		
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */