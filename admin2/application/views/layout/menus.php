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
	<li <?php if($controller=="sales"){ echo "class='selected'"; } ?> onclick='self.location="<?php echo site_url()."sales";?>"'>
		Sales
	</li>
	<li <?php if($controller=="paypal"){ echo "class='selected'"; } ?> onclick='self.location="<?php echo site_url()."paypal";?>"'>
		Paypal Configuration
	</li>
	<li <?php if($controller=="webusers"){ echo "class='selected'"; } ?> onclick='self.location="<?php echo site_url()."webusers";?>"'>
		Web Users
	</li>
</ul>

<script>
jQuery.ajax({
	url: "<?php echo site_url(); ?>revisions/countpending",
	type: "POST",
	data: "",
	success: function(data){
		if(data!="0"){
			jQuery("#revcount").html(data);
			jQuery("#revcount").show();
		}
	}
});
jQuery.ajax({
	url: "<?php echo site_url(); ?>contributions/countpending",
	type: "POST",
	data: "",
	success: function(data){
		if(data!="0"){
			jQuery("#concount").html(data);
			jQuery("#concount").show();
		}
	}
});
</script>