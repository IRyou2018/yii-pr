<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%items}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%sections}}`
 */
class m210802_112539_create_items_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%items}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'max_mark_value' => $this->integer(3)->notNull(),
            'item_type' => $this->boolean()->notNull(),
            'section_id' => $this->integer(11)->notNull(),
        ]);

        // creates index for column `section_id`
        $this->createIndex(
            '{{%idx-items-section_id}}',
            '{{%items}}',
            'section_id'
        );

        // add foreign key for table `{{%sections}}`
        $this->addForeignKey(
            '{{%fk-items-section_id}}',
            '{{%items}}',
            'section_id',
            '{{%sections}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%sections}}`
        $this->dropForeignKey(
            '{{%fk-items-section_id}}',
            '{{%items}}'
        );

        // drops index for column `section_id`
        $this->dropIndex(
            '{{%idx-items-section_id}}',
            '{{%items}}'
        );

        $this->dropTable('{{%items}}');
    }
}
