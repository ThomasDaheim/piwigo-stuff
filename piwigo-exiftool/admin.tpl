<!-- Show the title of the plugin -->
<div class="titlePage">
 <h2>{'Exiftool plugin'|@translate}</h2>
</div>
 
<!-- Show content in a nice box -->
<form method="post" action="" class="properties">
	<fieldset>
		<legend>{'Configuration'|@translate}</legend>

		<table>
		  <tr>
			<td align="right">{'exiftool path'|@translate} : &nbsp;&nbsp;</td>
			<td><input type="text" size="100" maxlength="100" name="exiftool_path" value="{$exiftool_path}"></td>
		  </tr>
		  <tr>
			<td align="right">{'exiftool flags'|@translate} : &nbsp;&nbsp;</td>
			<td><input type="text" size="100" maxlength="100" name="exiftool_flags" value="{$exiftool_flags}"></td>
		  </tr>
		</table>
	</fieldset>

	<p>
		<input class="submit" type="submit" value="{'Save Settings'|@translate}" name="submit"/>
	</p>
</form>
