<?php
use Migrations\AbstractMigration;

class AddCreatedAt extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $table = $this->table('jobs');
        $table->addColumn('created_at', 'datetime', [
            'default' => null,
            'null' => true,
        ]);
        $table->addIndex([
            'created_at',
        ]);
        $table->update();
    }
}
