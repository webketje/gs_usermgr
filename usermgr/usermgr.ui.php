<h3 class="floated" style="text-transform: capitalize;"><?php i18n('users_ui/label_users');?></h3>

<div class="edit-nav clearfix">
  <a href="javascript:void(0)" id="view-permissions">
     <span><?php i18n('users_ui/label_permissions'); ?></span>
  </a>
  <a href="javascript:void(0)" id="view-groups">
     <span><?php i18n('users_ui/label_groups'); ?></span>
  </a>
  <a href="javascript:void(0)" id="view-users" class="current">
     <span><?php i18n('users_ui/label_users'); ?></span>
  </a>
</div>

  <a href="javascript:void(0)" id="btn-add" accesskey="a">
     <span><?php i18n('users_ui/btn_add_user'); ?></span>
     <span style="display: none;"><?php i18n('users_ui/btn_add_group'); ?></span>
  </a>
<table class="edittable">
	<thead>
		<tr>
			<th><?php i18n('users_ui/label_user');?></th>
			<th><?php i18n('users_ui/label_group');?></th>
		</tr>
	</thead>
	<tbody>
		<tr></tr>
	</tbody>
</table>

<table class="edittable">
	<thead>
		<tr>
			<th><?php i18n('users_ui/label_permission');?></th>
			<th><?php i18n('users_ui/label_description');?></th>
		</tr>
	</thead>
	<tbody>
		<tr></tr>
	</tbody>
</table>