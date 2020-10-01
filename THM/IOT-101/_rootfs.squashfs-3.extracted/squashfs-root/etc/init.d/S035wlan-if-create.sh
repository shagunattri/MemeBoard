#!/bin/sh

vap_and_wds()
{
	ncecho 'Creating vap interface.     '

		for i in `seq 0 $(expr $NO_OF_RADIOS - 1)`;do
			for j in `seq 0 $(expr $NO_OF_VAPS - 1)`; do
				${WLANCONFIG} wifi${i}vap$j create wlandev wifi${i} wlanmode ap > ${NULL_DEVICE}
				if [ ${j} = 0 ]; then
					${IFCONFIG} wifi${i}vap$j txqueuelen 200
				else	
					${IFCONFIG} wifi${i}vap$j txqueuelen 50
				fi
			done
		done
		cecho green '[DONE]'


		ncecho 'Creating wds interface.     '

		for i in `seq 0 $(expr $NO_OF_RADIOS - 1)`;do
			for j in `seq 0 $(expr $NO_OF_WDS - 1)`; do
				${WLANCONFIG} wifi${i}wds$j create wlandev wifi${i} wlanmode swds > ${NULL_DEVICE}
				${IFCONFIG} wifi${i}wds$j mtu 1504 txqueuelen 50
			done
		done
		cecho green '[DONE]'
}

client_mode()
{
		ncecho 'Creating sta interface.     '
		${RMMOD} ath_${WLAN_BUS}
		${RMMOD} bridge.ko
		${INSMOD} /lib/modules/wlan/ath_${WLAN_BUS}.ko
		${INSMOD} /lib/modules/net/client_bridge/client_bridge.ko
		${INSMOD} /lib/modules/wlan/wlan_scan_sta.ko
		${WLANCONFIG} wifi0vap0 create wlandev wifi0 wlanmode sta > ${NULL_DEVICE}
		${IFCONFIG} wifi0vap0 txqueuelen 200
		cecho green '[DONE]'
}

[ -x ${IFCONFIG} ] && [ -x ${WLANCONFIG} ] || exit

AP_MODE=`grep wlan0:apMode /var/config | cut -d ':' -f 5 | cut -d ' ' -f 2`

if [ ${CLIENT_MODE} = "yes" ]; then
	if [ ${AP_MODE} = "5" ]; then
		client_mode
	else
		vap_and_wds
	fi
else
	vap_and_wds
fi
