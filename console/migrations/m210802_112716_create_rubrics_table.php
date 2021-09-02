<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%rubrics}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%items}}`
 */
class m210802_112716_create_rubrics_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%rubrics}}', [
            'id' => $this->primaryKey(),
            'level' => $this->string(255),
            'weight' => $this->string(255),
            'description' => $this->string(255),
            'item_id' => $this->integer(11)->notNull(),
        ]);

        // creates index for column `item_id`
        $this->createIndex(
            '{{%idx-rubrics-item_id}}',
            '{{%rubrics}}',
            'item_id'
        );

        // add foreign key for table `{{%items}}`
        $this->addForeignKey(
            '{{%fk-rubrics-item_id}}',
            '{{%rubrics}}',
            'item_id',
            '{{%items}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%items}}`
        $this->dropForeignKey(
            '{{%fk-rubrics-item_id}}',
            '{{%rubrics}}'
        );

        // drops index for column `item_id`
        $this->dropIndex(
            '{{%idx-rubrics-item_id}}',
            '{{%rubrics}}'
        );

        $this->dropTable('{{%rubrics}}');
    }
}
