<?php

XCube_DelegateUtils::call('Xoonips.Download.Access');

$_GET['action'] = 'Download';
$root = &XCube_Root::getSingleton();
$root->mController->execute();
