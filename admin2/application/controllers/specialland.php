<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
session_start();
class specialland extends CI_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->database();
	}
	public function index(){
		$start = $_GET['start'];
		$start += 0;
		$limit = 50;
		
		$sql = "select `x`, `y`, `id`, `title`, `detail`, `folder`, `picture`, `email_resent` from `land` where `land_special_id`  is not NULL order by `folder` desc limit $start, $limit" ;
		$export_sql = md5($sql);
		$_SESSION['export_sqls'][$export_sql] = $sql;
		$q = $this->db->query($sql);
		$records = $q->result_array();
		
		$sql = "select count(`id`) as `cnt` from `land` where `land_special_id` is not NULL order by `folder` desc" ;
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
		$data['content'] = $this->load->view('specialland/main', $data, true);
		$this->load->view('layout/main', $data);
	}
	
	
	public function search(){
		$start = $_GET['start'];
		$filter = $_GET['filter'];
		$start += 0;
		$limit = 50;
		$search = strtolower(trim($_GET['search']));
		$searchx = trim($_GET['search']);
		
		$sql = "select * from `land` where ";
		if($filter=='all' || !trim($filter)){
			$sql .= "
				LOWER(`name`) like '%".mysql_real_escape_string($search)."%' or
				LOWER(`email_address`) like '%".mysql_real_escape_string($search)."%' or
				LOWER(`twitter_username`) like '%".mysql_real_escape_string($search)."%' or
				LOWER(`website`) like '%".mysql_real_escape_string($search)."%' or
				LOWER(`blog_url`) like '%".mysql_real_escape_string($search)."%' or 
				LOWER(`facebook`) like '%".mysql_real_escape_string($search)."%' or 
				LOWER(`linkedin`) like '%".mysql_real_escape_string($search)."%' or 
				LOWER(`description`) like '%".mysql_real_escape_string($search)."%' or 
				LOWER(`tags`) like '%".mysql_real_escape_string($search)."%' or
				LOWER(`country`) like '%".mysql_real_escape_string($search)."%'
			";
		}
		else if($filter=='status'){
			$sql .= "
				LOWER(`status`) like '%".mysql_real_escape_string($search)."%'
			";
		}
		else{
			$sql .= "LOWER(`".$filter."`) like '%".mysql_real_escape_string($search)."%'";
		}
		$sql .= "order by `name` asc limit $start, $limit" ;
		$export_sql = md5($sql);
		$_SESSION['export_sqls'][$export_sql] = $sql;
		$q = $this->db->query($sql);
		$companies = $q->result_array();
		
		$sql = "select count(id) as `cnt` from `companies` where ";
		if($filter=='all' || !trim($filter)){
			$sql .= "
				LOWER(`name`) like '%".mysql_real_escape_string($search)."%' or
				LOWER(`email_address`) like '%".mysql_real_escape_string($search)."%' or
				LOWER(`twitter_username`) like '%".mysql_real_escape_string($search)."%' or
				LOWER(`website`) like '%".mysql_real_escape_string($search)."%' or
				LOWER(`blog_url`) like '%".mysql_real_escape_string($search)."%' or 
				LOWER(`facebook`) like '%".mysql_real_escape_string($search)."%' or 
				LOWER(`linkedin`) like '%".mysql_real_escape_string($search)."%' or 
				LOWER(`description`) like '%".mysql_real_escape_string($search)."%' or 
				LOWER(`tags`) like '%".mysql_real_escape_string($search)."%' or
				LOWER(`country`) like '%".mysql_real_escape_string($search)."%'
			";
		}
		else{
			$sql .= "LOWER(`".$filter."`) like '%".mysql_real_escape_string($search)."%'";
		}
		$sql .= "order by `name` asc" ;
		
		$q = $this->db->query($sql);
		$cnt = $q->result_array();
		$pages = ceil($cnt[0]['cnt']/$limit);
		
		$data = array();
		$data['companies'] = $companies;
		$data['export_sql'] = $export_sql;
		$data['pages'] = $pages;
		$data['start'] = $start;
		$data['limit'] = $limit;
		$data['search'] = $searchx;
		$data['filter'] = $filter;
		$data['cnt'] = $cnt[0]['cnt'];
		$data['content'] = $this->load->view('companies/main', $data, true);
		$this->load->view('layout/main', $data);
	}
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */