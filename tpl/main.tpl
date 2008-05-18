<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Server Status Page</title>
		<meta http-equiv="Pragma" content="no-cache"/>
		<meta http-equiv="Cache-Control" content="no-cache"/>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- CSS Stylesheet -->
<style type="text/css">
<!--
{CSS}
-->
</style>

<!-- / CSS Stylesheet -->
	</head>
	<body>
		<center>
		<div style="width:770px">
			{PAGE_logo}
	<table width="100%" border="0" cellspacing="1" cellpadding="3">
		<tr class="head"><th colspan="4">Server Status</th></tr>
		<tr>
			<th>Platform: </th><td>{platform}</td>
			<th>Uptime: </th><td>{uptime}</td>
		</tr>
		<tr>
			<th>Online Players: </th><td>{oplayers}</td>
			<th>CPU Usage: </th><td>{cpu}%</td>
		</tr>
		<tr>
			<th>Queued Players: </th><td>{qplayers}</td>
			<th>Memory Usage: </th><td>{ram} MB</td>
		</tr>
		<tr>
			<th>Average Latency: </th><td>{avglat} ms</td>
			<th>Thread Count: </th><td>{threads}</td>
		</tr>
		<tr>
			<th>Online GM Count: </th><td>{gmcount}</td>
			<th>Accepted Connections: </th><td>{acceptedconns}</td>
		</tr>
		<tr>
			<th>Alliance Online: </th><td>{alliance}</td>
			<th>Connection Peak: </th><td>{peakcount}</td>
		</tr>
		<tr>
			<th>Horde Online: </th><td>{horde}</td>
			<th>Last Update: </th><td>{lastupdate}</td>
		</tr>
		<tr>
			<td colspan="12" align="left">
			<center>{MOD_LINKS}</center>		</td>
		</tr>
	</table>
	

	<table width="100%" border="0" cellspacing="1" cellpadding="2">
		<tr class="head">
			<th colspan="7">{MOD_NAME}</th>
		</tr>
		<tr>
{MOD_PAGE}
		</tr>
		</table>	
		</div>
		<div class="footer">
			{PS_INFO} <br />
			PHP version By Zacki ( backmen@bk.ru ), original design by mmorpg4free.com, styling by PayBas @ emupedia.com.
		</div>
	</center></body>
	</html>