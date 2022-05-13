<?php

print_r($_REQUEST);
$postdata = http_build_query($_POST);
print_r($postdata);
?>
