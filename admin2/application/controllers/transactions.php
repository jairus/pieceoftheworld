<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
session_start();
class transactions extends CI_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->database();
	}
	public function index(){
		$start = $_GET['start'];
		$start += 0;
		$limit = 50;
		
		$sql = "SELECT T.*, W.name, W.useremail
				FROM transactions T
				LEFT JOIN web_users W on W.id = T.web_user_id
				ORDER BY T.dateCreated desc
				LIMIT $start, $limit 
				";
		$export_sql = md5($sql);
		$_SESSION['export_sqls'][$export_sql] = $sql;
		$q = $this->db->query($sql);
		$records = $q->result_array();
		
		$sql = "select count(`id`) as `cnt` from `transactions` " ;
		$q = $this->db->query($sql);
		$cnt = $q->result_array();
		$pages = ceil($cnt[0]['cnt']/$limit);
	
		// get total amount
		$sql = "select sum(totalAmount) as amount from transactions ";
		$q = $this->db->query($sql);
		$rs = $q->result_array();
		$amountSum = $rs[0]['amount'];
		
		$data = array();
		$data['records'] = $records;
		$data['export_sql'] = $export_sql;
		$data['pages'] = $pages;
		$data['start'] = $start;
		$data['limit'] = $limit;
		$data['cnt'] = $cnt[0]['cnt'];
		$data['amountSum'] = $amountSum;
		$data['content'] = $this->load->view('transactions/main', $data, true);		
		$this->load->view('layout/main', $data);
	}	
	
	public function search(){
		$data = array();
		$start = $_GET['start'];
		$start += 0;
		$limit = 50;
				
		$sql = "SELECT T.*, W.name, W.useremail 
				FROM transactions T
				LEFT JOIN web_users W on W.id = T.web_user_id				
				WHERE 1";
		
		$sqlWhere = '';
		if(isset($_POST['startDate']) && $_POST['startDate']){
			$sqlWhere .= " AND date(T.dateCreated) >= '".date_format(date_create_from_format('m/d/Y', $_POST['startDate']), 'Y-m-d')."'";
			$data['startDate'] = $_POST['startDate'];
		}
		if(isset($_POST['endDate']) && $_POST['endDate']){
			$sqlWhere .= " AND date(T.dateCreated) <= '".date_format(date_create_from_format('m/d/Y', $_POST['endDate']), 'Y-m-d')."'";
			$data['endDate'] = $_POST['endDate'];
		}
		
		$sql .= $sqlWhere . " ORDER BY T.dateCreated desc LIMIT $start, $limit";
		$export_sql = md5($sql);
		$_SESSION['export_sqls'][$export_sql] = $sql;
		$q = $this->db->query($sql);
		$records = $q->result_array();
		
		$sql = "SELECT COUNT(`T`.`id`) AS `cnt` 
				FROM `transactions` `T` 				
				WHERE 1 $sqlWhere ";
		$q = $this->db->query($sql);
		$cnt = $q->result_array();
		$pages = ceil($cnt[0]['cnt']/$limit);

		// get total amount
		$sql = "select sum(totalAmount) as amount from transactions T WHERE 1 $sqlWhere ";
		$q = $this->db->query($sql);
		$rs = $q->result_array();
		$amountSum = $rs[0]['amount'];
		
		
		$data['records'] = $records;		
		$data['export_sql'] = $export_sql;
		$data['pages'] = $pages;
		$data['start'] = $start;
		$data['limit'] = $limit;
		$data['search'] = $searchx;
		$data['cnt'] = $cnt[0]['cnt'];
		$data['amountSum'] = $amountSum;
		$data['content'] = $this->load->view('transactions/main', $data, true);		
		$this->load->view('layout/main', $data);		
	}	
}
?>
