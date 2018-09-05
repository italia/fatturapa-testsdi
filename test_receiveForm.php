<?php
require('soap/config.php');
?>

	<form action="<?php echo HOSTNAME; ?>SdIRiceviFile/test_RiceviFile.php" method="POST" enctype="multipart/form-data">
	<input type="file" name="File" />
	<input type="text" name="NomeFile" />
	<input type="submit" name="submit" value="submit" />
</form>
