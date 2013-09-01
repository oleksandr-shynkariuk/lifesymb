<?php
$alias = 'admin/customers/create';

$returnTo = ntsLink::makeLink( '-current-/../..' );
ntsLib::setVar('admin/customers/create::returnTo', $returnTo);
?>