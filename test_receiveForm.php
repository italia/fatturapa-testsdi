<?php
require('soap/config.php');
?>

<form action="<?php echo HOSTNAME; ?>SdIRiceviFile/test_RiceviFile.php" method="POST" enctype="multipart/form-data">
	<input type="file" name="File" />
	<input type="text" name="NomeFile" />
	<input type="submit" name="submit" value="submit" />
</form>
<h2>/timestamp</h2>
<form action="<?php echo BASENAME; ?>rpc/timestamp" method="POST" enctype="multipart/form-data">
	<input type="text" name="timestamp" value="2019-01-01 00:00:00" />	
	<input type="submit" name="submit" value="submit" />
</form>
<h2>/speed</h2>
<form action="<?php echo BASENAME; ?>rpc/speed" method="POST" enctype="multipart/form-data">
	<input type="text" name="speed" value="3600" />	
	<input type="submit" name="submit" value="submit" />
</form>