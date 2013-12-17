<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
session_start();
class affiliates extends CI_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->database();
	}
	public function index(){
		if($_SESSION['user']['affiliate']){
			$this->edit($_SESSION['user']['id']);
			return 0;
		}
		$table = "affiliates";
		$controller = $table;
		
		$start = $_GET['start'];
		$start += 0;
		$limit = 50;
		
		$sql = "select * from `".$table."` where 1 order by `id` desc limit $start, $limit" ;
		$export_sql = md5($sql);
		$_SESSION['export_sqls'][$export_sql] = $sql;
		$q = $this->db->query($sql);
		$records = $q->result_array();
		
		$t = count($records);
		for($i=0; $i<$t; $i++){
			$sql = "select count(`id`) as `cnt` from `affiliate_clicks` where `affiliate_id`='".$records[$i]['id']."'";
			$q = $this->db->query($sql);
			$cnt = $q->result_array();
			$cnt = $cnt[0]['cnt'];
			$records[$i]['clicks'] = $cnt;
			
			$sql = "select sum(`commission`) as `commission` from `affiliate_commissions` where `affiliate_id`='".$records[$i]['id']."'";
			$q = $this->db->query($sql);
			$cnt = $q->result_array();
			$commission = $cnt[0]['commission'];
			$records[$i]['commission'] = $commission;
		}
		
		$sql = "select count(`id`) as `cnt` from `".$table."` where 1 order by `id` desc" ;
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
		$data['content'] = $this->load->view($controller.'/main', $data, true);
		$this->load->view('layout/main', $data);
	}
	
	
	public function search(){
		$table = "affiliates";
		$controller = $table;
		
		$start = $_GET['start'];
		$filter = $_GET['filter'];
		$start += 0;
		$limit = 50;
		$search = strtolower(trim($_GET['search']));
		$searchx = trim($_GET['search']);
		
		$sql = "select * from `".$table."` where ";
		if($filter=='all' || !trim($filter)){
			$sql .= "
				LOWER(`id`) like '%".mysql_real_escape_string($search)."%' or
				LOWER(`title`) like '%".mysql_real_escape_string($search)."%' or
				LOWER(`email`) like '%".mysql_real_escape_string($search)."%' or
				LOWER(`website`) like '%".mysql_real_escape_string($search)."%' or
				LOWER(`detail`) like '%".mysql_real_escape_string($search)."%'
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
		$sql .= "order by `title` asc limit $start, $limit" ;
		$export_sql = md5($sql);
		$_SESSION['export_sqls'][$export_sql] = $sql;
		$q = $this->db->query($sql);
		$companies = $q->result_array();
		
		$sql = "select count(id) as `cnt` from `".$table."` where ";
		if($filter=='all' || !trim($filter)){
			$sql .= "
				LOWER(`id`) like '%".mysql_real_escape_string($search)."%' or
				LOWER(`title`) like '%".mysql_real_escape_string($search)."%' or
				LOWER(`email`) like '%".mysql_real_escape_string($search)."%' or
				LOWER(`website`) like '%".mysql_real_escape_string($search)."%' or
				LOWER(`detail`) like '%".mysql_real_escape_string($search)."%'
			";
		}
		else{
			$sql .= "LOWER(`".$filter."`) like '%".mysql_real_escape_string($search)."%'";
		}
		$sql .= "order by `title` asc" ;
		
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
		$data['content'] = $this->load->view($controller.'/main', $data, true);
		$this->load->view('layout/main', $data);
	}
	
	public function add(){
		$table = "affiliates";
		$controller = $table;
		$data['content'] = $this->load->view($controller.'/add', $data, true);
		$this->load->view('layout/main', $data);;
	}
	
	function ajax_add(){
		$table = "affiliates";
		$controller = $table;
		$error = false;
		if(!trim($_POST['title'])){
			?>alertX("Please input affiliate name!");<?php
			$error = true;
		}
		else if(!trim($_POST['website'])){
			?>alertX("Please input affiliate website!");<?php
			$error = true;
		}
		else if(!trim($_POST['commissionrate'])){
			?>alertX("Please input commission rate!");<?php
			$error = true;
		}
		
		if(!$error){
			$sql = "insert into `$table` set 
				`title` = '".mysql_real_escape_string($_POST['title'])."',
				`detail` = '".mysql_real_escape_string($_POST['detail'])."',
				`website` = '".mysql_real_escape_string($_POST['website'])."',
				`email` = '".mysql_real_escape_string($_POST['email'])."',
				`active` = '".mysql_real_escape_string($_POST['active'])."',
				`coupon` = '".mysql_real_escape_string($_POST['coupon'])."',
				`commissionrate` = '".mysql_real_escape_string($_POST['commissionrate'])."',
				`dateadded` = NOW()
			";
			$this->db->query($sql);
			$insert_id = $this->db->insert_id();
		
			?>
			alertX("Successfully Added Affiliate '<?php echo htmlentitiesX($_POST['title']); ?>'.");
			self.location = "<?php echo site_url(); echo $controller; ?>/add";
			<?php
		}
		?>jQuery("#record_form *").attr("disabled", false);<?php
	}
	
	
	function ajax_delete($id){
		if(!$_SESSION['user']['affiliate']){
			$sql = "delete from `affiliates` where `id`='".mysql_real_escape_string($id)."'";
			$this->db->query($sql);
		}
		//$sql = "delete from `startupkit_product_coupons` where `product_id`='".mysql_real_escape_string($id)."'";
		//$this->db->query($sql);
	}
	
	function ajax_edit(){
		$table = "affiliates";
		$controller = $table;
		$error = false;
		if(!$_SESSION['user']['affiliate']){
			if(!trim($_POST['title'])){
				?>alertX("Please input affiliate name!");<?php
				$error = true;
			}
			else if(!trim($_POST['website'])){
				?>alertX("Please input affiliate website!");<?php
				$error = true;
			}
			else if(!trim($_POST['commissionrate'])){
				?>alertX("Please input commission rate!");<?php
				$error = true;
			}
		}
		
		if(!$error){
			if(!$_SESSION['user']['affiliate']){
				$sql = "update `$table` set 
					`title` = '".mysql_real_escape_string($_POST['title'])."',
					`detail` = '".mysql_real_escape_string($_POST['detail'])."',
					`website` = '".mysql_real_escape_string($_POST['website'])."',
					`email` = '".mysql_real_escape_string($_POST['email'])."',
					`password` = '".mysql_real_escape_string($_POST['password'])."',
					`active` = '".mysql_real_escape_string($_POST['active'])."',
					`coupon` = '".mysql_real_escape_string($_POST['coupon'])."',
					`commissionrate` = '".mysql_real_escape_string($_POST['commissionrate'])."',
					`discountrate` = '".mysql_real_escape_string($_POST['discountrate'])."',
					`dateadded` = NOW()
					where `id` = '".mysql_real_escape_string($_POST['id'])."'
				";
				$this->db->query($sql);
				if(!$_SESSION['user']['affiliate']){
					?>
					alertX("Successfully Updated Affiliate '<?php echo htmlentitiesX($_POST['title']); ?>'.");
					self.location = "<?php echo site_url(); echo $controller; ?>/edit/<?php echo $_POST['id']; ?>";
					<?php
				}
				else{
					?>
					alertX("Successfully Updated Details");
					self.location = "<?php echo site_url(); echo $controller; ?>/edit/<?php echo $_SESSION['user']['id']; ?>";
					<?php
				}
			}
			else{
			

			}
			
		}
		?>jQuery("#record_form *").attr("disabled", false);<?php
	}
	
	public function edit($id){
		if($_SESSION['user']['affiliate']&&$id!=$_SESSION['user']['id']){
			redirect_to(site_url()."affiliates/");
			exit();
		}
		
		$table = "affiliates";
		$controller = $table;
		$sql = "select * from `$table` where `id` = '".mysql_real_escape_string($id)."' limit 1" ;
		$q = $this->db->query($sql);
		$record = $q->result_array();
		$record = $record[0];
		$sql = "select count(`id`) as `cnt` from `affiliate_clicks` where `affiliate_id`='".$record['id']."'";
		$q = $this->db->query($sql);
		$cnt = $q->result_array();
		$cnt = $cnt[0]['cnt'];
		$record['clicks'] = $cnt;
		
		$sql = "select sum(`commission`) as `commission` from `affiliate_commissions` where `affiliate_id`='".$record['id']."'";
		$q = $this->db->query($sql);
		$cnt = $q->result_array();
		$commission = $cnt[0]['commission'];
		$record['commission'] = $commission;
		
		$sql = "select sum(`gross`) as `gross` from `affiliate_commissions` where `affiliate_id`='".$record['id']."'";
		$q = $this->db->query($sql);
		$cnt = $q->result_array();
		$gross = $cnt[0]['gross'];
		$record['gross'] = $gross;
		
		$sql = "select * from `affiliate_commissions` where `affiliate_id`='".$record['id']."' order by `id` desc limit 10";
		$q = $this->db->query($sql);
		$transactions = $q->result_array();
		$record['transactions'] = $transactions;
		
		$data['record'] = $record;
		$data['content'] = $this->load->view($controller.'/add', $data, true);
		$this->load->view('layout/main', $data);;
	}
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */