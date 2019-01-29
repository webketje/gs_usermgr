<?php /* this file is part of the GS UserMgr plugin package, Copyright (c) 2017  Kevin Van Lierde <kevin.van.lierde@gmail.com> */
// Setup inclusions
$load['plugin'] = true;
$title = 'Unauthorized';

// Include common.php
include('inc/common.php');
	
// Variable settings
login_cookie_check();
include('template/header.php');
?>
<script>document.body.id = '<?php echo $_COOKIE['GS_LAST_TAB']; ?>'</script>
<?php 
include('template/include-nav.php');
?>
<div class="bodycontent clearfix">
	
	<div id="maincontent">
		<div class="main" >
			<?php 
			if (file_exists(GSDATAPAGESPATH . 'unauthorized.xml'))
			  $perm_denied_msg = getXML(GSDATAPAGESPATH . 'unauthorized.xml');
			else {
				$perm_denied_msg = new stdClass();
				$perm_denied_msg->title   = i18n_r('usermgr/permission_denied_title');
				$perm_denied_msg->content = i18n_r('usermgr/permission_denied_content');
			}
			echo '<h3>' . $perm_denied_msg->title . '</h3>' . html_entity_decode($perm_denied_msg->content, ENT_QUOTES);
			?>
		</div>
	</div>
	
</div>
<?php 
include('template/footer.php');