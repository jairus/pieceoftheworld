<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
session_start();
class tags extends CI_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->database();
	}
	public function index(){
		$start = $_GET['start'];
		$start += 0;
		$limit = 50;
				
		$sql = "select id, name, useCounter from `tags` where 1  order by useCounter desc limit $start, $limit";
		$export_sql = md5($sql);
		$_SESSION['export_sqls'][$export_sql] = $sql;
		$q = $this->db->query($sql);
		$records = $q->result_array();
				
		$sql = "select count(`id`) as `cnt` from `tags` where 1" ;
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
		$data['content'] = $this->load->view('tags/main', $data, true);
		$this->load->view('layout/main', $data);
	}		
	public function search(){
		$start = $_GET['start'];
		$filter = $_GET['filter'];
		$start += 0;
		$limit = 50;
		$search = strtolower(trim($_GET['search']));
		$searchx = trim($_GET['search']);
		
		$sql = "select id, name, useCounter from `tags` where 1 ";		
		if($filter=='id'){
			$sql .= "and id = '".mysql_real_escape_string($search)."' ";
		} elseif($filter=='useCounter'){
			$sql .= "and useCounter >= '".mysql_real_escape_string($search)."' ";		
		} elseif($search != ''){
			$sql .= "and LOWER(`".$filter."`) like '%".mysql_real_escape_string($search)."%'";
		}		
		$sql .= " order by useCounter desc limit $start, $limit " ;

		$export_sql = md5($sql);
		$_SESSION['export_sqls'][$export_sql] = $sql;
		$q = $this->db->query($sql);
		$records = $q->result_array();
				
		$sql = "select count(id) as `cnt`  from `tags` where 1 ";
		if($filter=='id'){
			$sql .= "and id = '".mysql_real_escape_string($search)."' ";
		} elseif($filter=='useCounter'){
			$sql .= "and useCounter >= '".mysql_real_escape_string($search)."' ";
		} elseif($search != ''){
			$sql .= "and LOWER(`".$filter."`) like '%".mysql_real_escape_string($search)."%'";
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
		$data['filter'] = $filter;
		$data['cnt'] = $cnt[0]['cnt'];
		$data['content'] = $this->load->view('tags/main', $data, true);
		$this->load->view('layout/main', $data);		
	}	
}
?>