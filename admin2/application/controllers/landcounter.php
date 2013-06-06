<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
session_start();
class landcounter extends CI_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->database();
	}
	public function index(){
		$start = $_GET['start'];
		$start += 0;
		$limit = 50;
				
		$sql = "select LV.* , L.land_special_id, L.land_detail_id, L.web_user_id, format(viewCtr, 0) as viewCtr, LD.title
				from `land_view` LV 
				left join land L on LV.land_id = L.id 
				left join land_detail LD on L.land_detail_id = LD.id 
				order by LV.`viewCtr` desc limit $start, $limit";
		$export_sql = md5($sql);
		$_SESSION['export_sqls'][$export_sql] = $sql;
		$q = $this->db->query($sql);
		$records = $q->result_array();
		
		//$sql = "select count(`id`) as `cnt` from `land` where `land_special_id` is NULL order by `folder` desc" ;
		$sql = "select count(`id`) as `cnt` from `land_view` " ;
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
		$data['content'] = $this->load->view('landcounter/main', $data, true);
		$this->load->view('layout/main', $data);
	}
	
	
	public function search(){
		$start = $_GET['start'];
		$filter = $_GET['filter'];
		$start += 0;
		$limit = 50;
		$search = strtolower(trim($_GET['search']));
		$searchx = trim($_GET['search']);
		
		$sql = "select LV.* , L.land_special_id, L.land_detail_id, L.web_user_id, format(viewCtr, 0) as viewCtr
				from `land_view` LV 
				left join land L on LV.land_id = L.id				
				where 1";
		
		if($filter=='sold'){
			$searchV = ($search == 'yes')? " != 0" : " = 0 ";
			$sql .= " and L.web_user_id $searchV ";
		}
		elseif($search){
			$sql .= " and LOWER(LV.".$filter.") like '%".mysql_real_escape_string($search)."%'";
		}
		$sql .= " order by LV.`viewCtr` desc limit $start, $limit";
		$export_sql = md5($sql);
		$_SESSION['export_sqls'][$export_sql] = $sql;
		$q = $this->db->query($sql);
		$records = $q->result_array();
		
		$sql = "select count(LV.id) as `cnt`
				from `land_view` LV 
				left join land L on LV.land_id = L.id				
				where 1 ";
		if($filter=='sold'){
			$searchV = ($search == 'yes')? " != 0" : " = 0 ";
			$sql .= " and L.web_user_id $searchV ";
		}
		elseif($search){
			$sql .= " and LOWER(LV.".$filter.") like '%".mysql_real_escape_string($search)."%'";
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
		$data['content'] = $this->load->view('landcounter/main', $data, true);
		$this->load->view('layout/main', $data);		
	}	
}
?>
