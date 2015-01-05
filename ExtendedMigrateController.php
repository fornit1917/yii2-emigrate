<?php
namespace fornit1917\yii2emigrate;

use yii\console\controllers\MigrateController;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

class ExtendedMigrateController extends MigrateController
{
    /**
     * @var string command of migration (create-table, drop-table, add-column, drop-column or empty)
     */
    public $command = null;

    /**
     * @var string|null table name for any command
     */
    public $table = null;

    /**
     * @var string|null options for command create-table. Default is options for mysql/innodb/utf8
     */
    public $tableOptions = 'ENGINE=InnoDB CHARSET=utf8';

    /**
     * @var string|null column name for commands add-column, drop-column
     */
    public $column = null;

    /**
     * @var string type of column for command add-column
     */
    public $columnType = '';

    /**
     * @inheritdoc
     */
    public $templateFile = '';

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return array_merge(
            parent::options($actionID),
            ($actionID == 'create') ? ['command', 'columnType', 'column', 'tableOptions', 'table'] : [] // extended options for action create
        );
    }

    /**
     * @inheritdoc
     */
    public function actionCreate($name)
    {
        if (!$this->templateFile) {
            $this->templateFile = __DIR__.'/views/migration.php';
        }

        if (!preg_match('/^\w+$/', $name)) {
            throw new Exception("The migration name should contain letters, digits and/or underscore characters only.");
        }

        $params = $this->getParamsFromName($name);
        $this->addParamsFromCommandLine($params);
        //print_r($params);die;

        $name = 'm' . gmdate('ymd_His') . '_' . $name;
        $file = $this->migrationPath . DIRECTORY_SEPARATOR . $name . '.php';

        if ($this->confirm("Create new migration '$file'?")) {
            $content = $this->renderFile(Yii::getAlias($this->templateFile), [
                'className'   => $name,
                'codeForUp'   => $this->getCodeForUp($params),
                'codeForDown' => $this->getCodeForDown($params)
            ]);
            file_put_contents($file, $content);
            $this->stdout("New migration created successfully.\n", Console::FG_GREEN);
        }
    }

    protected function getParamsFromName($name)
    {
        //it's CREATE TABLE or DROP TABLE
        preg_match('/^(create|drop)_table_(.+)$/i', $name, $matches);
        if ($matches)
        {
            return [
                'command' => $matches[1] == 'create' ? 'create-table' : 'drop-table',
                'table' => $matches[2],
            ];
        }
        //it's ADD COLUMN or DROP COLUMN
        preg_match('/^(add|drop)_column_(.+)_in_(.+)$/i', $name, $matches);
        if ($matches)
        {
            return [
                'command' => $matches[1] == 'add' ? 'add-column' : 'drop-column',
                'column' => $matches[2],
                'table' => $matches[3],
            ];
        }

        return [
            'command' => ''
        ];
    }

    protected function addParamsFromCommandLine(&$params)
    {
        if ($this->command !== null) {
            $params['command'] = $this->command;
        }
        if (!isset($params['command'])) {
            return;
        }

        switch ($params['command']) {
            case 'create-table':
            case 'drop-table':
                $params['tableOptions'] = $this->tableOptions;
                if ($this->table !== null) {
                    $params['table'] = $this->table;
                }
                break;

            case 'add-column':
            case 'drop-column':
                if ($this->table !== null) {
                    $params['table'] = $this->table;
                }
                if ($this->column !== null) {
                    $params['column'] = $this->column;
                }
                $params['columnType'] = $this->columnType;
                break;
        }
    }

    protected function isSupportedCommand($command)
    {
        return in_array($command, [
            'create-table',
            'drop-table',
            'add-column',
            'drop-column'
        ]);
    }

    protected function getCodeForUp($params)
    {
        switch ($params['command']) {
            case 'create-table':
                return $this->getCodeForCreateTable($params);
            case 'drop-table':
                return $this->getCodeForDropTable($params);
            case 'add-column':
                return $this->getCodeForAddColumn($params);
            case 'drop-column':
                return $this->getCodeForDropColumn($params);
        }
        return '';
    }

    protected function getCodeForDown($params)
    {
        switch ($params['command']) {
            case 'drop-table':
                return $this->getCodeForCreateTable($params);
            case 'create-table':
                return $this->getCodeForDropTable($params);
            case 'drop-column':
                return $this->getCodeForAddColumn($params);
            case 'add-column':
                return $this->getCodeForDropColumn($params);
        }
        return '';
    }

    protected function getCodeForCreateTable($params)
    {
        return $this->renderFile(__DIR__.'/views/createTable.php', [
            'table' => ArrayHelper::getValue($params, 'table', ''),
            'tableOptions' => ArrayHelper::getValue($params, 'tableOptions', $this->tableOptions),
        ]);
    }

    protected function getCodeForDropTable($params)
    {
        return $this->renderFile(__DIR__.'/views/dropTable.php', [
            'table' => ArrayHelper::getValue($params, 'table', ''),
        ]);
    }

    protected function getCodeForAddColumn($params)
    {
        return $this->renderFile(__DIR__.'/views/addColumn.php', [
            'table' => ArrayHelper::getValue($params, 'table', ''),
            'column' => ArrayHelper::getValue($params, 'column', ''),
            'columnType' => ArrayHelper::getValue($params, 'columnType', ''),
        ]);
    }

    protected function getCodeForDropColumn($params)
    {
        return $this->renderFile(__DIR__.'/views/dropColumn.php', [
            'table' => ArrayHelper::getValue($params, 'table', ''),
            'column' => ArrayHelper::getValue($params, 'column', ''),
        ]);
    }
}
