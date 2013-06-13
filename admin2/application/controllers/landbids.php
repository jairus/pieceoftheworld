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
		
		$sql = "SELECT `a`.*, `b`.`title` FROM `land_bids` `a` 
				LEFT JOIN `land_detail` `b` ON `b`.`id`=`a`.`land_id` 
				ORDER BY `a`.`bid` DESC LIMIT $start, $limit 
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
		
		$sql = "SELECT `a`.*, `b`.`title` FROM `land_bids` `a` 
				LEFT JOIN `land_detail` `b` ON `b`.`id`=`a`.`land_id` 
				WHERE 1";
		
		if($search){
			$sql .= " AND `a`.`land_id` LIKE '%".mysql_real_escape_string($search)."%'";
		}
		$sql .= " ORDER BY `a`.`bid` DESC LIMIT $start, $limit";
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
	
	public function ajax_delete($landBidId=""){
		if(!$_SESSION['user']){
			return false;
		}
		if(!$landBidId){
			$landBidId = $_POST['id'];
		}
		
		$sql = "delete from land_bids where id = ".$this->db->escape($landBidId)." limit 1";
		$q = $this->db->query($sql);
		?>
		alertX("Successfully deleted.");
		<?php
		exit();
	}
	
	function ajax_edit(){
		$table = "landbids";
		$controller = $table;
		$error = false;
		if(!trim($_POST['user_bid'])){
			?>alertX("Please input user bid!");<?php
			$error = true;
		}	
		
		if(!$error){
			$fieldUpdateSql = 
			"`bid` = '".mysql_real_escape_string($_POST['user_bid'])."',
			`message` = '".mysql_real_escape_string($_POST['user_message'])."'";
			
			$sql = "update `land_bids` set $fieldUpdateSql where `id` = '".$_POST['id']."' limit 1";
			$this->db->query($sql);
			?>
			alertX("Successfully Updated Land Bid: '<?php echo htmlentitiesX($_POST['user_bid']); ?>'.");
			<?php
		}
		?>jQuery("#record_form *").attr("disabled", false);<?php
	}
	
	public function edit($id){
		$table = "landbids";
		$controller = $table;
		$sql = "select * from `land_bids` where `id` = '".mysql_real_escape_string($id)."' limit 1";
		$q = $this->db->query($sql);
		$record = $q->result_array();
		$record = $record[0];
        $data['record'] = $record;

		$data['content'] = $this->load->view($controller.'/add', $data, true);
		
		$this->load->view('layout/main', $data);;
	}
}
?>
