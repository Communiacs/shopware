<?php
class Migrations_Migration411 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<SQL
ALTER TABLE s_core_auth
DROP admin,
DROP salted
SQL;
        $this->addSql($sql);
    }
}
