<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
session_start();
class webuser extends CI_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->database();
	}
	public function index(){
		$start = $_GET['start'];
		$start += 0;
		$limit = 50;
				
		$sql = "select id, useremail from `web_users` where 1 limit $start, $limit";
		$export_sql = md5($sql);
		$_SESSION['export_sqls'][$export_sql] = $sql;
		$q = $this->db->query($sql);
		$records = $q->result_array();
		
		//$sql = "select count(`id`) as `cnt` from `land` where `web_users_id` is NULL order by `folder` desc" ;
		$sql = "select count(`id`) as `cnt` from `web_users` where 1" ;
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
		$data['content'] = $this->load->view('webuser/main', $data, true);
		$this->load->view('layout/main', $data);
	}		
	public function search(){
		$start = $_GET['start'];
		$filter = $_GET['filter'];
		$start += 0;
		$limit = 50;
		$search = strtolower(trim($_GET['search']));
		$searchx = trim($_GET['search']);
		
		$sql = "select id, useremail from `web_users`  where 1 ";
		if($filter=='id'){
			$sql .= "and id = '".mysql_real_escape_string($search)."' ";
		} elseif($search != ''){
			$sql .= "and LOWER(`".$filter."`) like '%".mysql_real_escape_string($search)."%'";
		}
		$sql .= " limit $start, $limit" ;

		$export_sql = md5($sql);
		$_SESSION['export_sqls'][$export_sql] = $sql;
		$q = $this->db->query($sql);
		$records = $q->result_array();
				
		$sql = "select count(id) as `cnt`  from `web_users` WU where 1 ";
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
		$data['content'] = $this->load->view('webuser/main', $data, true);
		$this->load->view('layout/main', $data);		
	}	
	function ajax_edit(){
		$table = "webuser";
		$controller = $table;
		$error = false;		
		
		$this->load->library('form_validation');	
		$this->form_validation->set_rules('useremail', 'Email', 'required|valid_email');		
		
		if ($this->form_validation->run() == FALSE)
		{
			?>alertX("Please input valid user email!");<?php
			$error = true;
		}
		
		if(!$error){
			// check if there are other lands that are connected to the same land detail
			$id = $_POST['id'];			
			
			$sql = "update `web_users` set 
					`useremail` = '".mysql_real_escape_string($_POST['useremail'])."'" ;
			
			if(isset($_POST['password']) && $_POST['password'] != ''){
				$sql .= ", `plain_pass` = '".mysql_real_escape_string($_POST['password'])."'
						, `password` = '".md5($_POST['password'])."' ";				
			}										
			$sql .= "where `id` = '$id' limit 1";	
			
			$this->db->query($sql);										
			?>
			alertX("Successfully Updated Web User '<?php echo htmlentitiesX($_POST['useremail']); ?>'.");
			self.location = "<?php echo site_url(); echo $controller; ?>/edit/<?php echo $_POST['id']; ?>";
			<?php
		}
		?>jQuery("#record_form *").attr("disabled", false);<?php
	}	
	public function edit($id){
		$table = "webuser";
		$controller = $table;
		$sql = "select id, useremail from `web_users` where `id` = '".mysql_real_escape_string($id)."' limit 1";
		$q = $this->db->query($sql);
		$record = $q->result_array();
		$record = $record[0];

		$data['record'] = $record;
		$data['content'] = $this->load->view($controller.'/add', $data, true);
		
		$this->load->view('layout/main', $data);;
	}
	function ajax_add(){
		$table = "webuser";
		$controller = $table;
		$error = false;		
				
		$this->load->library('form_validation');	
		$this->form_validation->set_rules('useremail', 'Email', 'required|valid_email');		
		
		if ($this->form_validation->run() == FALSE)
		{
			?>alertX("Please input valid user email!");<?php
			$error = true;
		}
		
		if(!$error){								
			$sql = "insert into `web_users` set 
					`useremail` = '".mysql_real_escape_string($_POST['useremail'])."'
					";	
			if(isset($_POST['password']) && $_POST['password'] != ''){
				$sql .= ", `plain_pass` = '".mysql_real_escape_string($_POST['password'])."'
						, `password` = '".md5($_POST['password'])."' ";				
			}						
			$this->db->query($sql);										
			?>
			alertX("Successfully Inserted New Web User '<?php echo htmlentitiesX($_POST['useremail']); ?>'.");
			//self.location = "<?php echo site_url(); echo $controller; ?>/add";
			<?php
		}
		?>jQuery("#record_form *").attr("disabled", false);<?php
	}	
	public function add(){	
		$controller = "webuser";
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
		$sql = "delete from `web_users` where id = '".$id."' limit 1";
		$q = $this->db->query($sql);
		?>
		alertX("Successfully deleted.");
		<?php		
		exit();
	}
	public function ajax_search(){
		$name = strtolower($_GET['term'])."%";
		$sql = "select `id` as `value`, `useremail` as `label` from `web_users` where LOWER(`useremail`) like ".$this->db->escape(trim($name))." limit 10" ;
		$q = $this->db->query($sql);
		$records = $q->result_array();
		header('Content-type: application/json');
		echo json_encode($records);
		exit();
	}

}
?>