#!/bin/bash
\cp -f icecast.php /usr/local/cwpsrv/htdocs/resources/admin/modules/
#useradd -M icecast
yum -y install icecast
\cp -f icecast.temp /usr/share/icecast

if ! grep -q "\-- cwp_icecast --" /usr/local/cwpsrv/htdocs/resources/admin/include/3rdparty.php
then
cat <<'EOF' >> /usr/local/cwpsrv/htdocs/resources/admin/include/3rdparty.php
<!-- cwp_icecast -->
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
<!-- end cwp_icecast -->
EOF
fi
