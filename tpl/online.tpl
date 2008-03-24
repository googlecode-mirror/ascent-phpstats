<td>&nbsp;</td></tr>
<tr>
	<td><center>{OPT_SLIST}</center></td>
</tr>
<tr>
	<td>
	<table width="100%" border="0" cellspacing="1" cellpadding="2">
		<tr class="head">
			<th colspan="7">Current Instances</th>
		</tr>
		<tr>
			<th><a href="?m=onl&amp;serv={OPT_SERV}&amp;mord=map,{so_1}">Map (#)</a></th>
			<th><center><a href="?m=onl&amp;serv={OPT_SERV}&amp;mord=players,{so_2}">Players</a></center></th>
			<th><center><a href="?m=onl&amp;serv={OPT_SERV}&amp;mord=maxplayers,{so_3}">Player Limit</a></center></th>
			<th><center><a href="?m=onl&amp;serv={OPT_SERV}&amp;mord=maptype,{so_4}">Type</a></center></th>
			<th style="text-align:right">Creation Time</th>
			<th style="text-align:right">Expiry Time</th>
		</tr>
<!--BeginInst-->
		<tr>
			<td>
			{map_name} ({map})</td>
			<td align="center">{Inst_players}</td>
			<td align="center">{Inst_maxplayers}</td>
			<td align="center">{Inst_World}</td>
			<td align="right">{Inst_creationtime}</td>
			<td align="right">{Inst_expirytime}</td>
		</tr>
<!--EndInst-->
	</table>

	<table width="100%" border="0" cellspacing="1" cellpadding="2">
		<tr class="head">
			<th colspan="8">Online GMs</th>
		</tr>
		<tr>
			<th><a href="?m=onl&amp;serv={OPT_SERV}&amp;pord=name,{so_5}">Name</a></th>
			<th><center><a href="?m=onl&amp;serv={OPT_SERV}&amp;pord=race,{so_6}">Race</a></center></th>
			<th><center><a href="?m=onl&amp;serv={OPT_SERV}&amp;pord=class,{so_7}">Class</a></center></th>
			<th><center><a href="?m=onl&amp;serv={OPT_SERV}&amp;pord=level,{so_8}">Level</a></center></th>
			<th><center>Permissions</center></th>
			<th>Online Time</th>
			<th style="text-align:right"><a href="?m=onl&amp;serv={OPT_SERV}&amp;pord=latency,{so_9}">Latency</a></th>
		</tr>

<!--BeginGM-->
		<tr>
			<td>{GM_name}</td>
			<td align="center">
				<img src="icon/race/{GM_race}-{GM_gender}.gif" alt="{GM_race_name}" />
			</td>
			<td align="center"><img src="icon/class/{GM_class}.gif" alt="{GM_class_name}" /></td>
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
			<th><a href="?m=onl&amp;serv={OPT_SERV}&amp;pord=name,{so_5}">Name</a></th>
			<th><center><a href="?m=onl&amp;serv={OPT_SERV}&amp;pord=race,{so_6}">Race</a></center></th>
			<th><center><a href="?m=onl&amp;serv={OPT_SERV}&amp;pord=class,{so_7}">Class</a></center></th>
			<th><center>Rank</center></th>
			<th><center><a href="?m=onl&amp;serv={OPT_SERV}&amp;pord=level,{so_8}">Level</a></center></th>
			<th><a href="?m=onl&amp;serv={OPT_SERV}&amp;pord=map,{so_10}">Map</a></th>
			<th><a href="?m=onl&amp;serv={OPT_SERV}&amp;pord=areaid,{so_11}">Zone</a></th>
			<th>Online Time</th>
			<th style="text-align:right"><a href="?m=onl&amp;serv={OPT_SERV}&amp;pord=latency,{so_9}">Latency</a></th>
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
</td>