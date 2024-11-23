<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UserGenderMigration extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('user');
        $table->removeColumn('gender')->save();
        $table->addColumn('gender', 'smallinteger', ['limit' => 2, 'default' => 1])->update();
    }
}
