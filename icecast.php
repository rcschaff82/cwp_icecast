<?php
if ( !isset( $include_path ) )
{
    echo "invalid access";
    exit( );
}


class IceCast {
        private $dir = "/home/icecast";
        private $status_s2 = "";
        private $version_s2 = "";
        private $github_branches= "https://api.github.com/repos/rcschaff82/cwp_icecast/branches";
        private $github_url = "https://api.github.com/repos/rcschaff82/cwp_icecast/commits?per_page=1&sha=";
        public function __construct()
        {
                echo '<center><b>IceCast Module</b></center><br>';
        }
        public function initalize()
        {
                ///  This is the main function
                $this->check_is_s2_loaded();
                //$this->date_last_commit();
        }
        public function get_s2_version()
        {
                $s2_version = shell_exec("icecast -v");
                $this->version_s2 = $s2_version;
        }
        public function check_is_s2_loaded()
        {
		global $mysql_conn;
                $sql= "SELECT COUNT(*) FROM information_schema.tables WHERE table_name = 'icecast';";
                $resp = mysqli_query($mysql_conn,$sql);
		$row = mysqli_fetch_array($resp);
                $php = shell_exec("sudo -u icecast command -v icecast");
        if ($php && $row[0] == 1) {
                $this->get_s2_version();
                //$this->alert = "alert-success";
                //$this->message = "<strong>Success!</strong><br>$this->version_s2";
                //$this->toHtml();
                echo "Version: " . $this->version_s2;
		$this->add_server();
                $this->list_server();
        }
        else {
           $this->alert = "alert-info";
            $this->message = "<strong>Info!</strong> IceCast Is not installed.<br>
            To install the IceCast follow the guidelines below, or use the auto install script.";

            //Show Help Installation
            $this->toHtml();
            $this->message_install();
        }
        }
        private function list_server() {
global $mysql_conn;
$sql="select * from icecast";
$resp=mysqli_query($mysql_conn,$sql);
$servers = array();
while($row = mysqli_fetch_assoc($resp)) {
$servers[$row['port']] = array("port"=>$row['port'], "user"=>$row['user'],"conf"=>$row['config']);
}


switch(@$_POST['action']) {
case "srv_start":
$filer =  @$_POST['srv_id'];
$cmd = "/home/{$servers[$filer]['user']}/$filer.conf";
$temp = shell_exec("sudo -u {$servers[$filer]['user']} /usr/bin/icecast -b -c $cmd");
echo "Starting";
sleep(4);
break;

case "srv_stop":
$filer =  @$_POST['srv_id'];
echo "Stoppping<br>";
$cmd = "kill `cat /home/{$servers[$filer]['user']}/icecast/$filer.pid`";
shell_exec($cmd);
sleep(4);
break;

case "srv_delete":
$filer =  @$_POST['srv_id'];
echo "Deleting";
$port2 = intval($filer) + 1;
$cmd = "kill `cat /home/{$servers[$filer]['user']}/icecast/$filer.pid`";
shell_exec($cmd);
sleep(4);
unlink("/home/{$servers[$filer]['user']}/$filer.conf");
copy("/etc/csf/csf.conf","/etc/csf/csf.conf.bu");
shell_exec("sed -i 's/,".$filer.",".$port2."//g' /etc/csf/csf.conf");
mysqli_query($mysql_conn,"DELETE from icecast where port=$filer;");
break;
}
                echo <<<EOS
                <div class="slimScrollDiv" style="position: relative; overflow: hidden; width: 100%; height: auto;"><div class="table-responsive" style="overflow: hidden; width: 100%; height: auto;"><table class="table table-bordered">
<thead><tr>
<th>Server Status</th>
<th>User Name</th>
<th>Edit Server</th>
<th>Start Server</th>
<th>Stop Server</th>
<th>Admin Panel</th>
<th>Delete Server</th>
</tr></thead>

<tbody>
EOS;
$hostname = get_hostname();
foreach((array) $servers as $port=>$val) {
	$user= $val['user'];
	$full= $val['conf'];
	$base = basename($full);
	 $online = (file_exists("/home/$user/icecast/$port.pid"))? "checkmark":"close";
echo <<<EOS
                                <tr><td><span title="Status" class="icon12 minia-icon-$online"></span></td>
				<td>$user</td>
				<td><a href="index.php?module=file_editor&amp;file=$full">$base</a></td>	
                                <td><form action="" method="post" onsubmit="return confirm('Are you sure you want to start server: $base ?');"><input type="hidden" name="srv_id" value="$port" size="0"><input type="hidden" name="action" size="0" value="srv_start"><div class="form-group"><button type="submit" class="btn btn-success btn-xs">Start</button></div></form></td>
                                <td><form action="" method="post" onsubmit="return confirm('Are you sure you want to stop server: $base ?');"><input type="hidden" name="srv_id" value="$port" size="0"><input type="hidden" name="action" size="0" value="srv_stop"><div class="form-group"><button type="submit" class="btn btn-warning btn-xs">Stop</button></div></form></td>
                                <td><div class="form-group"><button type="submit" onclick="location.href='//{$hostname}:$port'" class="btn btn-default btn-xs">Admin Panel</button></div></td>
                                <td><form action="" method="post" onsubmit="return confirm('Are you sure you want to delete server: $base ?');"><input type="hidden" name="srv_id" value="$port" size="0"><input type="hidden" name="action" size="0" value="srv_delete"><div class="form-group"><button type="submit" class="btn btn-danger btn-xs">Delete</button></div></form></td></tr>
EOS;
}
echo <<<EOS
                        </tbody></table></div><div class="slimScrollBar ui-draggable" style="background: rgb(243, 243, 243); height: 5px; position: absolute; bottom: 3px; opacity: 0.4; display: none; border-radius: 5px; z-index: 99; width: 1117px;"></div><div class="slimScrollRail" style="width: 100%; height: 5px; position: absolute; bottom: 3px; display: none; border-radius: 5px; background: rgb(51, 51, 51); opacity: 0.3; z-index: 90;"></div></div>


EOS;


        }
        ///  Should be done ///   ip -4 addr | grep -oP '(?<=inet\s)\d+(\.\d+){3}'
        private function add_server() {
		$ips = networking_inet_ips();
		$selectuser = "<label>UserAcct</label><select name='user'>";
                global $mysql_conn;
		$resp=mysqli_query($mysql_conn,"Select `username` FROM `user`");
                while($row=mysqli_fetch_assoc($resp)) {
			$selectuser .= "<option value='{$row['username']}'>{$row['username']}</option>";
                }
		$selectuser .="</select><br>";
                $hostname = get_hostname();
                $selectip = "<label>Src/DstIP</label><select name='ip'><option value='ALL'>ALL</option>";
                foreach($ips as $ip) {
                        $nat = (ipv4_manage_is_ip_private( trim($ip)))?"*":"";
                        $selectip .= "<option value='$ip'>$ip$nat</option>";
                }
                $selectip .="</select><br>";

echo <<<EOT
<form method="post">
<input type="hidden" name="doadd" value="start">
<label>Port</label><input type="input" name="port" value="8000"><br>
<label>DJ Password</label><input type="input" name="pass" value=""><br>
<label>Admin Pass</label><input type="input" name="admin" value=""><br>
<label>Max Clients</label><input type="input" name="numclients" value="100"> *Max 100<br>
<label>Max Sources</label><input type="input" name="sources" value="1"> *Max 3<br>
{$selectip}
{$selectuser}
<button type="submit" name="submit" value="submit">Add a Server</button>

</form>

EOT;

                 if(@$_POST['doadd'] == 'start') {
                        $port = intval($_POST['port']);
                        $port2 = $port + 1;
			$pass = $_POST['pass'];
                        $admin = $_POST['admin'];
			$clients = $_POST['numclients'];
			$sources = $_POST['sources'];
			$ip = $_POST['ip'];
			$user = $_POST['user'];
		        global $mysql_conn;
			$sql= "select * from icecast where port='$port';";
        	        $resp = mysqli_query($mysql_conn,$sql);

			if (($se = shell_exec("netstat -paln | grep LISTEN | grep -oP :$port")) != "") {
				$this->throwError("$port already in use by another application!");
				return false;
			} 
			if (($row = mysqli_fetch_assoc($resp)) > 0){
				$this->throwError("$port already in use");
				return false;
			} 
			if ($clients > 100 || $clients == "") {
				$this->throwError("Invalid number of Clients!");
				return false;
			}
			if ($sources > 3 || $sources == "") {
				$this->throwError("Invalid number of Sources!");
                                return false;
			}
                        if (strlen($admin) < 6 || strlen($pass) < 6) {
                                $this->throwError("Passwords must be at least 6 characters!");
                                return false;
                        }
                        if ($admin == $pass) {
                                $this->throwError("You cannot use the same password for both DJ and Admin");
                                return false;
                        }

                        if ($port % 2 != 0) {
                                $this->throwError("$port should only be an even number!");
                                return false;

                        }
$data = file_get_contents("/usr/share/icecast/icecast.temp");
$data = str_replace("{{hostname}}",$hostname, $data);
$data = str_replace("{{numclients}}",$clients, $data);
$data = str_replace("{{sources}}",$sources, $data);
$data = str_replace("{{srcpass}}",$pass, $data);
$data = str_replace("{{admpass}}",$admin, $data);
$data = str_replace("{{port}}",$port, $data);
$data = str_replace("{{user}}",$user, $data);
if ($ip != "ALL") {
   $data = str_replace("{{bindaddr}}","<bind-address>$ip</bind-address>",$data);
} else {
   $data = str_replace("{{bindaddr}}","",$data);
}

file_put_contents("/home/$user/$port.conf",$data);
@mkdir("/home/$user/icecast",0700);
chown("/home/$user/icecast", $user);
chgrp("/home/$user/icecast", $user);
chown("/home/$user/$port.conf", $user);
chgrp("/home/$user/$port.conf", $user);
$sql = "insert into icecast (user, port, config) values ('$user', '$port', '/home/$user/$port.conf')";
mysqli_query($mysql_conn, $sql);

                copy("/etc/csf/csf.conf","/etc/csf/csf.conf.bu");
		shell_exec('sed -i -re "s@TCP_IN(.*)(\")@TCP_IN\1,'.$port.','.$port2.'\2@" /etc/csf/csf.conf');
		shell_exec('sed -i -re "s@TCP_OUT(.*)(\")@TCP_OUT\1,'.$port.','.$port2.'\2@" /etc/csf/csf.conf');
		shell_exec('sed -i -re "s@TCP6_IN(.*)(\")@TCP6_IN\1,'.$port.','.$port2.'\2@" /etc/csf/csf.conf');
		shell_exec('sed -i -re "s@TCP6_OUT(.*)(\")@TCP6_OUT\1,'.$port.','.$port2.'\2@" /etc/csf/csf.conf');
		}
        }


/////  Completed   ////
        private function message_install()
        {
        echo <<<EOD
        <form method="post">
        <input type="hidden" name="install" value="start">
        <button type="submit" name="submit" value="submit">Install IceCast</button>
        </form>

EOD;
         if(@$_POST['install'] == 'start') {
                 shell_exec("useradd -M icecast");
                shell_exec("yum -y install icecast");
		global $mysql_conn;
		$sql= "CREATE TABLE icecast (user VARCHAR(20), port VARCHAR(20), config TEXT);";
		$resp = mysqli_query($mysql_conn,$sql);
                echo "Please refresh the module";
        }

        }
	public function throwError($error="") 
	{
			echo '<div class="alert alert-error" style="background-color:#ff6257; max-width:400px">
                                <a class="close" data-dismiss="alert">×</a>
                                <strong>Error</strong><br>'.$error.'
                               </div>';
	}
        public function toHtml()
        {
                        echo '<div class="alert '.$this->alert.'">
                                <a class="close" data-dismiss="alert">×</a>
                                '.$this->message.'
                               </div>';
        }
//////    Nothing below this line is used   ///////
        public function date_last_commit()
        {
                $context = stream_context_create(array(
                  'http' => array(
                        'header'=> "User-Agent: http://mikeangstadt.name\r\n"
                  )
                ));

                $branches = @json_decode(file_get_contents($this->github_branches, false, $context));
                //var_dump($branches);
                $response = @file_get_contents($this->github_url.$branches[0]->commit->sha, false, $context);

                if ($response === false){
                  //throw new Exception("Error contacting github.");
                }

                //parse the JSON
                $json = json_decode($response);
                if ($json === null){
                  //throw new Exception("Error parsing JSON response from github.");
                }
                if (isset($json->error)){
                  throw new Exception($json->error);
                }

                $date = new DateTime($json[0]->commit->author->date);

                $this->date_last_commits = $date->format("Y-m-d H:i:s");
                $this->last_commits_message = $json[0]->commit->message;

                $this->update();
                //return $date->format("Y-m-d H:i:s");
        }

        public function update()
        {

                echo '<center><b>Github last commits date:</b><br>';
                echo $this->date_last_commits.'<br>';
                echo $this->last_commits_message.'<br><br>';

                if(@$_POST['update'] == 'start') {
                $this->message_install();
                } else {
                echo '<div class="btn-group">
                      <form action="index.php?module=icecast" method="POST">
                          <input type="hidden" name="update" value="start">
                          <button class="btn btn-warning">Show Update Instruction</button>
                      </form>
                      </div></center><br>';
                }

        }


}
try {
        include_once('update_class.php');
        $update = new gitupdate('rcschaff82','cwp_dnsreport');
        $force = (isset($_GET['forceupdate']))?'Y':'N';
        $update->checkupdate($force);
} catch (exception $e) {
        $exception = $e->getMessage();
}

$icecast = new IceCast();
$icecast->initalize();
?>
