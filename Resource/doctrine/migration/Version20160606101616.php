<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160606101616 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema)
    {
        $table = $schema->createTable('plg_efo_config');
        $table->addColumn('id', 'integer', array('autoincrement' => true));
        $table->addColumn('shopping_login_destination', 'integer');
        $table->addColumn('create_date', 'datetime');
        $table->addColumn('update_date', 'datetime');
        $table->setPrimaryKey(array('id'));

        $table = $schema->createTable('plg_efo_entry_form');
        $table->addColumn('id', 'integer', array('autoincrement' => true));
        $table->addColumn('name', 'string', array('length' => 255));
        $table->addColumn('path', 'string', array('length' => 1024));
        $table->addColumn('product_id', 'integer');
        $table->addColumn('page_id', 'integer');
        $table->addColumn('customer_registration_enabled', 'boolean');
        $table->addColumn('del_flg', 'smallint');
        $table->addColumn('create_date', 'datetime');
        $table->addColumn('update_date', 'datetime');
        $table->setPrimaryKey(array('id'));
        $table->addIndex(array('del_flg'), 'plg_efo_entry_form_del_flg_idx');
        $table->addIndex(array('path'), 'plg_efo_entry_form_path_idx');
        $table->addIndex(array('product_id'), 'plg_efo_entry_form_product_id_idx');
        $table->addIndex(array('page_id'), 'plg_efo_entry_form_page_id_idx');
        $table->addForeignKeyConstraint('dtb_product', array('product_id'), array('product_id'), array('onDelete' => 'RESTRICT', 'onUpdate' => 'CASCADE'), 'plg_efo_entry_form_product_id_foreign');
        $table->addForeignKeyConstraint('dtb_page_layout', array('page_id'), array('page_id'), array('onDelete' => 'RESTRICT', 'onUpdate' => 'CASCADE'), 'plg_efo_entry_form_page_id_foreign');

        $table = $schema->createTable('plg_efo_customer_property');
        $table->addColumn('id', 'integer', array('autoincrement' => true));
        $table->addColumn('property', 'string', array('length' => 255));
        $table->addColumn('label', 'string', array('length' => 255));
        $table->addColumn('enabled', 'boolean');
        $table->addColumn('rank', 'integer');
        $table->addColumn('create_date', 'datetime');
        $table->addColumn('update_date', 'datetime');
        $table->setPrimaryKey(array('id'));
        $table->addIndex(array('rank'), 'plg_efo_customer_property_rank_idx');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->dropTable('plg_efo_customer_property');
        $schema->dropTable('plg_efo_entry_form');
        $schema->dropTable('plg_efo_config');
    }
}
