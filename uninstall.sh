#!/bin/bash
\rm -f /usr/local/cwpsrv/htdocs/resources/admin/modules/icecast.php
yum -y remove icecast
\rm -f /usr/share/icecast/icecast.temp
# Remove From Menu
sd=$(grep -n "<\!-- cwp_icecast --" /usr/local/cwpsrv/htdocs/resources/admin/include/3rdparty.php | cut -f1 -d:)
ed=$(grep -n "<\!-- end cwp_icecast --" /usr/local/cwpsrv/htdocs/resources/admin/include/3rdparty.php | cut -f1 -d:)
cmd="$sd"",""$ed""d"
sed -i.bak -e "$cmd" /usr/local/cwpsrv/htdocs/resources/admin/include/3rdparty.php
while true; do
    read -p "Do you wish to remove all user data?" yn
    case $yn in
        [Yy]* ) 
		 mysql -Droot_cwp -e "select config from icecast" | while IFS= read -r loop
                        do
                                \rm -f $loop
                        done
		mysql -Droot_cwp -e "drop table if exists icecast"
		break;;
        [Nn]* ) break;;
        * ) echo "Please answer yes or no.";;
    esac
done

