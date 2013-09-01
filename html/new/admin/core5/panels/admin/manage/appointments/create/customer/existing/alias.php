<?php
$alias = 'admin/customers/browse';

$returnTo = ntsLink::makeLink( '-current-/../..' );
ntsLib::setVar('admin/customers/browse::returnTo', $returnTo);

$ids = null;
ntsLib::setVar('admin/customers/browse::ids', $ids);
?>