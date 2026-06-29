<?php

echo "test"."<br/>";

echo  "DOCUMENT_ROOT: ". $_SERVER['DOCUMENT_ROOT']."<br/>";
echo  "__DIR__ : ". __DIR__ ."<br/>";
echo  "__DIR__ : ". __DIR__ . '/../'."<br/>";
echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/')."<br/>";