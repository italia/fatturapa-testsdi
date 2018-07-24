<?php


 function autoload_f0424e3c2af8ede38d3a8920d83db7e0($class)
{
    $classes = array(
        'TrasmissioneFatture_service' => __DIR__ .'/TrasmissioneFatture_service.php',
        'fileSdIBase_Type' => __DIR__ .'/fileSdIBase_Type.php',
        'fileSdI_Type' => __DIR__ .'/fileSdI_Type.php',
        'rispostaSdIRiceviFile_Type' => __DIR__ .'/rispostaSdIRiceviFile_Type.php',
        'erroreInvio_Type' => __DIR__ .'/erroreInvio_Type.php'
    );
    if (!empty($classes[$class])) {
        include $classes[$class];
    };
}

spl_autoload_register('autoload_f0424e3c2af8ede38d3a8920d83db7e0');

// Do nothing. The rest is just leftovers from the code generation.
{
}
