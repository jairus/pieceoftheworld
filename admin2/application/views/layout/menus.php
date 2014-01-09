<?php

$controller = $this->router->class;
$method = $this->router->method;
if($method=='revision'){
	$controller = "revisions";
}
if($method=='contribution'){
	$controller = "contributions";
}

if($_SESSION['user']['affiliate']){
	if($controller!="affiliates"){
		ob_end_clean();
		?>
		<script>
			self.location = "<?php echo site_url(); ?>affiliates";
		</script>
		<?php
		exit();
	}
	return 0;
}
?>
<ul>
	<li <?php if($controller=="latest"){ echo "class='selected'"; } ?> onclick='self.location="<?php echo site_url()."latest";?>"'>
		Latest Updates
	</li>
	<li <?php if($controller=="land"){ echo "class='selected'"; } ?> onclick='self.location="<?php echo site_url()."land";?>"'>
		Single Land
	</li>
	<li <?php if($controller=="bundledland"){ echo "class='selected'"; } ?> onclick='self.location="<?php echo site_url()."bundledland";?>"'>
		Bundled Land
	</li>
	<li <?php if($controller=="specialland"){ echo "class='selected'"; } ?> onclick='self.location="<?php echo site_url()."specialland";?>"'>
		Special Land
	</li>
	<li <?php if($controller=="affiliates"){ echo "class='selected'"; } ?> onclick='self.location="<?php echo site_url()."affiliates";?>"'>
		Affiliates
	</li>
	<li <?php if($controller=="webuser"){ echo "class='selected'"; } ?> onclick='self.location="<?php echo site_url()."webuser";?>"'>
		Web Users
	</li>
	<li <?php if($controller=="user"){ echo "class='selected'"; } ?> onclick='self.location="<?php echo site_url()."user";?>"'>
		Admin Users
	</li>
	<li <?php if($controller=="landcounter"){ echo "class='selected'"; } ?> onclick='self.location="<?php echo site_url()."landcounter";?>"'>
		Land Views
	</li>
	<li <?php if($controller=="landbids"){ echo "class='selected'"; } ?> onclick='self.location="<?php echo site_url()."landbids";?>"'>
		Land Bids
	</li>
	<li <?php if($controller=="categories"){ echo "class='selected'"; } ?> onclick='self.location="<?php echo site_url()."categories";?>"'>
		Static Categories
	</li>
	<li <?php if($controller=="tags"){ echo "class='selected'"; } ?> onclick='self.location="<?php echo site_url()."tags";?>"'>
		User Tags
	</li>		
	<li <?php if($controller=="transactions"){ echo "class='selected'"; } ?> onclick='self.location="<?php echo site_url()."transactions";?>"'>
		Transactions
	</li>		
	
	<!--
	<li <?php if($controller=="sales"){ echo "class='selected'"; } ?> onclick='self.location="<?php echo site_url()."sales";?>"'>
		Sales
	</li>
	<li <?php if($controller=="paypal"){ echo "class='selected'"; } ?> onclick='self.location="<?php echo site_url()."paypal";?>"'>
		Paypal Configuration
	</li>
	-->
</ul>
