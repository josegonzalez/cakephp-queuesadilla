<?php
use Migrations\AbstractMigration;

class AddDefaultValueForLocked extends AbstractMigration
{

    public function change()
    {
        $table = $this->table('jobs');
        $table->changeColumn('locked', 'integer', [
            'default' => 0
        ]);
        $table->update();
    }
}

