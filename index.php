<? 
require_once('application.php');

//print_ar($_REQUEST);

$pageurl = me();
$CFG->pageurl = $pageurl;

//print_ar($pageurl);


$content_page = YContent::getPageByURL($pageurl,$CFG->content_page_content_type_id);

	if(($content_page['id'] > 0 && $content_page['is_active']=="Y") || $_POST['preview'] == "content") {
		if($_POST['preview'] == "content"){
			$data = Content::formatPreviewData($_POST['previewData']); //this function converts the data from the backend form posted here
																		 //into the arrays pulled from the DB normally.
			
			$content_page = $data['pagedata'];
			$content = $data['content'];
			unset($data);
		}else{
			$content = (YContent::get1PageContent($content_page['id'],"",$CFG->content_page_content_type_id,true));
			$content = $content['eng'];
		}
		
		$meta_keywords    = $content_page['meta_keywords'];
		$meta_description = $content_page['meta_description'];
		$title       = $content_page['page_title'];

		if($content_page['url'] == "/about"){
			$title = "About Us | We Pay Cash for Junk Cars | Cash for Damaged Wrecked Cars";
		}
		//sidebar control
		$sidebar_control_object = SidebarAdControl::generateSidebarInfo('manual',$CFG->pageurl);
		//print_ar($sidebar_control_object);
		//$header_code = $content['header_code'] ? $content['header_code'] : null;  //this is for A/B testing
		
		include_once('includes/header.inc.php');

		$template_file = "includes/layout".(int)$content_page['layout_id'].".php";
		if(file_exists($template_file)){
			require($template_file);
		} else {
			echo "<p>&nbsp;<p>&nbsp;
				<p>Error: Please create a Layout file: $template_file. </p>
				<p>&nbsp;<p>&nbsp;";
		}
		include_once('includes/footer.inc.php');	
		exit;
	}
	else if( $pageurl == '/' || $pageurl == '/index.php' )
	{
		$title = 'Planet Junk Cars';
		$title = 'We Pay Cash for Junk Cars, Wrecked Old Running or Not | Planet Junk Cars';
		$meta_keywords = $content['meta_keywords'];
		$meta_description = "Want to get the most money possible for your car? It doesn't matter if its beaten up, broken, damages or wrecked, we buy all makes, all models, in ANY CONDITION. ";
		include('includes/header.inc.php');
		include('includes/home.inc.php');
		include('includes/footer.inc.php');
	} else {

		$title = 'Planet Junk Cars';
		// $meta_keywords = $content['meta_keywords'];
		$meta_description = "404 Page Not Found";
		include('includes/header.inc.php');
		include('includes/404.php');
		include('includes/footer.inc.php');

	}




