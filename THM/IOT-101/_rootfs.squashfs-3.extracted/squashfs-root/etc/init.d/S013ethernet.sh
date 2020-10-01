#!/bin/sh
#
# All ethernet related stuff should come here, from inserting modules upto setti
#
ncecho 'Loading Ethernet module.    '

MODULE_TO_INSERT=`find /lib/modules -name ${ETH_DRIVER}`

if [ -e ${MODULE_TO_INSERT} ]; then

        ${INSMOD} ${MODULE_TO_INSERT}
        if [  $? != 0 ]; then
        	cecho red '[FAILED]'
                exit;
        fi
	if [ ${ETH_MAC_CONGIURABLE} = "yes" ]; then
		if [ -e ${MANU_BOARD_FILE} ]; then
			cecho yellow '[GENMAC]'
			ncecho '                            '
			if [ -e ${BDDATARD} ]; then
	        	${BDDATARD} ${MAC_OFFSET_4_ETH} | xargs ${IFCONFIG} ${ETH_INTERFACE} hw ether
			fi
		fi
		${IFCONFIG} ${ETH_INTERFACE} mtu ${ETH_MTU}
	fi

        cecho green '[DONE]'
else
        cecho red '[FAILED]'
fi
