#!/bin/bash
cp icecast.php /usr/local/cwpsrv/htdocs/resources/admin/modules/
useradd -m icecast
cd /home/icecast
wget http://download.nullsoft.com/shoutcast/tools/sc_serv2_linux_x64-latest.tar.gz
tar -xzf sc_serv2_linux_x64-latest.tar.gz
cat <<'EOF' >> /usr/local/cwpsrv/htdocs/resources/admin/include/3rdparty.php
<noscript>
</ul>
<li class="custom-menu"> <!-- this class "custom-menu" was added so you can remove the Developer Menu easily if you want -->
    <a href="?module=icecast"><span class="icon16 icomoon-icon-volume-high"></span>Icecast</a>
</li>
<li style="display:none;"><ul>
</noscript>
<script type="text/javascript">
        $(document).ready(function() {
                var newButtons = ''
                +' <li>'
                +' <a href="?module=icecast" class=""><span aria-hidden="true" class="icon16 icomoon-icon-volume-high"></span>Icecast</a>'
                +'</li>';
                $("li#mn-3").before(newButtons);
        });
</script>
EOF
