<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UserStatusMigration extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('user');
        $table->addColumn('status', 'string', ['limit' => 180, 'default' => 'active'])
            ->update();
    }
}
