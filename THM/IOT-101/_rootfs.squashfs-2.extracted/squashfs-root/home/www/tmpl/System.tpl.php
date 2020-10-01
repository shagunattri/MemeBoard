<?php /* Smarty version 2.6.18, created on 2009-10-28 14:11:07
         compiled from System.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'replace', 'System.tpl', 17, false),)), $this); ?>
	<tr>
		<td>
			<table class="tableStyle">
				<tr>
					<td colspan="3"><script>tbhdr('Access Point Information','accessPointInformation')</script></td>
				</tr>
				<tr>
					<td class="subSectionBodyDot">&nbsp;</td>
					<td class="spacer100Percent paddingsubSectionBody">
						<table class="tableStyle">
							<tr>
								<td class="DatablockLabel">Access Point Name</td>
								<td class="DatablockContent"><?php echo $this->_tpl_vars['data']['basicSettings']['apName']; ?>
</td>
							</tr>
							<tr>
								<td class="DatablockLabel">Ethernet MAC Address</td>
								<td class="DatablockContent"><?php echo ((is_array($_tmp=$this->_tpl_vars['data']['monitor']['ethernetMacAddress'])) ? $this->_run_mod_handler('replace', true, $_tmp, "-", ":") : smarty_modifier_replace($_tmp, "-", ":")); ?>
</td>
							</tr>
							<?php if (! $this->_tpl_vars['config']['DUAL_CONCURRENT']['status']): ?>
							<tr>
								<td class="DatablockLabel">Wireless MAC Address</td>
                                <?php if ($this->_tpl_vars['config']['TWOGHZ']['status'] && $this->_tpl_vars['data']['radioStatus0'] == '1'): ?>
                                    <td class="DatablockContent"><?php echo ((is_array($_tmp=$this->_tpl_vars['data']['monitor']['macAddress']['wlan0'])) ? $this->_run_mod_handler('replace', true, $_tmp, "-", ":") : smarty_modifier_replace($_tmp, "-", ":")); ?>
</td>
                                <?php endif; ?>
<!--@@@FIVEGHZSTART@@@-->
                                <?php if ($this->_tpl_vars['config']['FIVEGHZ']['status'] && $this->_tpl_vars['data']['radioStatus1'] == '1'): ?>
                                    <td class="DatablockContent"><?php echo ((is_array($_tmp=$this->_tpl_vars['data']['monitor']['macAddress']['wlan1'])) ? $this->_run_mod_handler('replace', true, $_tmp, "-", ":") : smarty_modifier_replace($_tmp, "-", ":")); ?>
</td>
                                <?php endif; ?>
<!--@@@FIVEGHZEND@@@-->
							</tr>
<!--@@@DUAL_CONCURRENTSTART@@@-->
							<?php else: ?>
							<?php if ($this->_tpl_vars['config']['TWOGHZ']['status']): ?>
							<tr>
								<td class="DatablockLabel">Wireless MAC Address for 2.4GHz</td>
								<td class="DatablockContent"><?php echo ((is_array($_tmp=$this->_tpl_vars['data']['monitor']['macAddress']['wlan0'])) ? $this->_run_mod_handler('replace', true, $_tmp, "-", ":") : smarty_modifier_replace($_tmp, "-", ":")); ?>
</td>
							</tr>
							<?php endif; ?>
<!--@@@FIVEGHZSTART@@@-->
							<?php if ($this->_tpl_vars['config']['FIVEGHZ']['status']): ?>
							<tr>
								<td class="DatablockLabel">Wireless MAC Address for 5GHz</td>
								<td class="DatablockContent"><?php echo ((is_array($_tmp=$this->_tpl_vars['data']['monitor']['macAddress']['wlan1'])) ? $this->_run_mod_handler('replace', true, $_tmp, "-", ":") : smarty_modifier_replace($_tmp, "-", ":")); ?>
</td>
							</tr>
							<?php endif; ?>
<!--@@@FIVEGHZEND@@@-->
<!--@@@DUAL_CONCURRENTEND@@@-->

							<?php endif; ?>
							<tr>
								<td class="DatablockLabel">Country / Region</td>
								<td class="DatablockContent"><?php echo $this->_tpl_vars[countryList][$this->_tpl_vars[data][basicSettings][sysCountryRegion]]; ?></td>
							</tr>
							<tr>
								<td class="DatablockLabel">Firmware Version</td>
								<td class="DatablockContent"><?php echo $this->_tpl_vars['data']['monitor']['sysVersion']; ?>
</td>
							</tr>
							<tr>
								<td class="DatablockLabel" >Current Time</td>
								<td class="DatablockContent"><?php  echo `/usr/local/bin/date.sh `; ?></td>
							</tr>
						</table>
					</td>
					<td class="subSectionBodyDotRight">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="3" class="subSectionBottom">&nbsp;</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="spacerHeight21"></td>
	</tr>
	<tr>
		<td>
			<table class="tableStyle">
				<tr>
					<td colspan="3"><script>tbhdr('Current IP Settings','currentIPSettings')</script></td>
				</tr>
				<tr>
					<td class="subSectionBodyDot">&nbsp;</td>
					<td class="spacer100Percent paddingsubSectionBody">
						<table class="tableStyle">
							<tr>
								<td class="DatablockLabel">IP Address</td>
								<td class="DatablockContent"><?php echo $this->_tpl_vars['data']['monitor']['ipAddress']; ?>
</td>
							</tr>
							<tr>
								<td class="DatablockLabel">Subnet Mask</td>
								<td class="DatablockContent"><?php echo $this->_tpl_vars['data']['monitor']['subNetMask']; ?>
</td>
							</tr>
							<tr>
								<td class="DatablockLabel">Default Gateway</td>
								<td class="DatablockContent"><?php echo ((is_array($_tmp=$this->_tpl_vars['data']['monitor']['defaultGateway'])) ? $this->_run_mod_handler('replace', true, $_tmp, '0.0.0.0', '') : smarty_modifier_replace($_tmp, '0.0.0.0', '')); ?>
</td>
							</tr>
<?php if ($this->_tpl_vars['config']['DHCPCLIENT']['status']): ?>
							<tr>
								<td class="DatablockLabel">DHCP Client</td>
								<td class="DatablockContent"><?php if ($this->_tpl_vars['data']['basicSettings']['dhcpClientStatus'] == 0): ?>Disabled<?php else: ?>Enabled<?php endif; ?></td>
							</tr>
<?php endif; ?>
						</table>
					</td>
					<td class="subSectionBodyDotRight">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="3" class="subSectionBottom">&nbsp;</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="spacerHeight21">&nbsp;</td>
	</tr>
<?php if ($this->_tpl_vars['config']['TWOGHZ']['status'] && $this->_tpl_vars['data']['radioStatus0'] == '1'): ?>
	<tr id="wlan1">
		<td>
			<table class="tableStyle">
				<tr>
					<td colspan="3" style="width: 560px;"><script>tbhdr('Current Wireless Settings for 802.11<?php echo $this->_tpl_vars['wlan0ModeString']; ?>
','currentWirelessSettings')</script></td>
				</tr>
				<tr>
					<td class="subSectionBodyDot">&nbsp;</td>
					<td class="spacer100Percent paddingsubSectionBody">
						<table class="tableStyle">
							<tr>
								<td class="DatablockLabel">Access Point Mode</td>
								<td class="DatablockContent"><?php 
								switch ($this->_tpl_vars['data']['wlanSettings']['wlanSettingTable']['wlan0']['apMode']) {
									case '0':
										echo "Access Point";
										break;
									case '1':
										echo "Point to Point";
										break;
									case '2':
										echo "Point to Point (with Client Association)";
										break;
									case '3':
										echo "Point to Multi point (with Client Association)";
										break;
									case '4':
										echo "Point to Multi point";
										break;
									case '5':
										echo "Client";
										break;
								}
								 ?></td>
							</tr>
							<tr>
								<td class="DatablockLabel">Channel / Frequency</td>
								<td class="DatablockContent"><?php if ($this->_tpl_vars['data']['wlanSettings']['wlanSettingTable']['wlan0']['channel'] == '0'): ?>Auto (<?php echo $this->_tpl_vars['data']['monitor']['currentChannel']['wlan0']; ?>
)<?php else: ?><?php echo $this->_tpl_vars['data']['monitor']['currentChannel']['wlan0']; ?>
<?php endif; ?></td>
							</tr>
<?php if ($this->_tpl_vars['config']['ROGUEAP']['status']): ?>
							<tr>
								<td class="DatablockLabel">Rogue AP Detection</td>
								<td class="DatablockContent"><?php if ($this->_tpl_vars['data']['wlanSettings']['wlanSettingTable']['wlan0']['rogueApDetection'] == '0'): ?>Disabled<?php else: ?>Enabled<?php endif; ?></td>
							</tr>
<?php endif; ?>
						</table>
					</td>
					<td class="subSectionBodyDotRight">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="3" class="subSectionBottom">&nbsp;</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="spacerHeight21">&nbsp;</td>
	</tr>
<?php endif; ?>
<!--@@@FIVEGHZSTART@@@-->
<?php if ($this->_tpl_vars['config']['FIVEGHZ']['status'] && $this->_tpl_vars['data']['radioStatus1'] == '1'): ?>
	<tr id="wlan2" >
		<td>
			<table class="tableStyle">
				<tr>
					<td colspan="3" style="width: 560px;"><script>tbhdr('Current Wireless Settings for 802.11<?php echo $this->_tpl_vars['wlan1ModeString']; ?>
','currentWirelessSettings')</script></td>
				</tr>
				<tr>
					<td class="subSectionBodyDot">&nbsp;</td>
					<td class="spacer100Percent paddingsubSectionBody">
						<table class="tableStyle">
							<tr>
								<td class="DatablockLabel">Access Point Mode</td>
								<td class="DatablockContent"><?php 
								switch ($this->_tpl_vars['data']['wlanSettings']['wlanSettingTable']['wlan1']['apMode']) {
									case '0':
										echo "Access Point";
										break;
									case '1':
										echo "Point to Point";
										break;
									case '2':
										echo "Point to Point (with Client Association)";
										break;
									case '3':
										echo "Point to Multi point (with Client Association)";
										break;
									case '4':
										echo "Point to Multi point";
										break;
								}
								 ?></td>
							</tr>
							<tr>
								<td class="DatablockLabel">Channel / Frequency</td>
								<td class="DatablockContent"><?php if ($this->_tpl_vars['data']['wlanSettings']['wlanSettingTable']['wlan1']['channel'] == '0'): ?>Auto (<?php echo $this->_tpl_vars['data']['monitor']['currentChannel']['wlan1']; ?>
)<?php else: ?><?php echo $this->_tpl_vars['data']['monitor']['currentChannel']['wlan1']; ?>
<?php endif; ?></td>
							</tr>
<?php if ($this->_tpl_vars['config']['ROGUEAP']['status']): ?>
							<tr>
								<td class="DatablockLabel">Rogue AP Detection</td>
								<td class="DatablockContent"><?php if ($this->_tpl_vars['data']['wlanSettings']['wlanSettingTable']['wlan1']['rogueApDetection'] == '0'): ?>Disabled<?php else: ?>Enabled<?php endif; ?></td>
							</tr>
<?php endif; ?>
						</table>
					</td>
					<td class="subSectionBodyDotRight">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="3" class="subSectionBottom">&nbsp;</td>
				</tr>
			</table>
		</td>
	</tr>
<?php endif; ?>
<!--@@@FIVEGHZEND@@@-->