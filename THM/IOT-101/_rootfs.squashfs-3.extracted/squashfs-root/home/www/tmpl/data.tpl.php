<?php /* Smarty version 2.6.18, created on 2009-07-17 05:00:09
         compiled from data.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'replace', 'data.tpl', 16, false),)), $this); ?>
<form name="dataForm" id="dataForm" enctype="multipart/form-data" action="index.php?page=master" method="post">
	<input type="hidden" id="loading" value="true">
	<input type="hidden" name="menu1" id="menu1" value="<?php echo $this->_tpl_vars['navigation']['1']; ?>
">
	<input type="hidden" name="menu2" id="menu2" value="<?php echo $this->_tpl_vars['navigation']['2']; ?>
">
	<input type="hidden" name="menu3" id="menu3" value="<?php echo $this->_tpl_vars['navigation']['3']; ?>
">
	<input type="hidden" name="menu4" id="menu4" value="<?php echo $this->_tpl_vars['navigation']['4']; ?>
">
	<input type="hidden" name="mode7" id="mode7" value="">
	<input type="hidden" name="mode8" id="mode8" value="">
	<input type="hidden" name="mode9" id="mode9" value="">
	<input type="hidden" name="Action" id="Action" value="">
	<input type="hidden" id="logout">
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "help.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<div id="bodyblock" <?php if ($this->_tpl_vars['templateName'] == 'Login.tpl'): ?>align="center"<?php endif; ?> style="background-color: #FFFFFF;">
		<table <?php if ($this->_tpl_vars['templateName'] == "Login.tpl"): ?>align="center"<?php else: ?>id="bodyContainer"<?php endif; ?>>
			<tr>
				<td class="font15Bold"><?php if ($this->_tpl_vars['templateName'] != 'Login.tpl'): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['navigation']['0'])) ? $this->_run_mod_handler('replace', true, $_tmp, "&nbsp;", "") : smarty_modifier_replace($_tmp, "&nbsp;", "")); ?>
<?php endif; ?>&nbsp;</td>
			</tr>
			<tr>
				<td class="spacerHeight9" style="text-align: center;" align="center">
					<table id="errorMessageBlock" align="center" style="margin: 4px auto 10px auto; <?php if ($this->_tpl_vars['errorString'] == ''): ?>display: none;<?php elseif ($this->_tpl_vars['templateName'] == "Login.tpl"): ?>margin-left: 10px; text-align: center;<?php endif; ?>">
						<tr>
							<td style="padding: 5px; vertical-align: top;"><img src="images/alert.gif" style="border: 0px; padding: 0px; margin: 0px;"></td>
							<td style="padding: 5px 5px 5px 0px; vertical-align: middle;"><b id="br_head"><?php if ($this->_tpl_vars['errorString'] == ''): ?>Please address the fields highlighted!<?php else: ?><?php echo $this->_tpl_vars['errorString']; ?>
<?php endif; ?></b></td>
						</tr>
						<tr>
							<td style="padding: 0px; vertical-align: top;"></td>
							<td style="padding: 0px 5px 5px 0px; text-align: left;"></td>
						</tr>
					</table>
				</td>
			</tr>
						<?php 
			//echo $this->_tpl_vars['templateName'];
            if (file_exists("tmpl/".$this->_tpl_vars['templateName'].".php")){
			 ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => $this->_tpl_vars['templateName'], 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php 
			}
			else {
			 ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "404.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php 
			}
			 ?>
		</table>
	</div>
	
</form>
