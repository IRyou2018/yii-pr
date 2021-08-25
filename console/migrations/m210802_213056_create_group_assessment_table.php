<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%group_assessment}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%assessments}}`
 */
class m210802_213056_create_group_assessment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%group_assessment}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'group_number' => $this->integer(2),
            'mark' => $this->string(5),
            'marked' => $this->boolean(),
            'assessment_id' => $this->integer(11),
        ]);

        // creates index for column `assessment_id`
        $this->createIndex(
            '{{%idx-group_assessment-assessment_id}}',
            '{{%group_assessment}}',
            'assessment_id'
        );

        // add foreign key for table `{{%assessments}}`
        $this->addForeignKey(
            '{{%fk-group_assessment-assessment_id}}',
            '{{%group_assessment}}',
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
            '{{%fk-group_assessment-assessment_id}}',
            '{{%group_assessment}}'
        );

        // drops index for column `assessment_id`
        $this->dropIndex(
            '{{%idx-group_assessment-assessment_id}}',
            '{{%group_assessment}}'
        );

        $this->dropTable('{{%group_assessment}}');
    }
}
