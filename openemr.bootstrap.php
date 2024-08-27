<?php

namespace OpenEMR\Modules\CustomModuleCdss;

use OpenEMR\Module\CustomModuleCdss\Bootstrap;

require_once "src/Bootstrap.php";
/**
 * @global EventDispatcher $eventDispatcher Injected by the OpenEMR module loader;
 */

$bootstrap = new Bootstrap($eventDispatcher, $GLOBALS['kernel']);
$bootstrap->subscribeToEvents();
