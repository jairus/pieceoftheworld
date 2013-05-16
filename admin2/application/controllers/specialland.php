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

		$sql = "select LS.*, C.name as categoryName
		        from `land_special` LS
		        left join categories C on C.id = LS.category_id
		        where 1 and LS.`id` in (select distinct `land_special_id` from `land`) order by LS.`id` desc limit $start, $limit";
		$export_sql = md5($sql);
		$_SESSION['export_sqls'][$export_sql] = $sql;
		$q = $this->db->query($sql);
		$records = $q->result_array();
		
		//$sql = "select count(`id`) as `cnt` from `land` where `land_special_id` is NULL order by `folder` desc" ;
		$sql = "select count(`id`) as `cnt` from `land_special` where 1 and `id` in (select distinct `land_special_id` from `land`)" ;
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
		
		$sql = "select LS.*, WU.useremail, C.name as categoryName from `land_special` LS
				left join web_users WU on WU.id = LS.web_user_id
				left join categories C on C.id = LS.category_id
				where 1 and `LS`.`id` in (select distinct `land_special_id` from `land`)";
		if($filter=='id'){
			$sql .= "and LS.id = '".mysql_real_escape_string($search)."' ";
		} elseif($filter == 'useremail'){
			$sql .= "and LOWER(WU.useremail) like '%".mysql_real_escape_string($search)."%'";
        } elseif($filter=='category'){
                $sql .= "and LOWER(C.name) like '%".mysql_real_escape_string($search)."%' ";
		} elseif($search != ''){
			$sql .= "and LOWER(`".$filter."`) like '%".mysql_real_escape_string($search)."%'";
		}
		$sql .= " limit $start, $limit" ;

		$export_sql = md5($sql);
		$_SESSION['export_sqls'][$export_sql] = $sql;
		$q = $this->db->query($sql);
		$records = $q->result_array();
				
		$sql = "select count(LS.id) as `cnt`  from `land_special` LS
				left join web_users WU on WU.id = LS.web_user_id
				left join categories C on C.id = LS.category_id
				where 1 and `LS`.`id` in (select distinct `land_special_id` from `land`) ";
		if($filter=='id'){
			$sql .= "and LS.id = '".mysql_real_escape_string($search)."' ";
		} elseif($filter == 'useremail'){
			$sql .= "and LOWER(WU.useremail) like '%".mysql_real_escape_string($search)."%'";
        } elseif($filter=='category'){
            $sql .= "and LOWER(C.name) like '%".mysql_real_escape_string($search)."%' ";
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
			$landSpecialId = $_POST['id'];
			$landId = $_POST['id'];
						
			$sql = "update `land_special` set 
					`title` = '".mysql_real_escape_string($_POST['title'])."',
					`detail` = '".mysql_real_escape_string($_POST['detail'])."',
					`price` = '".mysql_real_escape_string($_POST['price'])."',
					`land_owner` = '".mysql_real_escape_string($_POST['land_owner'])."',
					`web_user_id` = '".mysql_real_escape_string($_POST['web_user_id'])."',
					`category_id` = '".mysql_real_escape_string($_POST['category_id'])."'
					where `id` = '$landSpecialId' limit 1";	
			
			$this->db->query($sql);										

			$sql = "delete from `pictures_special` where `land_special_id`=".$this->db->escape($landSpecialId);
			$this->db->query($sql);
			if(is_array($_POST['pictures'])){

				// if no main pix, default it to the first one
				$mainPix = (isset($_POST['isMainPix']))? str_replace("admin2/../", '', $_POST['isMainPix']) : str_replace("admin2/../", '', $_POST['pictures'][0]);

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
            $this->saveVideos($landSpecialId, 'land_special');
			?>
			alertX("Successfully Updated Special Land Details '<?php echo htmlentitiesX($_POST['title']); ?>'.");
			self.location = "<?php echo site_url(); echo $controller; ?>/edit/<?php echo $_POST['id']; ?>";
			jQuery("#savebutton").val("Save");
			<?php
		}
		?>jQuery("#record_form *").attr("disabled", false);<?php
	}	
	
	function ajax_add(){
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
			$landSpecialId = $_POST['id'];
			$landId = $_POST['id'];
						
			$sql = "insert into `land_special` set 
					`title` = '".mysql_real_escape_string($_POST['title'])."',
					`detail` = '".mysql_real_escape_string($_POST['detail'])."',
					`price` = '".mysql_real_escape_string($_POST['price'])."',
					`land_owner` = '".mysql_real_escape_string($_POST['land_owner'])."',
					`category_id` = '".mysql_real_escape_string($_POST['category_id'])."',
					`web_user_id` = '".mysql_real_escape_string($_POST['web_user_id'])."'					
					";	
			
			$this->db->query($sql);										
			$insert_id = $this->db->insert_id();
			
			if(count($_POST['points'])){
				foreach($_POST['points'] as $value){
					list($x, $y) = explode("-", $value);
					$sql = "insert into `land` set 
					`x`='".mysql_real_escape_string($x)."',
					`y`='".mysql_real_escape_string($y)."',
					`land_special_id`='".mysql_real_escape_string($insert_id)."'
					";
					$this->db->query($sql);	
				}
			}
			
			$sql = "delete from `pictures_special` where `land_special_id`=".$this->db->escape($insert_id);
			$this->db->query($sql);
			if(is_array($_POST['pictures'])){
				
				//$mainPix = $_POST['isMainPix'];
				
				// if no main pix, default it to the first one
				$mainPix = (isset($_POST['isMainPix']))? $_POST['isMainPix'] : $_POST['pictures'][0];

				
				foreach($_POST['pictures'] as $key=>$value){
					//move files
					$from = dirname(__FILE__)."/../../../_uploads2/specialland/temp/".$_POST['sid']."/".urldecode(basename($value));
					$folder = dirname(__FILE__)."/../../../_uploads2/specialland/".$insert_id."/images/";
					if(!is_dir($folder)){
						@mkdir(dirname(__FILE__)."/../../../_uploads2/specialland/".$insert_id."/", 0777);
						@mkdir(dirname(__FILE__)."/../../../_uploads2/specialland/".$insert_id."/images/", 0777);
					}
					$to = $folder.urldecode(basename($value));
					rename($from, $to);
					
					//http%3A//www.pieceoftheworld.co/admin2/../_uploads2/specialland/temp/special1366809169454/images.jpg
					$value2 = str_replace("admin2/../_uploads2/specialland/temp/".$_POST['sid']."/", "_uploads2/specialland/".$insert_id."/images/", $value);
					$isMain = ($mainPix == $value)? 1 : 0;
					$sql = "insert into `pictures_special` set 
					`land_special_id`=".$this->db->escape($insert_id).", 
					`title`=".$this->db->escape($_POST['picture_titles'][$key]).",
					`isMain`='$isMain', 
					`picture`=".$this->db->escape($value2);
					$this->db->query($sql);
				}
				
			
			}
            $this->saveVideos($insert_id, 'land_special');
			?>
			alertX("Successfully Added Special Land Details '<?php echo htmlentitiesX($_POST['title']); ?>'.");
			self.location = "<?php echo site_url(); echo $controller; ?>/edit/<?php echo $insert_id; ?>";
			<?php
		}
		?>jQuery("#record_form *").attr("disabled", false);<?php
	}	
	
	
	public function edit($id){
		$table = "specialland";
		$controller = $table;
		$sql = "select LS.*, WU.useremail from `land_special` LS
				left join web_users WU on WU.id = LS.web_user_id
				where LS.id = '".mysql_real_escape_string($id)."' limit 1";
		$q = $this->db->query($sql);
		$record = $q->result_array();
		$record = $record[0];
		
		$sql = "select * from `pictures_special` where `land_special_id`=".$this->db->escape($id)." order by id asc";
		$q = $this->db->query($sql);
		$pictures = $q->result_array();	
		
		//get first point of the special land
		
		$sql = "select * from `land` where `land_special_id`='".mysql_real_escape_string($id)."'";
		$q = $this->db->query($sql);
		$points = $q->result_array();
		$record['points'] = $points;
		
		$data['pictures'] = $pictures;			
		$data['record'] = $record;

        $sql = "select id, name from `categories` where `deleted` = '0' order by name asc";
        $q = $this->db->query($sql);
        $categories = $q->result_array();
        $data['categories'] = $categories;

        $sql = "select * from `videos_special` where `land_special_id`=".$this->db->escape($record['id'])." order by id asc";
        $q = $this->db->query($sql);
        $videos = $q->result_array();
        $data['videos'] = $videos;

		$data['content'] = $this->load->view($controller.'/add', $data, true);

		
		$this->load->view('layout/main', $data);;
	}
	
	public function add(){
		$table = "specialland";
		$controller = $table;

        $sql = "select id, name from `categories` where `deleted` = '0' order by name asc";
        $q = $this->db->query($sql);
        $categories = $q->result_array();
        $data['categories'] = $categories;

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
		$sql = "delete from `land_special` where id = '".$id."'";
		$q = $this->db->query($sql);
		$sql = "delete from `land` where land_special_id = '".$id."'";
		$q = $this->db->query($sql);
		$sql = "delete from `pictures_special` where land_special_id = '".$id."'";
		$q = $this->db->query($sql);
		?>
		alertX("Successfully deleted.");
		<?php		
		exit();
	}
    private function saveVideos($landId, $type)
    {
        $result = array();
        if($type == 'land_detail'){
            $table = 'videos';
            $landField = 'land_id';
        } else {
            $table = 'videos_special';
            $landField = 'land_special_id';
        }

        $sql = "delete from `$table` where `$landField`=".mysql_real_escape_string($landId);
        $this->db->query($sql);
        if(is_array($_POST['video_link'])){

            foreach($_POST['video_link'] as $key => $value){
                if($value){
                    $sql = "insert into `$table` set
                `$landField`='".mysql_real_escape_string($landId)."',
                `title`='".mysql_real_escape_string($_POST['video_title'][$key])."',
                `video`='".mysql_real_escape_string($value)."'";
                    $this->db->query($sql);
                }
            }
            $result = array('status' => true, 'message' => 'Video saved successfully');
        } else {
            $result = array('status' => false, 'message' => 'Cannot save video');
        }
        return $result;
    }
    public function ajax_saveVideo()
    {
        $result = $this->saveVideos($_POST['id'], 'land_special');
        ?>
        alertX("<?php echo $result['message']?>");
        <?php
    }
}
?>