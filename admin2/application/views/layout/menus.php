<?php
$controller = $this->router->class;
$method = $this->router->method;
if($method=='revision'){
	$controller = "revisions";
}
if($method=='contribution'){
	$controller = "contributions";
}
?>
<ul>
	<li <?php if($controller=="latest"){ echo "class='selected'"; } ?> onclick='self.location="<?php echo site_url()."latest";?>"'>
		Latest Updates
	</li>
	<li <?php if($controller=="land"){ echo "class='selected'"; } ?> onclick='self.location="<?php echo site_url()."land";?>"'>
		Land
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
		System Users
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
