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
				
		/*
		$sql = "select L.`x`, L.`y`, L.`id`, WU.useremail,  LD.`land_owner` , format(LS.price,2) as price,
				if(L.`web_user_id` = 0, LS.title, LD.title) as title,
				if(L.`web_user_id` = 0, LS.detail, LD.detail) as detail				
				from `land` L
				left join land_detail LD on LD.id = L.land_detail_id				
				left join land_special LS on LS.id = L.land_special_id
				left join web_users WU on WU.id = L.web_user_id
				where L.`land_special_id` is not NULL limit $start, $limit" ;
		*/
		$sql = "select * from `land_special` where 1 limit $start, $limit";
		$export_sql = md5($sql);
		$_SESSION['export_sqls'][$export_sql] = $sql;
		$q = $this->db->query($sql);
		$records = $q->result_array();
		
		//$sql = "select count(`id`) as `cnt` from `land` where `land_special_id` is NULL order by `folder` desc" ;
		$sql = "select count(`id`) as `cnt` from `land_special` where 1" ;
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
		
		$sql = "select L.`x`, L.`y`, L.`id`, WU.useremail,  LD.`land_owner` , format(LS.price,2) as price,
				if(L.`web_user_id` = 0, LS.title, LD.title) as title,
				if(L.`web_user_id` = 0, LS.detail, LD.detail) as detail				
				from `land` L
				left join land_detail LD on LD.id = L.land_detail_id				
				left join land_special LS on LS.id = L.land_special_id
				left join web_users WU on WU.id = L.web_user_id
				where L.`land_special_id` is not NULL " ;					
				
		if($filter=='id'){
			$sql .= "and L.id = '".mysql_real_escape_string($search)."' ";
		} elseif ($filter == 'title' || $filter =='detail' ) {
			$sql .= "and if(L.`web_user_id` = 0, LOWER(LS.".$filter."), LOWER(LD.".$filter.") ) like '%".mysql_real_escape_string($search)."%' ";			
		} elseif($search != ''){
			$sql .= "and LOWER(`".$filter."`) like '%".mysql_real_escape_string($search)."%'";
		}
		$sql .= " limit $start, $limit" ;

		$export_sql = md5($sql);
		$_SESSION['export_sqls'][$export_sql] = $sql;
		$q = $this->db->query($sql);
		$records = $q->result_array();
		
		$sql = "select count(L.id) as `cnt` 
				from `land` L
				left join land_detail LD on LD.id = L.land_detail_id				
				left join land_special LS on LS.id = L.land_special_id
				left join web_users WU on WU.id = L.web_user_id
				where L.`land_special_id` is not NULL  ";
		if($filter=='id'){
			$sql .= "and L.id = '".mysql_real_escape_string($search)."' ";
		} elseif ($filter == 'title' || $filter =='detail' ) {
			$sql .= "and if(L.`web_user_id` = 0, LOWER(LS.".$filter."), LOWER(LD.".$filter.") ) like '%".mysql_real_escape_string($search)."%' ";			
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
		$data['content'] = $this->load->view('specialland/main', $data, true);
		$this->load->view('layout/main', $data);		
	}	
	function ajax_edit(){
		$table = "specialland";
		$controller = $table;
		$error = false;
		$_POST['price'] = str_replace(',','',trim($_POST['price']));
		
		if(!trim($_POST['title'])){
			?>alertX("Please input land title!");<?php
			$error = true;
		}
		if(!is_numeric($_POST['price']))
		{
			?>alertX("Please enter valid price!");<?php
			$error = true;
		}
		
		if(!$error){
			// check if there are other lands that are connected to the same land detail
			$landSpecialId = $_POST['id'];
			$landId = $_POST['id'];						
			
			$fieldUpdateSql = 
				"`title` = '".mysql_real_escape_string($_POST['title'])."',
				`detail` = '".mysql_real_escape_string($_POST['detail'])."',				
				`land_owner` = '".mysql_real_escape_string($_POST['land_owner'])."'";
			
			$sql = "update `land_special` set 
					`title` = '".mysql_real_escape_string($_POST['title'])."',
					`detail` = '".mysql_real_escape_string($_POST['detail'])."',
					`price` = '".mysql_real_escape_string($_POST['price'])."'
					where `id` = '$landSpecialId' limit 1";	
			
			$this->db->query($sql);										

			$sql = "delete from `pictures_special` where `land_special_id`=".$this->db->escape($landSpecialId);
			$this->db->query($sql);
			if(is_array($_POST['pictures'])){

				$mainPix = str_replace("admin2/../", '', $_POST['isMainPix']); 
				foreach($_POST['pictures'] as $key=>$value){
					$value = str_replace("admin2/../", '', $value); 
					
					$isMain = ($mainPix == $value)? 1 : 0;
					$sql = "insert into `pictures_special` set 
					`land_special_id`=".$this->db->escape($landSpecialId).", 
					`title`=".$this->db->escape($_POST['picture_titles'][$key]).",
					`isMain`='$isMain', 
					`picture`=".$this->db->escape($value);
					$this->db->query($sql);
				}
			}
			?>
			alertX("Successfully Updated Special Land Details '<?php echo htmlentitiesX($_POST['title']); ?>'.");
			self.location = "<?php echo site_url(); echo $controller; ?>/edit/<?php echo $_POST['id']; ?>";
			<?php
		}
		?>jQuery("#record_form *").attr("disabled", false);<?php
	}	
	public function edit($id){
		$table = "specialland";
		$controller = $table;
		$sql = "select * from `land_special` where `id` = '".mysql_real_escape_string($id)."' limit 1";
		$q = $this->db->query($sql);
		$record = $q->result_array();
		$record = $record[0];
		
		$sql = "select * from `pictures_special` where `land_special_id`=".$this->db->escape($id)." order by id asc";
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
		$sql = "select id, land_special_id, land_detail_id from `land` where `id`=".$this->db->escape($landId) . " limit 1";
		$q = $this->db->query($sql);
		$rs = $q->row_array();
		if(!empty($rs))
		{
			// totally delete
			$sql = "delete from land_special where id = '".$rs['land_special_id']."' limit 1";
			$q = $this->db->query($sql);
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