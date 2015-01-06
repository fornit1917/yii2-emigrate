<? if ($tableOptions): ?>
$this->createTable('<?= $table ?>', [], '<?=$tableOptions?>');
<? else: ?>
$this->createTable('<?= $table ?>', []);
<? endif ?>