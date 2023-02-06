<?php
use Migrations\AbstractMigration;

class AddStatusAndDate extends AbstractMigration
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
        $table->addColumn('status', 'string', [
            'default' => 'new',
            'null' => false,
            'limit' => 50,
        ]);
        $table->addColumn('executed_date', 'datetime', [
            'default' => null,
            'null' => true 
        ]);
        $table->update();
    }
}
