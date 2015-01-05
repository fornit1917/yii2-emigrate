<?php
/**
 * This view is used by console/controllers/MigrateController.php
 * The following variables are available in this view:
 */
/* @var $className string the new migration class name */

echo "<?php\n";
?>

use yii\db\Schema;
use yii\db\Migration;

class <?= $className ?> extends Migration
{
    public function up()
    {
        <?= $codeForUp."\n" ?>
    }

    public function down()
    {
<? if (!$codeForDown): ?>
        echo "<?= $className ?> cannot be reverted!!!.\n";
        return false;
<? else: ?>
        <?= $codeForDown."\n" ?>
<? endif; ?>
    }
}
