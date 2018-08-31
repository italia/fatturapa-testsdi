<?php

?>
<form action="/sdi/soap/SdIRiceviFile/" method="POST" enctype="multipart/form-data">
    <label for="File">File</label>
    <input type="file" name="File" id="File"/>
    <br/>
    <label for="NomeFile">Nome File</label>
    <input type="text" name="NomeFile" id="NomeFile"/>
    <br/>
    <input type="submit" name="submit" value="submit" />
</form>
