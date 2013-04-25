<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
session_start();
class land extends CI_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->database();
	}
	public function index(){
		$start = $_GET['start'];
		$start += 0;
		$limit = 50;
				
		$sql = "select L.`x`, L.`y`, L.`id`, LD.`title`, LD.`detail`, LD.`folder`, LD.`picture`, LD.`email_resent` , LD.`land_owner` , WU.useremail, L.web_user_id
				from `land` L
				left join land_detail LD on LD.id = L.land_detail_id
				left join web_users WU on WU.id = L.web_user_id
				where L.`land_special_id` is NULL order by L.`id` desc limit $start, $limit";
		$export_sql = md5($sql);
		$_SESSION['export_sqls'][$export_sql] = $sql;
		$q = $this->db->query($sql);
		$records = $q->result_array();
		
		//$sql = "select count(`id`) as `cnt` from `land` where `land_special_id` is NULL order by `folder` desc" ;
		$sql = "select count(`id`) as `cnt` from `land` where `land_special_id` is NULL " ;
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
		$data['content'] = $this->load->view('land/main', $data, true);
		$this->load->view('layout/main', $data);
	}
	
	
	public function search(){
		$start = $_GET['start'];
		$filter = $_GET['filter'];
		$start += 0;
		$limit = 50;
		$search = strtolower(trim($_GET['search']));
		$searchx = trim($_GET['search']);
		
		//$sql = "select * from `land` where ";
		$sql = "select L.`x`, L.`y`, L.`id`, LD.`title`, LD.`detail`, LD.`folder`, LD.`picture`, LD.`email_resent` , LD.`land_owner` , WU.useremail, L.web_user_id
				from `land` L
				left join land_detail LD on LD.id = L.land_detail_id
				left join web_users WU on WU.id = L.web_user_id
				where L.`land_special_id` is NULL and " ;
		
		if($filter=='id'){
			$sql .= "L.id = '".mysql_real_escape_string($search)."' ";
		}
		else{
			$sql .= "LOWER(`".$filter."`) like '%".mysql_real_escape_string($search)."%'";
		}
		$sql .= " limit $start, $limit" ;
		$export_sql = md5($sql);
		$_SESSION['export_sqls'][$export_sql] = $sql;
		$q = $this->db->query($sql);
		$records = $q->result_array();
		
		$sql = "select count(L.id) as `cnt` from `land` L
				left join land_detail LD on LD.id = L.land_detail_id
				left join web_users WU on WU.id = L.web_user_id
				where L.`land_special_id` is NULL and ";
		if($filter=='id'){
			$sql .= "L.id = '".mysql_real_escape_string($search)."' ";
		}
		else{
			$sql .= "LOWER(`".$filter."`) like '%".mysql_real_escape_string($search)."%'";
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
		$data['content'] = $this->load->view('land/main', $data, true);
		$this->load->view('layout/main', $data);		
	}
	
	function ajax_edit(){
		$table = "land";
		$controller = $table;
		$error = false;
		if(!trim($_POST['title'])){
			?>alertX("Please input land title!");<?php
			$error = true;
		}
		// check if the web user exists if the autocomplete was not used
		if($_POST['web_user_id'] == '' ){
			if($_POST['useremail'] != ''){
				$sql = "select id from web_users where useremail = '".mysql_real_escape_string($_POST['useremail'])."' limit 1";
				$rs = $this->db->query($sql)->row_array();
				if(!empty($rs)){
					$_POST['web_user_id'] = $rs['id'];
				}
				else
				{
					?>alertX("Please enter existing web users only");<?php
					$error = true;			
				}			
			} else {
				$_POST['web_user_id'] = 0;
			}
			
		}		
		
		if(!$error){
			// check if there are other lands that are connected to the same land detail
			$landDetailId = $_POST['land_detail_id'];
			$landId = $_POST['id'];						
			$sql = "update land set `web_user_id` = '".mysql_real_escape_string($_POST['web_user_id'])."' where id = '$landId' limit 1";
			$this->db->query($sql);
			
			$fieldUpdateSql = 
				"`title` = '".mysql_real_escape_string($_POST['title'])."',
				`detail` = '".mysql_real_escape_string($_POST['detail'])."',				
				`land_owner` = '".mysql_real_escape_string($_POST['land_owner'])."'";									
			
			$sql = "select id from land where land_detail_id = '$landDetailId' and id != '$landId' limit 1";
			if($this->db->query($sql)->num_rows())
			{
				// create new land detail if there exist lands that depend on this one
				$sql = "insert into land_detail set $fieldUpdateSql";
				$this->db->query($sql);
				$landDetailId = $this->db->insert_id();
				$sql = "update `land` set land_detail_id = '$landDetailId' where `id` = '$landId' limit 1";
				$this->db->query($sql);
			}
			else
			{
				$sql = "update `land_detail` set $fieldUpdateSql where `id` = '$landDetailId' limit 1";
				$this->db->query($sql);					
			}			

			$sql = "delete from `pictures` where `land_id`=".$this->db->escape($landId);
			$this->db->query($sql);
			if(is_array($_POST['pictures'])){

				// if no main pix, default it to the first one
				$mainPix = (isset($_POST['isMainPix']))? str_replace("admin2/../", '', $_POST['isMainPix']) : str_replace("admin2/../", '', $_POST['pictures'][0]);

				foreach($_POST['pictures'] as $key=>$value){
					$value = str_replace("admin2/../", '', $value); 
					
					$isMain = ($mainPix == $value)? 1 : 0;
					$sql = "insert into `pictures` set 
					`land_id`=".$this->db->escape($landId).", 
					`title`=".$this->db->escape($_POST['picture_titles'][$key]).",
					`isMain`='$isMain', 
					`picture`=".$this->db->escape($value);
					$this->db->query($sql);
				}
			}
			?>
			alertX("Successfully Updated Land Details '<?php echo htmlentitiesX($_POST['title']); ?>'.");
			// self.location = "<?php echo site_url(); echo $controller; ?>/edit/<?php echo $_POST['id']; ?>";
			<?php
		}
		?>jQuery("#record_form *").attr("disabled", false);<?php
	}
	
	public function edit($id){
		$table = "land";
		$controller = $table;
		$sql = "select L.`x`, L.`y`, L.`id`, LD.`title`, LD.`detail`, LD.`folder`,  LD.`land_owner`,  LD.`picture`, LD.`email_resent` , LD.`id` as land_detail_id, WU.useremail
				from `land` L
				left join land_detail LD on LD.id = L.land_detail_id
				left join web_users WU on WU.id = L.web_user_id
				where L.`id` = '".mysql_real_escape_string($id)."' limit 1";
		$q = $this->db->query($sql);
		$record = $q->result_array();
		$record = $record[0];
		
		$sql = "select * from `pictures` where `land_id`=".$this->db->escape($id)." order by id asc";
		$q = $this->db->query($sql);
		$pictures = $q->result_array();	
		
		$data['pictures'] = $pictures;			
		$data['record'] = $record;
		$data['content'] = $this->load->view($controller.'/add', $data, true);

		
		$this->load->view('layout/main', $data);;
	}
	public function ajax_delete($landId=""){
		if(!$_SESSION['user']){
			return false;
		}
		if(!$landId){
			$landId = $_POST['id'];
		}
		$sql = "select id, land_detail_id from `land` where `id`=".$this->db->escape($landId) . " limit 1";
		$q = $this->db->query($sql);
		$rs = $q->row_array();
		if(!empty($rs))
		{			
			$sql = "delete from land_detail where id = '".$rs['land_detail_id']."' limit 1";
			$q = $this->db->query($sql);
			$sql = "delete from land where id = '".$rs['id']."' limit 1";
			$q = $this->db->query($sql);
			?>
			alertX("Successfully deleted.");
			<?php		
		}
		else
		{
			?>
			alertX("The record is already deleted in the database.");
			<?php		
		}
		exit();
	}	
}
?>
