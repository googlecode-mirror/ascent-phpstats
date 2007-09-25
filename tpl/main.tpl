	<html>
	<head>
		<title>Server Status Page</title>
		<meta http-equiv="Pragma" content="no-cache"/>
		<meta http-equiv="Cache-Control" content="no-cache"/>
		<style type="text/css" media="screen">@import url(server_stats.css);</style>
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
	</table>
	
	<div style="margin-bottom:20px">

	<form action="?do=reg" method="post" target="_self">
  		<table  width="100%" border="0" cellpadding="0" cellspacing="0" style="border-top-style: none;border-right-style: none;border-bottom-style: none;border-left-style: none;">
		<tr>
		  <td valign="top" nowrap><table width="100%" height="100%" border="0" align="left" style="margin-bottom:0px;">
            <tr class="head">
              <th height="21%" align="left" valign="top" scope="col">Registration</th>
            </tr>
            <tr align="center">
              <td height="31%" valign="top"><label>Account &nbsp;&nbsp;&nbsp;&nbsp;
                    <input name="reg_name" type="text" id="reg_name" value="{FormReg_user}" maxlength="16">
                </label>              </td>
            </tr>
            <tr>
              <td height="19%" align="center" valign="top"><label>Password&nbsp;
                    <input name="reg_password" type="password" id="reg_password" value="{FormReg_password}" maxlength="32">
                </label>              </td>
            </tr>
            <tr>
              <td height="19%" align="center" valign="top"><label>E-mail&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <input name="reg_email" type="text" id="reg_email" value="{FormReg_email}" maxlength="32">
                </label>              </td>
            </tr>
            <tr align="center">
              <td height="29%" valign="top"><input name="Submit" type="submit" class="button" value="Submit"></td>
            </tr>
            <tr align="center">
              <td valign="top">{FormReg_msg}</td>
            </tr>
          </table></td>
		  <td nowrap>
		<table width="100%" height="100%" border="0" style="margin-bottom:0px;">
		<tr class="head">
			<th colspan="12">Info</th>
		</tr>

		<tr>
			<th width="27%" height="24">Accounts</th>
			<td width="11%" align="center">{S_1}</td>
			<th width="18%">Alliance</th>
			<td width="12%" align="center" nowrap>{S_2}</td>
			<th width="19%">Horde</th>
			<td width="13%" align="center" nowrap>{S_3}</td>
		</tr>
		<tr>
			<th>Characters</th>
			<td align="center">{S_4}</td>
			<th>Human</th>
			<td align="center" nowrap>{S_5}</td>
			<th>Undead</th>
			<td align="center" nowrap>{S_24}</td>
		</tr>
		<tr>
			<th>GameMaster</th>
			<td align="center">{S_6}</td>
			<th>Dwarf</th>
			<td align="center" nowrap>{S_7}</td>
			<th>Tauren</th>
			<td align="center" nowrap>{S_8}</td>
		</tr>
		<tr>
			<td></td>
			<td align="center"></td>
			<th>Draenei</th>
			<td align="center" nowrap>{S_10}</td>
			<th>Blood Elf</th>
			<td align="center" nowrap>{S_11}</td>
		</tr>
		<tr>
			<td></td>
			<td align="center"></td>
			<th>Night Elf</th>
			<td align="center" nowrap>{S_13}</td>
			<th>Orc</th>
			<td align="center" nowrap>{S_14}</td>
		</tr>
		<tr>
			<td></td>
			<td align="center"></td>
			<th>Gnom</th>
			<td align="center" nowrap>{S_16}</td>
			<th>Troll</th>
			<td align="center" nowrap>{S_17}</td>
		</tr>
		</table>
		<table width="100%" height="100%" border="0" style="margin-bottom:0px;">
		<tr class="head">
			<th colspan="12">Classes</th>
		</tr>
		<tr>
			<th>Mage</th>
			<td align="center">{S_21}</td>
			<th>Warlock</th>
			<td align="center">{S_22}</td>
			<th>Druid</th>
			<td align="center">{S_23}</td>
		</tr>
		<tr>
			<th>Rogue</th>
			<td align="center">{S_18}</td>
			<th>Priest</th>
			<td align="center">{S_19}</td>
			<th>Shaman</th>
			<td align="center">{S_20}</td>
		</tr>
		<tr>
			<th>Warrior</th>
			<td align="center">{S_15}</td>
			<th>Hunter</th>
			<td align="center">{S_12}</td>
			<th>Paladin</th>
			<td align="center">{S_9}</td>
		</tr>
		</table>		
		</td>
		</tr>
		</table>	
	</form>
	<table width="100%" border="0" cellspacing="1" cellpadding="2">
		<tr class="head">
			<th colspan="7">Current Instances</th>
		</tr>
		<tr>
			<th>Map (#)</th>
			<th><center>Players</center></th>
			<th><center>Player Limit</center></th>
			<th><center>State</center></th>
			<th><center>Type</center></th>
			<th style="text-align:right">Creation Time</th>
			<th style="text-align:right">Expiry Time</th>
		</tr>
<!--BeginInst-->
		<tr>
			<td>
			{map_name} ({map})</td>
			<td align="center">{Inst_players}</td>
			<td align="center">
				{Inst_maxplayers}
			</td>
			<td align="center">{Inst_state}</td>
			<td align="center">
			{Inst_World}
            </td>
			<td align="right">{Inst_creationtime}</td>
			<td align="right">{Inst_expirytime}</td>
		</tr>
<!--EndInst-->
	</table>
	</div>

	<table width="100%" border="0" cellspacing="1" cellpadding="2">
		<tr class="head">
			<th colspan="8">Online GMs</th>
		</tr>
		<tr>
			<th>Name</th>
			<th><center>Race</center></th>
			<th><center>Class</center></th>
			<th><center>Level</center></th>
			<th><center>Permissions</center></th>
			<th>Online Time</th>
			<th style="text-align:right">Latency</th>
		</tr>

<!--BeginGM-->
		<tr>
			<td>{GM_name}</td>
			<td align="center">
				<img src="icon/race/{GM_race}-{GM_gender}.gif" alt="{GM_race_name}" />
			</td>
			<td align="center">
				<img src="icon/class/{GM_class}.gif" alt="{GM_class_name}" />
			</td>
			<td align="center">{GM_level}</td>
			<td align="center">[{GM_permissions}]</td>
			<td>{GM_ontime}</td>
			<td align="right">{GM_ms} ms</td>
		</tr>
<!--EndGM-->
	</table>
	<table width="100%" border="0" cellspacing="1" cellpadding="2">
		<tr class="head">
			<th colspan="9">Online Players</th>
		</tr>
		<tr>
			<th>Name</th>
			<th><center>Race</center></th>
			<th><center>Class</center></th>
			<th><center>Rank</center></th>
			<th><center>Level</center></th>
			<th>Map</th>
			<th>Zone</th>
			<th>Online Time</th>
			<th style="text-align:right">Latency</th>
		</tr>
<!--BeginPL-->
		<tr>
			<td>{PL_name}</td>
			<td align="center">
				<img src="icon/race/{PL_race}-{PL_gender}.gif" alt="{PL_race_name}" />
			</td>
			<td align="center">
				<img src="icon/class/{PL_class}.gif" alt="{PL_class_name}" />
			</td>
			<td align="center">
				<img src="icon/pvpranks/rank_default_{PL_faction}.gif" alt="{PL_faction_name}" />
			</td>
			<td align="center">
				{PL_level}
			</td>
			<td>
				{PL_map}
			</td>


			<td>
				{PL_areaid}
			</td>

			<td nowrap="nowrap">{PL_ontime}</td>
			<td nowrap="nowrap" align="right">{PL_latency} ms</td>
		</tr>
<!--EndPL-->
</table>
		</div>
		<div class="footer">
			{PS_INFO}<br>
			PHP version By Zacki ( backmen@bk.ru ), original design by mmorpg4free.com, styling by PayBas @ emupedia.com.
		</div>
		</center>
	</body>
	</html>