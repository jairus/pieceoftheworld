<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
session_start();
class user extends CI_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->database();
	}
	public function index(){
		$start = $_GET['start'];
		$start += 0;
		$limit = 50;
				
		$sql = "select * from `user` where 1 order by id desc limit $start, $limit";
		$export_sql = md5($sql);
		$_SESSION['export_sqls'][$export_sql] = $sql;
		$q = $this->db->query($sql);
		$records = $q->result_array();		
		
		//$sql = "select count(`id`) as `cnt` from `land` where `user_id` is NULL order by `folder` desc" ;
		$sql = "select count(`id`) as `cnt` from `user` where 1" ;
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
		$data['content'] = $this->load->view('user/main', $data, true);
		$this->load->view('layout/main', $data);
	}		
	public function search(){
		$start = $_GET['start'];
		$filter = $_GET['filter'];
		$start += 0;
		$limit = 50;
		$search = strtolower(trim($_GET['search']));
		$searchx = trim($_GET['search']);
		
		$sql = "select * from `user`  where 1 ";
		if($filter=='id'){
			$sql .= "and id = '".mysql_real_escape_string($search)."' ";
		} elseif($search != ''){
			$sql .= "and LOWER(`".$filter."`) like '%".mysql_real_escape_string($search)."%'";
		}
		$sql .= " order by id desc limit $start, $limit" ;

		$export_sql = md5($sql);
		$_SESSION['export_sqls'][$export_sql] = $sql;
		$q = $this->db->query($sql);
		$records = $q->result_array();
				
		$sql = "select count(id) as `cnt`  from `user` WU where 1 ";
		if($filter=='id'){
			$sql .= "and id = '".mysql_real_escape_string($search)."' ";
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
		$data['content'] = $this->load->view('user/main', $data, true);
		$this->load->view('layout/main', $data);		
	}	
	function ajax_edit(){
		$table = "user";
		$controller = $table;
		$error = false;		
		
		$this->load->library('form_validation');	
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email');		
		
		if ($this->form_validation->run() == FALSE)
		{
			?>alertX("Please input valid user email!");<?php
			$error = true;
		}
		elseif(!$this->isUnique($_POST['email'], $_POST['id']) ){
			?>alertX("That email is not available!");<?php
			$error = true;		
		}
		
		if(!$error){
			// check if there are other lands that are connected to the same land detail
			$id = $_POST['id'];			
			
			$sql = "update `user` set 
					`first_name` = '".mysql_real_escape_string($_POST['first_name'])."',
					`last_name` = '".mysql_real_escape_string($_POST['last_name'])."',
					`email` = '".mysql_real_escape_string($_POST['email'])."',
					`city` = '".mysql_real_escape_string($_POST['city'])."',
					`state_us` = '".mysql_real_escape_string($_POST['state_us'])."',
					`state_nonus` = '".mysql_real_escape_string($_POST['state_nonus'])."',
					`country` = '".mysql_real_escape_string($_POST['country'])."',
					`is_admin` = '".mysql_real_escape_string($_POST['is_admin'])."'" ;
			
			if(isset($_POST['password']) && $_POST['password'] != ''){
				$sql .= ", `password` = '".($_POST['password'])."' ";				
			}										
			$sql .= "where `id` = '$id' limit 1";	
			
			$this->db->query($sql);										
			?>
			alertX("Successfully Updated User '<?php echo htmlentitiesX($_POST['email']); ?>'.");
			self.location = "<?php echo site_url(); echo $controller; ?>/edit/<?php echo $_POST['id']; ?>";
			<?php
		}
		?>jQuery("#record_form *").attr("disabled", false);<?php
	}	
	public function edit($id){
		$table = "user";
		$controller = $table;
		$sql = "select * from `user` where `id` = '".mysql_real_escape_string($id)."' limit 1";
		$q = $this->db->query($sql);
		$record = $q->result_array();
		$record = $record[0];
		unset($record['password']);

		$data['record'] = $record;
		$data['content'] = $this->load->view($controller.'/add', $data, true);
		
		$this->load->view('layout/main', $data);;
	}
	function ajax_add(){
		$table = "user";
		$controller = $table;
		$error = false;		
				
		$this->load->library('form_validation');	
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email');		
		
		if ($this->form_validation->run() == FALSE)
		{
			?>alertX("Please input valid user email!");<?php
			$error = true;
		}
		elseif(!$this->isUnique($_POST['email']) ){
			?>alertX("That email is not available!");<?php
			$error = true;		
		}
		
		if(!$error){								
			$sql = "insert into `user` set 
					`first_name` = '".mysql_real_escape_string($_POST['first_name'])."',
					`last_name` = '".mysql_real_escape_string($_POST['last_name'])."',
					`email` = '".mysql_real_escape_string($_POST['email'])."',
					`city` = '".mysql_real_escape_string($_POST['city'])."',
					`state_us` = '".mysql_real_escape_string($_POST['state_us'])."',
					`state_nonus` = '".mysql_real_escape_string($_POST['state_nonus'])."',
					`country` = '".mysql_real_escape_string($_POST['country'])."',
					`is_admin` = '".mysql_real_escape_string($_POST['is_admin'])."'" ;					
			if(isset($_POST['password']) && $_POST['password'] != ''){
				$sql .= ", `password` = '".($_POST['password'])."' ";				
			}						
			$this->db->query($sql);										
			?>
			alertX("Successfully Inserted New User '<?php echo htmlentitiesX($_POST['email']); ?>'.");
			//self.location = "<?php echo site_url(); echo $controller; ?>/add";
			<?php
		}
		?>jQuery("#record_form *").attr("disabled", false);<?php
	}	
	private function isUnique($value='', $id = null, $field = 'email' )
	{
		$table = "user";
		$where = ($id)? " and id != '$id'" : ' ';
		$query = $this->db->query("select id from $table where $field = '".mysql_real_escape_string($value)."' $where limit 1");
		return !$query->num_rows();
	}
	public function add(){	
		$controller = "user";
		$data['content'] = $this->load->view($controller.'/add', $data, true);
		$this->load->view('layout/main', $data);;
	}
	public function ajax_delete($id=""){
		if(!$_SESSION['user']){
			return false;
		}
		if(!$id){
			$id = $_POST['id'];
		}
		$id = mysql_real_escape_string($id);
		$sql = "delete from `user` where id = '".$id."' limit 1";
		$q = $this->db->query($sql);
		?>
		alertX("Successfully deleted.");
		<?php		
		exit();
	}
}
?>