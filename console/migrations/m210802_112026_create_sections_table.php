<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%sections}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%assessments}}`
 */
class m210802_112026_create_sections_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%sections}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'assessment_id' => $this->integer(11)->notNull(),
            'section_type' => $this->boolean()->notNull(),
        ]);

        // creates index for column `assessment_id`
        $this->createIndex(
            '{{%idx-sections-assessment_id}}',
            '{{%sections}}',
            'assessment_id'
        );

        // add foreign key for table `{{%assessments}}`
        $this->addForeignKey(
            '{{%fk-sections-assessment_id}}',
            '{{%sections}}',
            'assessment_id',
            '{{%assessments}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%assessments}}`
        $this->dropForeignKey(
            '{{%fk-sections-assessment_id}}',
            '{{%sections}}'
        );

        // drops index for column `assessment_id`
        $this->dropIndex(
            '{{%idx-sections-assessment_id}}',
            '{{%sections}}'
        );

        $this->dropTable('{{%sections}}');
    }
}
