<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
session_start();
class landbids extends CI_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->database();
	}
	public function index(){
		$start = $_GET['start'];
		$start += 0;
		$limit = 50;
		
		$sql = "SELECT `a`.*, `c`.`title` FROM `land_bids` `a` 
				LEFT JOIN `land` `b` ON `a`.`land_id`=`b`.`id` 
				LEFT JOIN `land_detail` `c` ON `b`.`land_detail_id`=`c`.`id` 
				ORDER BY `bid` DESC LIMIT $start, $limit 
				";
		$export_sql = md5($sql);
		$_SESSION['export_sqls'][$export_sql] = $sql;
		$q = $this->db->query($sql);
		$records = $q->result_array();
		
		$sql = "select count(`id`) as `cnt` from `land_bids` " ;
		$q = $this->db->query($sql);
		$cnt = $q->result_array();
		$pages = ceil($cnt[0]['cnt']/$limit);
		
		$data = array();
		$data['records'] = $records;
		$data['export_sql'] = $export_sql;
		$data['pages'] = $pages;
		$data['start'] = $start;
		$data['limit'] = $limit;
		$data['cnt'] = $cnt[0]['cnt'];
		$data['content'] = $this->load->view('landbids/main', $data, true);
		$this->load->view('layout/main', $data);
	}
	
	
	public function search(){
		$start = $_GET['start'];
		$start += 0;
		$limit = 50;
		$search = strtolower(trim($_GET['search']));
		$searchx = trim($_GET['search']);
		
		$sql = "SELECT `a`.*, `c`.`title` FROM `land_bids` `a` 
				LEFT JOIN `land` `b` ON `a`.`land_id`=`b`.`id` 
				LEFT JOIN `land_detail` `c` ON `b`.`land_detail_id`=`c`.`id` 
				WHERE 1";
		
		if($search){
			$sql .= " AND `a`.`land_id` LIKE '%".mysql_real_escape_string($search)."%'";
		}
		$sql .= " ORDER BY `bid` DESC LIMIT $start, $limit";
		$export_sql = md5($sql);
		$_SESSION['export_sqls'][$export_sql] = $sql;
		$q = $this->db->query($sql);
		$records = $q->result_array();
		
		$sql = "SELECT COUNT(`a`.`id`) AS `cnt` FROM `land_bids` `a` 
				LEFT JOIN `land` `b` ON `a`.`land_id`=`b`.`id` 
				WHERE 1";
		
		if($search){
			$sql .= " AND `a`.`land_id` LIKE '%".mysql_real_escape_string($search)."%'";
		}	
		$q = $this->db->query($sql);
		$cnt = $q->result_array();
		$pages = ceil($cnt[0]['cnt']/$limit);
		
		$data = array();
		$data['records'] = $records;		
		$data['export_sql'] = $export_sql;
		$data['pages'] = $pages;
		$data['start'] = $start;
		$data['limit'] = $limit;
		$data['search'] = $searchx;
		$data['cnt'] = $cnt[0]['cnt'];
		$data['content'] = $this->load->view('landbids/main', $data, true);
		$this->load->view('layout/main', $data);		
	}	
}
?>
