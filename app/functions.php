<?php
// 取消内存限制
ini_set('memory_limit', '-1');
// gzip
ob_start("ob_gzhandler");
